<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Inventory\StockOutService;

class StockOutController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        try {
            $service = new StockOutService();
            $movement = $service->handle(
                (int) $data['product_id'],
                (int) $data['warehouse_id'],
                (int) $data['quantity'],
                $data['notes'] ?? null,
                $request->user()?->id ?? null
            );

            return response()->json($movement, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing request'], 500);
        }
    }
}
