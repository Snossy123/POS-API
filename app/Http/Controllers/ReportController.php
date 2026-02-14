<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
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
                    GROUP BY date
                    ORDER BY date
                ", [$dateFrom, $dateTo]);
                break;

            case 'purchases':
                $data = DB::select("
                    SELECT 
                        pi.date,
                        COUNT(DISTINCT pi.id) AS invoices,
                        SUM(pi.total) AS total,
                        COUNT(ii.id) AS items
                    FROM purchase_invoices pi
                    LEFT JOIN invoice_items ii ON pi.id = ii.invoice_id
                    WHERE pi.date BETWEEN ? AND ?
                    GROUP BY pi.date
                    ORDER BY pi.date
                ", [$dateFrom, $dateTo]);
                break;

            case 'profits':
                $data = DB::select("
                    SELECT s.date,
                        IFNULL(s.total, 0) AS sales,
                        IFNULL(p.total, 0) AS purchases,
                        (IFNULL(s.total, 0) - IFNULL(p.total, 0)) AS profit
                    FROM 
                    (SELECT date, SUM(total) AS total FROM sales_invoices WHERE date BETWEEN ? AND ? GROUP BY date) s
                    LEFT JOIN 
                    (SELECT date, SUM(total) AS total FROM purchase_invoices WHERE date BETWEEN ? AND ? GROUP BY date) p
                    ON s.date = p.date
                    UNION
                    SELECT p.date,
                        IFNULL(s.total, 0) AS sales,
                        IFNULL(p.total, 0) AS purchases,
                        (IFNULL(s.total, 0) - IFNULL(p.total, 0)) AS profit
                    FROM 
                    (SELECT date, SUM(total) AS total FROM sales_invoices WHERE date BETWEEN ? AND ? GROUP BY date) s
                    RIGHT JOIN 
                    (SELECT date, SUM(total) AS total FROM purchase_invoices WHERE date BETWEEN ? AND ? GROUP BY date) p
                    ON s.date = p.date
                    ORDER BY date
                ", [$dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo]);
                break;

            case 'top-selling':
                $data = DB::select("
                    SELECT product_name, SUM(quantity) AS quantity, SUM(price * quantity) AS revenue
                    FROM sales_invoice_items
                    JOIN sales_invoices ON sales_invoice_items.invoice_id = sales_invoices.id
                    WHERE sales_invoices.date BETWEEN ? AND ?
                    GROUP BY product_name
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
                    GROUP BY product_name
                ", [$dateFrom, $dateTo]);
                break;

            case 'sold-items':
                // The query in reports.php for 'sold-items' was causing error in legacy code? Or just SQL?
                // Step 243 showed it:
                // SELECT p.name as product_name, IFNULL(SUM(sii.quantity), 0) as quantity_sold, p.stock as remaining
                // FROM products p
                // LEFT JOIN sales_invoice_items sii ON p.name = sii.product_name
                // LEFT JOIN sales_invoices si ON sii.invoice_id = si.id AND si.date BETWEEN ? AND ?
                // GROUP BY p.id
                
                $data = DB::select("
                    SELECT p.name AS product_name, 
                        IFNULL(SUM(sii.quantity), 0) AS quantity_sold, 
                        p.stock AS remaining
                    FROM products p
                    LEFT JOIN sales_invoice_items sii 
                        ON p.name = sii.product_name
                    LEFT JOIN sales_invoices si 
                        ON sii.invoice_id = si.id 
                        AND si.date BETWEEN ? AND ?
                    GROUP BY p.id
                ", [$dateFrom, $dateTo]);
                break;

            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }

        return response()->json($data);
    }
}
