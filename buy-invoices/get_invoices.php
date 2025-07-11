<?php
require 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM purchase_invoices ORDER BY id DESC");
    $invoices = $stmt->fetchAll();

    foreach ($invoices as &$invoice) {
        $stmt_items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $stmt_items->execute([$invoice['id']]);
        $invoice['items'] = $stmt_items->fetchAll();
    }

    echo json_encode($invoices);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
