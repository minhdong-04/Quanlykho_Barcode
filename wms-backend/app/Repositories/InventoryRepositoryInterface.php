<?php

namespace App\Repositories;

use App\Models\Inventory;

interface InventoryRepositoryInterface
{
    public function lockForUpdate(int $productId): ?Inventory;
}
