<?php
header("Content-Type: application/json");

// إعدادات الاتصال بقاعدة البيانات
$host = "localhost";
$dbname = "your_database_name";
$username = "your_db_username";
$password = "your_db_password";

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "فشل الاتصال بقاعدة البيانات"]);
    exit;
}

// استقبال البيانات
$input = json_decode(file_get_contents("php://input"), true);
$action = $input['action'] ?? '';

if ($action === 'add' || $action === 'update') {
    $product = $input['product'];
    if ($action === 'update') {
        // تعديل منتج
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ?, barcode = ?, category = ? WHERE id = ?");
        $stmt->execute([$product['name'], $product['price'], $product['stock'], $product['barcode'], $product['category'], $product['id']]);
        $message = "تم تحديث المنتج بنجاح";
    } else {
        // إضافة منتج جديد
        $stmt = $pdo->prepare("INSERT INTO products (id, name, price, stock, barcode, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product['id'], $product['name'], $product['price'], $product['stock'], $product['barcode'], $product['category']]);
        $message = "تم إضافة المنتج بنجاح";
    }
    echo json_encode([
        "success" => true,
        "message" => $message,
        "products" => getProducts($pdo)
    ]);
} elseif ($action === 'delete') {
    $id = $input['id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode([
        "success" => true,
        "message" => "تم حذف المنتج بنجاح",
        "products" => getProducts($pdo)
    ]);
} elseif ($action === 'list') {
    echo json_encode([
        "success" => true,
        "products" => getProducts($pdo)
    ]);
} else {
    echo json_encode(["success" => false, "message" => "طلب غير صالح"]);
}

function getProducts($pdo) {
    $stmt = $pdo->query("SELECT * FROM products");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
