<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استقبال البيانات
    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $product = $input['product'];
        if ($action === 'update') {
            // Processing image
            $imagePath = $product['image_path'] ?? null;
            if (isset($product['image']) && strpos($product['image'], 'data:image') === 0) {
                $image_parts = explode(";base64,", $product['image']);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.' . $image_type;
                $file = 'uploads/products/' . $fileName;
                if (!is_dir('uploads/products/')) {
                    mkdir('uploads/products/', 0777, true);
                }
                file_put_contents($file, $image_base64);
                $imagePath = $file;
            }

            // تعديل منتج
            $stmt = $pdo->prepare("UPDATE products SET name = ?, hasSizes = ?, price = ?, s_price = ?, m_price = ?, l_price = ?, stock = ?, barcode = ?, category_id = ?, image = ? WHERE id = ?");
            $stmt->execute([$product['name'], (int)$product['hasSizes'], $product['price'], $product['s_price'], $product['m_price'], $product['l_price'], $product['stock'], $product['barcode'], !empty($product['category_id']) ? (int)$product['category_id'] : null, $imagePath, $product['id']]);
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
            // Processing image
            $imagePath = null;
            if (isset($product['image']) && strpos($product['image'], 'data:image') === 0) {
                $image_parts = explode(";base64,", $product['image']);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.' . $image_type;
                $file = 'uploads/products/' . $fileName;
                if (!is_dir('uploads/products/')) {
                    mkdir('uploads/products/', 0777, true);
                }
                file_put_contents($file, $image_base64);
                $imagePath = $file;
            }

            $stmt = $pdo->prepare("INSERT INTO products (name, hasSizes, price, s_price, m_price, l_price, stock, barcode, category_id, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product['name'], (int)$product['hasSizes'], $product['price'], $product['s_price'], $product['m_price'], $product['l_price'], $product['stock'], $product['barcode'], !empty($product['category_id']) ? (int)$product['category_id'] : null, $imagePath]);
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
    $stmt = $pdo->query("
                SELECT 
                    p.*,
                    c.name AS category
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
            ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getProduct($pdo, $name)
{
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->execute(["%$name%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
