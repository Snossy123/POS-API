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

            $mergedItems = $this->mergeRequestItems($data['items']);

            foreach ($mergedItems as $item) {
                SalesInvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? $item['id'] ?? null,
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'barcode' => $item['barcode'] ?? '',
                ]);
            }

            $this->inventoryService->deductForSale($mergedItems);

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

            if ($status === 'refunded') {
                $this->inventoryService->restoreForInvoiceItems($salesInvoice->items);
            } elseif (!empty($data['items'])) {
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

    public function pay(Request $request, SalesInvoice $salesInvoice)
    {
        $this->authorize('pay', $salesInvoice);

        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,card'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        $paymentMethod = $data['payment_method'];
        $total = (float) $salesInvoice->total;
        $amountPaid = $paymentMethod === 'card'
            ? $total
            : (float) ($data['amount_paid'] ?? $total);

        if ($paymentMethod === 'cash' && $amountPaid < $total) {
            return response()->json([
                'status' => 'error',
                'message' => 'المبلغ المدفوع أقل من الإجمالي',
            ], 422);
        }

        $changeGiven = max(0, $amountPaid - $total);

        DB::transaction(function () use ($salesInvoice, $request, $paymentMethod, $amountPaid, $changeGiven) {
            $salesInvoice->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_given' => $changeGiven,
            ]);

            SalesInvoiceAction::create([
                'invoice_id' => $salesInvoice->id,
                'action' => 'pay',
                'performed_by' => $request->user()->id,
                'performer_type' => AuthUser::type($request->user()),
                'meta' => [
                    'payment_method' => $paymentMethod,
                    'amount_paid' => $amountPaid,
                    'change_given' => $changeGiven,
                ],
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'تم دفع الفاتورة',
            'invoice' => $this->transformInvoice($salesInvoice->fresh()->load(['employee', 'items'])),
        ]);
    }

    public function updateItems(Request $request, SalesInvoice $salesInvoice)
    {
        $this->authorize('updateItems', $salesInvoice);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable'],
            'items.*.id' => ['nullable'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.barcode' => ['nullable', 'string'],
            'kitchen_note' => ['nullable', 'string', 'max:500'],
            'total' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($salesInvoice, $request, $data) {
            $mergedItems = $this->mergeRequestItems($data['items']);
            $total = $this->calculateItemsTotal($mergedItems);

            $oldItems = $salesInvoice->items()->get();
            $this->inventoryService->adjustForItemChanges($oldItems, $mergedItems);

            $salesInvoice->items()->delete();

            foreach ($mergedItems as $item) {
                SalesInvoiceItem::create([
                    'invoice_id' => $salesInvoice->id,
                    'product_id' => $item['product_id'] ?? $item['id'] ?? null,
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'barcode' => $item['barcode'] ?? '',
                ]);
            }

            $updateData = ['total' => $total];
            if (array_key_exists('kitchen_note', $data)) {
                $updateData['kitchen_note'] = $data['kitchen_note'];
            }
            $salesInvoice->update($updateData);

            SalesInvoiceAction::create([
                'invoice_id' => $salesInvoice->id,
                'action' => 'items_update',
                'performed_by' => $request->user()->id,
                'performer_type' => AuthUser::type($request->user()),
                'meta' => ['total' => $total, 'item_count' => count($mergedItems)],
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الفاتورة',
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
        $data['status'] = $invoice->status ?? 'completed';
        $data['payment_status'] = $invoice->payment_status ?? 'paid';
        $data['total'] = (float) $invoice->total;
        $data['amount_paid'] = (float) ($invoice->amount_paid ?? 0);
        $data['change_given'] = (float) ($invoice->change_given ?? 0);
        $data['items'] = $this->mergeStoredItems($invoice->items);

        return $data;
    }

    private function mergeRequestItems(array $items): array
    {
        $merged = [];

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $price = round((float) $item['price'], 2);
            $key = ($productId ?? ($item['name'] ?? '')) . '|' . number_format($price, 2, '.', '');

            if (isset($merged[$key])) {
                $merged[$key]['quantity'] += (float) $item['quantity'];
                continue;
            }

            $merged[$key] = $item;
            $merged[$key]['price'] = $price;
            $merged[$key]['quantity'] = (float) $item['quantity'];
            if ($productId) {
                $merged[$key]['product_id'] = $productId;
            }
        }

        return array_values($merged);
    }

    private function mergeStoredItems(iterable $items): array
    {
        $merged = [];

        foreach ($items as $item) {
            $productId = $item->product_id;
            $price = round((float) $item->price, 2);
            $key = ($productId ?? $item->product_name) . '|' . number_format($price, 2, '.', '');

            if (isset($merged[$key])) {
                $merged[$key]['quantity'] += (float) $item->quantity;
                continue;
            }

            $merged[$key] = [
                'id' => $productId ?? $item->id,
                'product_id' => $productId,
                'name' => $item->product_name,
                'price' => $price,
                'quantity' => (float) $item->quantity,
                'barcode' => $item->barcode,
            ];
        }

        return array_values($merged);
    }

    private function calculateItemsTotal(array $items): float
    {
        return array_reduce(
            $items,
            fn (float $sum, array $item) => $sum + ((float) $item['price'] * (float) $item['quantity']),
            0.0
        );
    }
}
