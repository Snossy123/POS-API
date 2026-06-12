<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceAction;
use App\Models\SalesInvoiceItem;
use App\Models\Shift;
use App\Services\InventoryService;
use App\Support\AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    public function __construct(private InventoryService $inventoryService)
    {
    }

    public function index()
    {
        $invoices = SalesInvoice::with(['employee', 'items'])->orderBy('id', 'desc')->get();

        $transformed = $invoices->map(fn ($invoice) => $this->transformInvoice($invoice));

        return response()->json([
            'status' => 'success',
            'message' => 'Invoices fetched successfully',
            'invoices' => $transformed,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', SalesInvoice::class);

        $data = $request->all();
        $clientId = $request->header('X-Client-Id');

        if ($clientId) {
            $existing = SalesInvoice::where('client_id', $clientId)->first();
            if ($existing) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Invoice already synced',
                    'invoice' => $this->transformInvoice($existing->load(['employee', 'items'])),
                ]);
            }
        }

        try {
            DB::beginTransaction();

            $shift = $request->attributes->get('current_shift');
            if (!$shift && $request->user() instanceof Employee) {
                $shift = Shift::where('employee_id', $request->user()->id)
                    ->where('status', 'open')
                    ->latest('id')
                    ->first();
            }

            $paymentMethod = $data['payment_method'] ?? 'cash';
            $total = (float) $data['total'];
            $paymentStatus = in_array($data['payment_status'] ?? 'paid', ['paid', 'unpaid', 'partial'], true)
                ? $data['payment_status']
                : 'paid';
            $orderType = in_array($data['order_type'] ?? 'takeaway', ['takeaway', 'table'], true)
                ? $data['order_type']
                : 'takeaway';

            $amountPaid = $paymentStatus === 'unpaid'
                ? 0
                : (isset($data['amount_paid']) ? (float) $data['amount_paid'] : $total);
            $changeGiven = $paymentStatus === 'unpaid' ? 0 : max(0, $amountPaid - $total);

            $invoice = SalesInvoice::create([
                'invoice_number' => $data['invoiceNumber'],
                'date' => $data['date'],
                'time' => $data['time'],
                'employee_id' => $data['employee_id'],
                'shift_id' => $shift?->id,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_given' => $changeGiven,
                'kitchen_note' => $data['kitchen_note'] ?? '',
                'order_type' => $orderType,
                'status' => 'completed',
                'payment_status' => $paymentStatus,
                'client_id' => $clientId,
            ]);

            foreach ($data['items'] as $item) {
                SalesInvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? $item['id'] ?? null,
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'barcode' => $item['barcode'] ?? '',
                ]);
            }

            $this->inventoryService->deductForSale($data['items']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice saved successfully',
                'invoice' => $this->transformInvoice($invoice->load(['employee', 'items'])),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function void(Request $request, SalesInvoice $salesInvoice)
    {
        $this->authorize('void', $salesInvoice);

        if (!$salesInvoice->isVoidable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن إلغاء هذه الفاتورة',
            ], 422);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($salesInvoice, $request, $data) {
            $user = $request->user();

            $salesInvoice->update([
                'status' => 'void',
                'voided_at' => now(),
                'voided_by' => AuthUser::employeeId($user) ?? $user->id,
            ]);

            $this->inventoryService->restoreForInvoiceItems($salesInvoice->items);

            SalesInvoiceAction::create([
                'invoice_id' => $salesInvoice->id,
                'action' => 'void',
                'performed_by' => $user->id,
                'performer_type' => AuthUser::type($user),
                'reason' => $data['reason'] ?? null,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'تم إلغاء الفاتورة',
            'invoice' => $this->transformInvoice($salesInvoice->fresh()->load(['employee', 'items'])),
        ]);
    }

    public function refund(Request $request, SalesInvoice $salesInvoice)
    {
        $this->authorize('refund', $salesInvoice);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:500'],
            'items' => ['nullable', 'array'],
        ]);

        $amount = (float) $data['amount'];
        $maxRefundable = $salesInvoice->refundableAmount();

        if ($amount > $maxRefundable) {
            return response()->json([
                'status' => 'error',
                'message' => 'مبلغ الاسترجاع أكبر من المسموح',
            ], 422);
        }

        DB::transaction(function () use ($salesInvoice, $request, $data, $amount) {
            $newRefundTotal = (float) $salesInvoice->refund_amount + $amount;
            $status = abs($newRefundTotal - (float) $salesInvoice->total) < 0.01
                ? 'refunded'
                : 'partial_refund';

            $salesInvoice->update([
                'refund_amount' => $newRefundTotal,
                'status' => $status,
            ]);

            if (!empty($data['items'])) {
                $this->inventoryService->restorePartial($salesInvoice->items, $data['items']);
            }

            SalesInvoiceAction::create([
                'invoice_id' => $salesInvoice->id,
                'action' => 'refund',
                'performed_by' => $request->user()->id,
                'performer_type' => AuthUser::type($request->user()),
                'reason' => $data['reason'] ?? null,
                'meta' => ['amount' => $amount],
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الاسترجاع',
            'invoice' => $this->transformInvoice($salesInvoice->fresh()->load(['employee', 'items'])),
        ]);
    }

    public function updatePaymentStatus(Request $request, SalesInvoice $salesInvoice)
    {
        $this->authorize('updatePaymentStatus', $salesInvoice);

        $data = $request->validate([
            'payment_status' => ['required', 'in:paid,unpaid,partial'],
        ]);

        $salesInvoice->update([
            'payment_status' => $data['payment_status'],
        ]);

        SalesInvoiceAction::create([
            'invoice_id' => $salesInvoice->id,
            'action' => 'payment_status_change',
            'performed_by' => $request->user()->id,
            'performer_type' => AuthUser::type($request->user()),
            'meta' => ['payment_status' => $data['payment_status']],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث حالة الدفع',
            'invoice' => $this->transformInvoice($salesInvoice->fresh()->load(['employee', 'items'])),
        ]);
    }

    public function reprint(Request $request, SalesInvoice $salesInvoice)
    {
        $this->authorize('reprint', $salesInvoice);

        SalesInvoiceAction::create([
            'invoice_id' => $salesInvoice->id,
            'action' => 'reprint',
            'performed_by' => $request->user()->id,
            'performer_type' => AuthUser::type($request->user()),
        ]);

        return response()->json([
            'status' => 'success',
            'invoice' => $this->transformInvoice($salesInvoice->load(['employee', 'items'])),
        ]);
    }

    private function transformInvoice(SalesInvoice $invoice): array
    {
        $data = $invoice->toArray();
        $data['invoiceNumber'] = $invoice->invoice_number;
        $data['cashier'] = $invoice->employee?->name;
        $data['items'] = $invoice->items->map(fn ($item) => [
            'id' => $item->product_id ?? $item->id,
            'product_id' => $item->product_id,
            'name' => $item->product_name,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'barcode' => $item->barcode,
        ]);

        return $data;
    }
}
