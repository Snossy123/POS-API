<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Employee;

class SalesInvoiceController extends Controller
{
    public function index()
    {
        $invoices = SalesInvoice::with(['employee', 'items'])->orderBy('id', 'desc')->get();

        $transformed = $invoices->map(function($invoice) {
            $data = $invoice->toArray();
            $data['invoiceNumber'] = $invoice->invoice_number; // Frontend might expect camelCase
            $data['cashier'] = $invoice->employee ? $invoice->employee->name : null;
            // Items are already loaded, just ensure fields match
            $data['items'] = $invoice->items->map(function($item){
                return [
                     'name' => $item->product_name, // DB column is product_name?
                     // Step 60 showed: SELECT product_name AS name ...
                     // So DB col is product_name.
                     'price' => $item->price,
                     'quantity' => $item->quantity
                ];
            });
            return $data;
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

        try {
            DB::beginTransaction();

            $invoice = SalesInvoice::create([
                'invoice_number' => $data['invoiceNumber'],
                'date' => $data['date'],
                'time' => $data['time'],
                'employee_id' => $data['employee_id'],
                'total' => (float) $data['total'],
                'kitchen_note' => $data['kitchen_note'] ?? ''
            ]);

            foreach ($data['items'] as $item) {
                SalesInvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_name' => $item['name'], // JSON sends 'name'
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'barcode' => $item['barcode'] ?? ''
                ]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Invoice saved successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
