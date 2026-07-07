<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** Only count completed (non-void, non-refunded) sales invoices in reports. */
    private const ACTIVE_SALE = "status = 'completed'";

    public function index(Request $request)
    {
        $this->authorize('viewReports');

        $type = $request->query('type', 'sales');
        $dateFrom = $request->query('from', date('Y-m-01'));
        $dateTo = $request->query('to', date('Y-m-t'));

        $data = [];

        switch ($type) {
            case 'sales':
                $data = DB::select("
                    SELECT date, COUNT(*) AS invoices, SUM(total) AS total
                    FROM sales_invoices
                    WHERE date BETWEEN ? AND ?
                      AND " . self::ACTIVE_SALE . "
                    GROUP BY date
                    ORDER BY date
                ", [$dateFrom, $dateTo]);
                break;

            case 'purchases':
                $data = DB::select("
                    SELECT 
                        pi.date,
                        pi.invoice_type,
                        COUNT(DISTINCT pi.id) AS invoices,
                        SUM(pi.total) AS total,
                        COUNT(ii.id) AS items
                    FROM purchase_invoices pi
                    LEFT JOIN invoice_items ii ON pi.id = ii.invoice_id
                    WHERE pi.date BETWEEN ? AND ?
                    GROUP BY pi.date, pi.invoice_type
                    ORDER BY pi.date, pi.invoice_type
                ", [$dateFrom, $dateTo]);
                break;

            case 'profits':
                $data = DB::select("
                    SELECT date, sales, purchases, profit FROM (
                        SELECT s.date,
                            IFNULL(s.total, 0) AS sales,
                            IFNULL(p.total, 0) AS purchases,
                            (IFNULL(s.total, 0) - IFNULL(p.total, 0)) AS profit
                        FROM 
                        (SELECT date, SUM(total) AS total FROM sales_invoices WHERE date BETWEEN ? AND ? AND " . self::ACTIVE_SALE . " GROUP BY date) s
                        LEFT JOIN 
                        (SELECT date, SUM(total) AS total FROM purchase_invoices WHERE date BETWEEN ? AND ? GROUP BY date) p
                        ON s.date = p.date
                        UNION
                        SELECT p.date,
                            IFNULL(s.total, 0) AS sales,
                            IFNULL(p.total, 0) AS purchases,
                            (IFNULL(s.total, 0) - IFNULL(p.total, 0)) AS profit
                        FROM 
                        (SELECT date, SUM(total) AS total FROM sales_invoices WHERE date BETWEEN ? AND ? AND " . self::ACTIVE_SALE . " GROUP BY date) s
                        RIGHT JOIN 
                        (SELECT date, SUM(total) AS total FROM purchase_invoices WHERE date BETWEEN ? AND ? GROUP BY date) p
                        ON s.date = p.date
                    ) AS combined
                    ORDER BY combined.date
                ", [$dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo]);
                break;

            case 'top-selling':
                $data = DB::select("
                    SELECT sii.product_name, SUM(sii.quantity) AS quantity, SUM(sii.price * sii.quantity) AS revenue
                    FROM sales_invoice_items sii
                    JOIN sales_invoices si ON sii.invoice_id = si.id
                    WHERE si.date BETWEEN ? AND ?
                      AND si." . self::ACTIVE_SALE . "
                    GROUP BY sii.product_name
                    ORDER BY quantity DESC
                    LIMIT 5
                ", [$dateFrom, $dateTo]);
                break;

            case 'purchased-items':
                $data = DB::select("
                    SELECT product_name, SUM(quantity) AS quantity, SUM(purchase_price * quantity) AS cost
                    FROM invoice_items
                    JOIN purchase_invoices ON invoice_items.invoice_id = purchase_invoices.id
                    WHERE purchase_invoices.date BETWEEN ? AND ?
                      AND purchase_invoices.invoice_type = 'general'
                    GROUP BY product_name
                    ORDER BY quantity DESC
                ", [$dateFrom, $dateTo]);
                break;

            case 'sold-items':
                $data = DB::select("
                    SELECT p.name AS product_name,
                        IFNULL(sold.quantity_sold, 0) AS quantity_sold,
                        p.stock AS remaining
                    FROM products p
                    LEFT JOIN (
                        SELECT sii.product_name, SUM(sii.quantity) AS quantity_sold
                        FROM sales_invoice_items sii
                        JOIN sales_invoices si ON sii.invoice_id = si.id
                        WHERE si.date BETWEEN ? AND ?
                          AND si." . self::ACTIVE_SALE . "
                        GROUP BY sii.product_name
                    ) sold ON p.name = sold.product_name
                    ORDER BY quantity_sold DESC, p.name ASC
                ", [$dateFrom, $dateTo]);
                break;

            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }

        return response()->json($data);
    }
}
