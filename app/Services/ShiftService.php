<?php

namespace App\Services;

use App\Models\SalesInvoice;
use App\Models\Shift;

class ShiftService
{
    public function calculateExpectedCash(Shift $shift): float
    {
        $cashSales = SalesInvoice::where('shift_id', $shift->id)
            ->whereIn('status', ['completed', 'partial_refund'])
            ->whereIn('payment_method', ['cash', 'mixed'])
            ->get()
            ->sum(function (SalesInvoice $invoice) {
                $net = (float) $invoice->total - (float) $invoice->refund_amount;

                if ($invoice->payment_method === 'mixed') {
                    return min($net, (float) ($invoice->amount_paid ?? $net));
                }

                return $net;
            });

        return (float) $shift->opening_float + $cashSales;
    }

    public function buildReport(Shift $shift): array
    {
        $invoices = SalesInvoice::with('items')
            ->where('shift_id', $shift->id)
            ->get();

        $completed = $invoices->whereIn('status', ['completed', 'partial_refund']);
        $voided = $invoices->where('status', 'void');

        return [
            'shift' => $shift->load('employee'),
            'summary' => [
                'total_sales' => $completed->sum('total'),
                'total_refunds' => $completed->sum('refund_amount'),
                'net_sales' => $completed->sum(fn ($i) => (float) $i->total - (float) $i->refund_amount),
                'invoice_count' => $completed->count(),
                'void_count' => $voided->count(),
                'expected_cash' => $shift->expected_cash,
                'actual_cash' => $shift->actual_cash,
                'cash_difference' => $shift->cash_difference,
            ],
            'invoices' => $completed->values(),
        ];
    }
}
