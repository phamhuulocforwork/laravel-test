<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::prefix('export')->group(function () {
    Route::get('products/excel', [ExportController::class, 'exportProductsExcel']);
    Route::get('products/pdf', [ExportController::class, 'exportProductsPdf']);
    Route::get('products/pdf/download', [ExportController::class, 'exportProductsPdfDownload']);
});

Route::apiResource('customers', CustomerController::class);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Product routes
    Route::apiResource('products', ProductController::class);

    // Order routes
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders/{id}/items', [OrderController::class, 'addItem']);
    Route::put('orders/{id}/items/{itemId}', [OrderController::class, 'updateItem']);
    Route::delete('orders/{id}/items/{itemId}', [OrderController::class, 'deleteItem']);
});

