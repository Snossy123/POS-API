<?php

$type = $_GET['type'] ?? 'sales';
$dateFrom = $_GET['from'] ?? date('Y-m-01');
$dateTo = $_GET['to'] ?? date('Y-m-t');

if ($type === 'sales') {
    // تقرير المبيعات
    $stmt = $pdo->prepare("
        SELECT date, COUNT(*) AS invoices, SUM(total) AS total
        FROM sales_invoices
        WHERE date BETWEEN ? AND ?
        GROUP BY date
        ORDER BY date
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($type === 'purchases') {
    // تقرير المشتريات
    $stmt = $pdo->prepare("
        SELECT 
            pi.date,
            COUNT(DISTINCT pi.id) AS invoices,
            SUM(pi.total) AS total,
            COUNT(ii.id) AS items
        FROM purchase_invoices pi
        LEFT JOIN invoice_items ii ON pi.id = ii.invoice_id
        WHERE pi.date BETWEEN ? AND ?
        GROUP BY pi.date
        ORDER BY pi.date;
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($type === 'profits') {
    // تقرير الأرباح
    $stmt = $pdo->prepare("
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
    ");
    $stmt->execute([$dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($type === 'top-selling') {
    // المنتجات الأكثر مبيعاً
    $stmt = $pdo->prepare("
        SELECT product_name, SUM(quantity) AS quantity, SUM(price * quantity) AS revenue
        FROM sales_invoice_items
        JOIN sales_invoices ON sales_invoice_items.invoice_id = sales_invoices.id
        WHERE sales_invoices.date BETWEEN ? AND ?
        GROUP BY product_name
        ORDER BY quantity DESC
        LIMIT 5
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($type === 'purchased-items') {
    // المنتجات المشتراة
    $stmt = $pdo->prepare("
        SELECT product_name, SUM(quantity) AS quantity, SUM(purchase_price * quantity) AS cost
        FROM invoice_items
        JOIN purchase_invoices ON invoice_items.invoice_id = purchase_invoices.id
        WHERE purchase_invoices.date BETWEEN ? AND ?
        GROUP BY product_name
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($type === 'sold-items') {
    // المنتجات المباعة مع المتبقي
    $stmt = $pdo->prepare("
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
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid report type']);
    exit;
}

echo json_encode($data);
