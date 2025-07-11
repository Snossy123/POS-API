<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/db.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/api/products' || $path === '/api/products/') {
    require 'products/products.php';
} else if ($path === '/api/categories' || $path === '/api/categories/') {
    require 'products/categories.php';
} else if ($path === '/api/sales-invoices' || $path === '/api/sales-invoices/') {
    require 'sales-invoices/invoice.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Resource not found']);
    exit();
}