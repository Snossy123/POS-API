<?php
// إعدادات الاتصال بقاعدة البيانات
$pdo = new PDO("mysql:host=localhost;dbname=my_database;charset=utf8", "root", "");

// استرجاع الفواتير
$stmt = $pdo->query("
  SELECT * FROM sales_invoices ORDER BY id DESC
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

header("Content-Type: application/json");
echo json_encode($invoices);
?>
