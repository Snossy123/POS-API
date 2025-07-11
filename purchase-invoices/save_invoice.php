<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO purchase_invoices (invoice_number, supplier, date, time, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['invoiceNumber'],
        $data['supplier'],
        $data['date'],
        $data['time'],
        $data['total']
    ]);

    $invoice_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_name, barcode, quantity, purchase_price, sale_price, category) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($data['items'] as $item) {
        $stmt->execute([
            $invoice_id,
            $item['productName'],
            $item['barcode'],
            $item['quantity'],
            $item['purchasePrice'],
            $item['salePrice'],
            $item['category']
        ]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'invoice_id' => $invoice_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
