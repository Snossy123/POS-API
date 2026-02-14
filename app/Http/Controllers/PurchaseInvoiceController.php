<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseInvoice;
use App\Models\InvoiceItem;
use App\Models\Category;
use App\Models\Product;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        // Join categories to get category name as 'category'
        // Using Eloquent:
        $invoices = PurchaseInvoice::with('items.category_info')->orderBy('id', 'desc')->get();
        
        // Transform to match legacy format if needed, or return as is.
        // Legacy getInvoices returned:
        // [ { ...invoice_fields..., items: [ { ...item_fields..., category: "Category Name" } ] } ]
        
        // Eloquent 'with' returns nested relation.
        // We might need to map it to flatten category name into item if frontend expects 'category' field.
        
        $transformed = $invoices->map(function($invoice) {
            $invData = $invoice->toArray();
            $invData['items'] = $invoice->items->map(function($item) {
                $itmData = $item->toArray();
                $itmData['category'] = $item->category_info ? $item->category_info->name : $item->category;
                return $itmData;
            });
            return $invData;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Invoices fetched successfully',
            'invoices' => $transformed
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        // Validation...

        try {
            DB::beginTransaction();

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $data['invoice_number'],
                'supplier' => $data['supplier'],
                'date' => $data['date'],
                'time' => $data['time'],
                'total' => $data['total']
            ]);

            foreach ($data['items'] as $item) {
                // Lookups
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
                    'product_id' => $productId, // We added this column in previous steps? Wait, standard invoice_items table has product_id?
                    // Step 30 schema: invoice_items has product_id.
                    // But legacy code in Step 1 was using product_name etc.
                    // My fix in Step 37 added product_id to INSERT.
                    // So yes, we should use it.
                    'product_name' => $item['product_name'],
                    'barcode' => $item['barcode'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'sale_price' => $item['sale_price'],
                    'category_id' => $categoryId
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'invoice_id' => $invoice->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
