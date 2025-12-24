<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function lowStock(Request $request)
    {
        $service = new InventoryService();
        return response()->json($service->checkLowStock());
    }

    public function index()
    {
        return \App\Models\LowStockAlert::query()->orderBy('created_at', 'desc')->get();
    }

    public function acknowledge($id)
    {
        $alert = \App\Models\LowStockAlert::findOrFail($id);
        $alert->notified = true;
        $alert->save();
        return response()->json(['message' => 'Alert acknowledged']);
    }

    public function clear($id)
    {
        $alert = \App\Models\LowStockAlert::findOrFail($id);
        $alert->delete();
        return response()->json(['message' => 'Alert cleared']);
    }
}
