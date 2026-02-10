<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $employee = $input['employee'] ?? [];

        if ($action === 'update') {
            // تعديل بيانات الموظف
            $stmt = $pdo->prepare("
                UPDATE employees 
                SET name = ?, email = ?, password = ?, role = ?, phone = ?, salary = ?, hiring_date = ?, active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $employee['name'],
                $employee['email'],
                $employee['password'],
                $employee['role'],
                $employee['phone'],
                $employee['salary'],
                $employee['hiring_date'],
                $employee['active'] ? 1 : 0,
                $employee['id']
            ]);
            $message = "تم تحديث بيانات الموظف بنجاح";
        } else {
            // إضافة موظف جديد
            $stmt = $pdo->prepare("
                INSERT INTO employees (name, email, password, role, phone, salary, hiring_date, active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $employee['name'],
                $employee['email'],
                $employee['password'],
                $employee['role'],
                $employee['phone'],
                $employee['salary'],
                $employee['hiring_date'],
                $employee['active'] ? 1 : 0
            ]);
            $message = "تم إضافة الموظف بنجاح";
        }

        echo json_encode([
            "success" => true,
            "message" => $message,
            "employees" => getEmployees($pdo)
        ]);
    } elseif ($action === 'delete') {
        $id = $input['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode([
                "success" => true,
                "message" => "تم حذف الموظف بنجاح",
                "employees" => getEmployees($pdo)
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "لم يتم إرسال معرف الموظف"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "طلب غير صالح"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'message' => 'تم جلب بيانات الموظفين بنجاح',
        'employees' => getEmployees($pdo)
    ]);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'طريقة الطلب غير مسموحة']);
}

function getEmployees($pdo) {
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
