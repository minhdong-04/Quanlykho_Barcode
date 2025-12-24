<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Inventory\StockInService;

class StockInController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
        ]);
        try {
            $service = new StockInService();
            $movement = $service->handle(
                (int) $data['product_id'],
                (int) $data['warehouse_id'],
                (int) $data['quantity'],
                isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                $data['notes'] ?? null,
                $request->user()?->id ?? null
            );

            return response()->json($movement, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
