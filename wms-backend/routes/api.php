<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\StockOutController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\BarcodeDeviceController;
use App\Http\Controllers\Api\StockMovementHistoryController;

// Mobile/token auth endpoints (stateless)
Route::prefix('v1')->group(function () {
    Route::post('auth/login-mobile', [AuthController::class, 'loginMobile']);
    Route::post('auth/logout-mobile', [AuthController::class, 'logoutMobile'])->middleware('auth:sanctum');
});

// Temporary unauthenticated debug endpoint to check if a barcode exists.
// Remove or protect this in production.
Route::get('v1/check-barcode', function (Request $request) {
    try {
        $barcode = $request->query('barcode');
        $exists = \App\Models\Product::where('barcode', $barcode)->exists();
        return response()->json(['exists' => (bool) $exists]);
    } catch (\Exception $e) {
        Log::error('check-barcode failed: '.$e->getMessage());
        return response()->json(['exists' => false, 'error' => $e->getMessage()], 500);
    }
});

Route::prefix('v1')->middleware('web')->group(function () {
    // Auth (login moved to web routes to use session & CSRF for Sanctum SPA)
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Forgot / Reset password (public)
    Route::post('auth/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
    Route::post('auth/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

    // Products
    Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');
    // Suppliers
    Route::apiResource('suppliers', SupplierController::class)->middleware('auth:sanctum');
    
    // Stock In (POST for barcode-based operations)
    Route::post('stock-in', [StockInController::class, 'store'])->middleware('auth:sanctum');
    
    // Stock Out (POST for barcode-based operations)
    Route::post('stock-out', [StockOutController::class, 'store'])->middleware('auth:sanctum');
    // Backward-compatible alias (some frontend builds call /stock-outs)
    Route::post('stock-outs', [StockOutController::class, 'store'])->middleware('auth:sanctum');
    // Stock Out Feature (Alternative Clean Architecture endpoint)
    Route::post('stock-out-feature', [\App\Http\Controllers\Api\StockOutFeatureController::class, 'store'])->middleware('auth:sanctum');

    // Dashboard summary
    Route::get('dashboard/summary', [\App\Http\Controllers\Api\DashboardController::class, 'summary'])->middleware('auth:sanctum');
    
    // Inventory list (join inventories with products)
    Route::get('inventory', function (Request $request) {
        $barcode = $request->query('barcode');

        $items = \Illuminate\Support\Facades\DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->leftJoin('product_supplier', 'products.id', '=', 'product_supplier.product_id')
            ->leftJoin('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
            ->when($barcode, function ($q) use ($barcode) {
                $q->where('products.barcode', $barcode);
            })
            ->select(
                'inventories.product_id',
                'inventories.quantity',
                'inventories.reorder_level',
                'products.id as id',
                'products.sku',
                'products.barcode',
                'products.name',
                'products.sale_price',
                \Illuminate\Support\Facades\DB::raw('GROUP_CONCAT(DISTINCT suppliers.name SEPARATOR ", ") as supplier_names')
            )
            ->groupBy(
                'inventories.product_id',
                'inventories.quantity',
                'inventories.reorder_level',
                'products.id',
                'products.sku',
                'products.barcode',
                'products.name',
                'products.sale_price'
            )
            ->get();

        return response()->json($items);
    });

    // Low Stock Items
    Route::get('inventory/low-stock', function (Request $request) {
        $items = \Illuminate\Support\Facades\DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereRaw('inventories.quantity <= products.low_stock_threshold')
            ->select(
                'inventories.id',
                'inventories.product_id',
                'inventories.quantity as current_quantity',
                'inventories.reorder_level',
                'products.id as id',
                'products.sku as product_sku',
                'products.barcode as product_barcode',
                'products.name as product_name',
                'products.low_stock_threshold as threshold',
                'products.sale_price'
            )
            ->orderBy('inventories.quantity', 'asc')
            ->get();

        return response()->json($items);
    });
    
    // Stock Movement History (Admin)
    Route::get('stock-movements', [StockMovementHistoryController::class, 'index'])->middleware('auth:sanctum');
    // Stock movement logs (All authenticated users)
    Route::get('stock-movements/logs', [StockMovementHistoryController::class, 'logs'])->middleware('auth:sanctum');
    Route::get('stock-movements/export', [StockMovementHistoryController::class, 'export'])->middleware('auth:sanctum');
    
    // Users
    Route::apiResource('users', UserController::class)->middleware('auth:sanctum');
});
