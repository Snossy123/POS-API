<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            if(getProduct($pdo, $product['name'])){
                echo json_encode([
                    "success" => false,
                    "message" => "المنتج موجود مسبقاً",
                    "products" => getProducts($pdo)
                ]);
                exit();
            }
            $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, barcode, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$product['name'], $product['price'], $product['stock'], $product['barcode'], $product['category']]);
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
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'message' => 'Products fetched successfully',
        'products' => getProducts($pdo)
    ]);
    exit();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
function getProducts($pdo)
{
    $stmt = $pdo->query("SELECT * FROM products");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getProduct($pdo, $name)
{
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->execute(["%$name%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
