<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO purchase_invoices (invoice_number, supplier, date, time, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['invoice_number'],
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
                $item['product_name'],
                $item['barcode'],
                $item['quantity'],
                $item['purchase_price'],
                $item['sale_price'],
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
    try {
        $stmt = $pdo->query("SELECT * FROM purchase_invoices ORDER BY id DESC");
        $invoices = $stmt->fetchAll();

        foreach ($invoices as &$invoice) {
            $stmt_items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
            $stmt_items->execute([$invoice['id']]);
            $invoice['items'] = $stmt_items->fetchAll();
        }

        return $invoices;
    } catch (Exception $e) {
        http_response_code(500);
        return ['error' => $e->getMessage()];
    }
}