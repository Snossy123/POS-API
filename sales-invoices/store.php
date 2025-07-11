<?php

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO sales_invoices (invoice_number, date, time, cashier, total)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['invoiceNumber'],
        $data['date'],
        $data['time'],
        $data['cashier'],
        $data['total']
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
?>
