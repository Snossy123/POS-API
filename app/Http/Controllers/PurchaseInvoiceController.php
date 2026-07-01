<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function nextNumber()
    {
        return response()->json([
            'status' => 'success',
            'invoice_number' => $this->generateInvoiceNumber(),
        ]);
    }

    public function index(Request $request)
    {
        $query = PurchaseInvoice::with('items.category_info')->orderBy('id', 'desc');

        $type = $request->query('invoice_type');
        if (in_array($type, ['general', 'operation'], true)) {
            $query->where('invoice_type', $type);
        }

        $invoices = $query->get();

        $transformed = $invoices->map(fn ($invoice) => $this->transformInvoice($invoice));

        return response()->json([
            'status' => 'success',
            'message' => 'Invoices fetched successfully',
            'invoices' => $transformed,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'supplier' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'total' => ['required', 'numeric', 'min:0'],
            'invoice_type' => ['required', 'in:general,operation'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.barcode' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'items.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.category' => ['nullable', 'string', 'max:255'],
        ]);

        $invoiceType = $data['invoice_type'];
        $invoiceNumber = trim((string) ($data['invoice_number'] ?? ''));
        if ($invoiceNumber === '') {
            $invoiceNumber = $this->generateInvoiceNumber();
        }

        try {
            DB::beginTransaction();

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier' => $data['supplier'],
                'invoice_type' => $invoiceType,
                'date' => $data['date'],
                'time' => $data['time'],
                'total' => $data['total'],
            ]);

            foreach ($data['items'] as $item) {
                if ($invoiceType === 'operation') {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => null,
                        'product_name' => $item['product_name'],
                        'barcode' => '',
                        'quantity' => 1,
                        'purchase_price' => $item['purchase_price'],
                        'sale_price' => 0,
                        'category_id' => null,
                    ]);
                    continue;
                }

                $categoryId = null;
                if (!empty($item['category'])) {
                    $cat = Category::where('name', $item['category'])->first();
                    $categoryId = $cat ? $cat->id : null;
                }

                $productId = null;
                if (!empty($item['barcode'])) {
                    $prod = Product::where('barcode', $item['barcode'])->first();
                    $productId = $prod ? $prod->id : null;
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $productId,
                    'product_name' => $item['product_name'],
                    'barcode' => $item['barcode'] ?? '',
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'sale_price' => $item['sale_price'] ?? 0,
                    'category_id' => $categoryId,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'invoice' => $this->transformInvoice($invoice->fresh()->load('items.category_info')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "PUR-{$date}-";

        $last = PurchaseInvoice::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = 1;
        if ($last && str_starts_with($last, $prefix)) {
            $sequence = ((int) substr($last, strlen($prefix))) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function transformInvoice(PurchaseInvoice $invoice): array
    {
        $invData = $invoice->toArray();
        $invData['invoice_type'] = $invoice->invoice_type ?? 'general';
        $invData['total'] = (float) $invoice->total;
        $invData['items'] = $invoice->items->map(function ($item) {
            $itmData = $item->toArray();
            $itmData['category'] = $item->category_info ? $item->category_info->name : ($item->category ?? '');
            $itmData['purchase_price'] = (float) $item->purchase_price;
            $itmData['sale_price'] = (float) $item->sale_price;
            $itmData['quantity'] = (float) $item->quantity;

            return $itmData;
        });

        return $invData;
    }
}
