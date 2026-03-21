<?php

namespace App\Repositories;

use App\Models\Inventory;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function lockForUpdate(int $productId): ?Inventory
    {
        return Inventory::where('product_id', $productId)->lockForUpdate()->firstOrFail();
    }
}
