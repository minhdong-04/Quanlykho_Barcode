<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\StockInController;
use App\Http\Controllers\Api\V1\StockOutController;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Products
    Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class)->middleware('auth:sanctum');

    // Stock
    Route::post('stock-in', [StockInController::class, 'store'])->middleware('auth:sanctum');
    Route::post('stock-out', [StockOutController::class, 'store'])->middleware('auth:sanctum');
    // Alerts
    Route::get('alerts/low-stock', [\App\Http\Controllers\Api\V1\AlertsController::class, 'lowStock'])->middleware('auth:sanctum');
    Route::get('alerts', [\App\Http\Controllers\Api\V1\AlertsController::class, 'index'])->middleware('auth:sanctum');
    Route::patch('alerts/{id}/acknowledge', [\App\Http\Controllers\Api\V1\AlertsController::class, 'acknowledge'])->middleware('auth:sanctum');
    Route::delete('alerts/{id}', [\App\Http\Controllers\Api\V1\AlertsController::class, 'clear'])->middleware('auth:sanctum');
});
