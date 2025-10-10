<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO sales_invoices (invoice_number, date, time, employee_id, total, kitchen_note)
                            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['invoiceNumber'],
            $data['date'],
            $data['time'],
            $data['employee_id'],
            (float) $data['total'],
            $data['kitchen_note'] ?? ''
        ]);
        $invoiceId = $pdo->lastInsertId();

        $itemStmt = $pdo->prepare("INSERT INTO sales_invoice_items (invoice_id, product_name, price, quantity, barcode)
                                VALUES (?, ?, ?, ?, ?)");

        foreach ($data['items'] as $item) {
            $itemStmt->execute([
                $invoiceId,
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['barcode'] ?? ''
            ]);
        }

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Invoice saved successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'message' => 'Invoices fetched successfully',
        'invoices' => getInvoices($pdo)
    ]);
    exit();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}

function getInvoices($pdo)
{
    // استرجاع الفواتير مع اسم الكاشير
    $stmt = $pdo->query("
        SELECT 
            si.*, 
            si.invoice_number AS invoiceNumber,
            e.name AS cashier
        FROM sales_invoices si
        LEFT JOIN employees e ON si.employee_id = e.id
        ORDER BY si.id DESC
    ");
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($invoices as &$invoice) {
    $stmt_items = $pdo->prepare("
        SELECT product_name AS name, price, quantity
        FROM sales_invoice_items
        WHERE invoice_id = ?
    ");
    $stmt_items->execute([$invoice['id']]);
    $invoice['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }

    return $invoices;
}