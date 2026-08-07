<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseRequestController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // ---- Users ----
        Route::middleware('permission:users.view')->get('users', [UserController::class, 'index']);
        Route::middleware('permission:users.update')->put('users/{user}/roles', [UserController::class, 'assignRoles']);

        // ---- Customers ----
        Route::get('customers', [CustomerController::class, 'index'])->middleware('permission:customers.view');
        Route::post('customers', [CustomerController::class, 'store'])->middleware('permission:customers.create');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:customers.view');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete');

        // ---- Products & Inventory ----
        Route::get('products', [ProductController::class, 'index'])->middleware('permission:products.view');
        Route::post('products', [ProductController::class, 'store'])->middleware('permission:products.create');
        Route::get('products/low-stock', [ProductController::class, 'lowStock'])->middleware('permission:products.view');
        Route::get('products/{product}', [ProductController::class, 'show'])->middleware('permission:products.view');
        Route::put('products/{product}', [ProductController::class, 'update'])->middleware('permission:products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.delete');
        Route::get('products/{product}/stock-movements', [ProductController::class, 'movements'])->middleware('permission:products.view');
        Route::post('products/{product}/stock', [ProductController::class, 'adjustStock'])->middleware('permission:products.manage_stock');

        // ---- Purchase Requests ----
        Route::get('purchase-requests', [PurchaseRequestController::class, 'index'])->middleware('permission:purchase_requests.view');
        Route::post('purchase-requests', [PurchaseRequestController::class, 'store'])->middleware('permission:purchase_requests.create');
        Route::get('purchase-requests/{purchase_request}', [PurchaseRequestController::class, 'show'])->middleware('permission:purchase_requests.view');
        Route::put('purchase-requests/{purchase_request}', [PurchaseRequestController::class, 'update'])->middleware('permission:purchase_requests.update');
        Route::post('purchase-requests/{purchase_request}/approve', [PurchaseRequestController::class, 'approve'])->middleware('permission:purchase_requests.approve');
        Route::post('purchase-requests/{purchase_request}/reject', [PurchaseRequestController::class, 'reject'])->middleware('permission:purchase_requests.approve');

        // ---- Invoices ----
        Route::get('invoices', [InvoiceController::class, 'index'])->middleware('permission:invoices.view');
        Route::post('invoices', [InvoiceController::class, 'store'])->middleware('permission:invoices.create');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('permission:invoices.view');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->middleware('permission:invoices.update');
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->middleware('permission:invoices.mark_paid');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('permission:invoices.delete');
    });
});