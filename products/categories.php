<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استلام البيانات من React
    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $category = $input['category'];
        if ($action === 'update') {
            // تعديل الفئة
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, color = ? WHERE id = ?");
            $stmt->execute([$category['name'], $category['description'], $category['color'], $category['id']]);
            $message = "تم تحديث الفئة بنجاح";
        } else {
            // إضافة فئة جديدة
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, color) VALUES (?, ?, ?)");
            $stmt->execute([$category['name'], $category['description'], $category['color']]);
            $message = "تم إضافة الفئة بنجاح";
        }
        echo json_encode([
            "success" => true,
            "message" => $message,
            "categories" => getCategories($pdo)
        ]);
    } elseif ($action === 'delete') {
        $id = $input['id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode([
            "success" => true,
            "message" => "تم حذف الفئة بنجاح",
            "categories" => getCategories($pdo)
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "طلب غير صالح"]);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'message' => 'Categories fetched successfully',
        'categories' => getCategories($pdo)
    ]);
    exit();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}

function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
