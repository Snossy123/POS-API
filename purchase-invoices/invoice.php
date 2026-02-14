<?php


// Ensure database connection is available
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/env_loader.php';
    loadEnv(__DIR__ . '/../.env');
    require_once __DIR__ . '/../config/db.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

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

        // Prepare statements for lookups
        $stmtCategory = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
        $stmtProduct = $pdo->prepare("SELECT id FROM products WHERE barcode = ? LIMIT 1");
        
        // Insert item with foreign keys
        $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, barcode, quantity, purchase_price, sale_price, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($data['items'] as $item) {
            // Lookup Category ID
            $categoryId = null;
            if (!empty($item['category'])) {
                $stmtCategory->execute([$item['category']]);
                $catResult = $stmtCategory->fetch();
                if ($catResult) {
                    $categoryId = $catResult['id'];
                }
            }

            // Lookup Product ID
            $productId = null;
            if (!empty($item['barcode'])) {
                $stmtProduct->execute([$item['barcode']]);
                $prodResult = $stmtProduct->fetch();
                if ($prodResult) {
                    $productId = $prodResult['id'];
                }
            }

            $stmt->execute([
                $invoice_id,
                $productId,
                $item['product_name'],
                $item['barcode'],
                $item['quantity'],
                $item['purchase_price'],
                $item['sale_price'],
                $categoryId
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

        // Prepare statement once outside the loop
        $stmt_items = $pdo->prepare("
            SELECT i.*, c.name as category 
            FROM invoice_items i 
            LEFT JOIN categories c ON i.category_id = c.id 
            WHERE i.invoice_id = ?
        ");

        foreach ($invoices as &$invoice) {
            $stmt_items->execute([$invoice['id']]);
            $invoice['items'] = $stmt_items->fetchAll();
        }

        return $invoices;
    } catch (Exception $e) {
        http_response_code(500);
        return ['error' => $e->getMessage()];
    }
}