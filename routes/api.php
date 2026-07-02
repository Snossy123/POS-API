<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmployeeAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

Route::middleware('license')->group(function () {
    Route::post('/auth/admin/login', [AdminAuthController::class, 'login']);
    Route::post('/auth/employee/login', [EmployeeAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::post('/products', [ProductController::class, 'handle']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::patch('/products/{product}/stock', [ProductController::class, 'adjustStock']);

        Route::post('/categories', [CategoryController::class, 'handle']);
        Route::get('/categories', [CategoryController::class, 'index']);

        Route::post('/employees', [EmployeeController::class, 'handle']);
        Route::get('/employees', [EmployeeController::class, 'index']);

        Route::get('/purchase-invoices/next-number', [PurchaseInvoiceController::class, 'nextNumber']);
        Route::post('/purchase-invoices', [PurchaseInvoiceController::class, 'store']);
        Route::get('/purchase-invoices', [PurchaseInvoiceController::class, 'index']);

        Route::get('/sales-invoices', [SalesInvoiceController::class, 'index']);
        Route::post('/sales-invoices', [SalesInvoiceController::class, 'store'])
            ->middleware('shift.open');
        Route::patch('/sales-invoices/{salesInvoice}/void', [SalesInvoiceController::class, 'void']);
        Route::post('/sales-invoices/{salesInvoice}/refund', [SalesInvoiceController::class, 'refund']);
        Route::post('/sales-invoices/{salesInvoice}/pay', [SalesInvoiceController::class, 'pay']);
        Route::patch('/sales-invoices/{salesInvoice}/items', [SalesInvoiceController::class, 'updateItems']);
        Route::patch('/sales-invoices/{salesInvoice}/payment-status', [SalesInvoiceController::class, 'updatePaymentStatus']);
        Route::post('/sales-invoices/{salesInvoice}/reprint', [SalesInvoiceController::class, 'reprint']);

        Route::get('/shifts', [ShiftController::class, 'index']);
        Route::get('/shifts/current', [ShiftController::class, 'current']);
        Route::post('/shifts/open', [ShiftController::class, 'open']);
        Route::post('/shifts/{shift}/close', [ShiftController::class, 'close']);
        Route::get('/shifts/{shift}/report', [ShiftController::class, 'report']);

        Route::get('/reports', [ReportController::class, 'index']);
    });
});
