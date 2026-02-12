<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/env_loader.php';
loadEnv(__DIR__ . '/.env');

require_once 'config/db.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try { 
    // Normalize path (remove trailing slash)
    $path = rtrim($path, '/');

    // Remove project folder if it exists (for production)
    $basePath = '/POS-API';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }

    // Route map
    $routes = [
        '/api/products'          => 'products/products.php',
        '/api/categories'        => 'products/categories.php',
        '/api/sales-invoices'    => 'sales-invoices/invoice.php',
        '/api/purchase-invoices' => 'purchase-invoices/invoice.php',
        '/api/reports'           => 'reports/reports.php',
        '/api/employees'         => 'employees/employees.php',
    ];

    // Route handling
    if (array_key_exists($path, $routes)) {
        require $routes[$path];
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        exit();
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
    exit();
}