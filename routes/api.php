<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\BarcodeController;
use App\Http\Controllers\Api\V1\StockInController;
use App\Http\Controllers\Api\V1\StockOutController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Services\Inventory\InventoryService;
use App\Http\Controllers\Api\V1\AlertController;

Route::prefix('api')->group(function () {
    Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    // Public endpoints
    Route::post('barcode/scan', [BarcodeController::class, 'scan']);

    // Protected resource endpoints (require token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('products', ProductController::class);

        Route::post('stock/in', [StockInController::class, 'store']);
        Route::post('stock/out', [StockOutController::class, 'store']);

        Route::get('inventory/low-stock', function () {
            $service = new InventoryService();
            return $service->checkLowStock();
        });
        Route::get('alerts/low-stock', [AlertController::class, 'lowStock']);
    });
    });
});
