<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\ReportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Legacy action-based routes
Route::post('/products', [ProductController::class, 'handle']);
Route::get('/products', [ProductController::class, 'index']); // Optional REST support

Route::post('/categories', [CategoryController::class, 'handle']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::post('/employees', [EmployeeController::class, 'handle']);
Route::get('/employees', [EmployeeController::class, 'index']);

// Invoice routes (REST-ish)
Route::post('/purchase-invoices', [PurchaseInvoiceController::class, 'store']);
Route::get('/purchase-invoices', [PurchaseInvoiceController::class, 'index']);

Route::post('/sales-invoices', [SalesInvoiceController::class, 'store']);
Route::get('/sales-invoices', [SalesInvoiceController::class, 'index']);

// Reports
Route::get('/reports', [ReportController::class, 'index']);
