<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\Product;

class InventoryService
{
    public function checkLowStock($threshold = null)
    {
        $q = Inventory::with('product','warehouse')
            ->whereColumn('quantity', '<=', 'reorder_level');

        if ($threshold !== null) {
            $q->where('quantity', '<=', $threshold);
        }

        return $q->get();
    }
}
