<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockOutService
{
	/**
	 * Remove stock from inventory and record movement.
	 *
	 * @throws \RuntimeException when insufficient stock
	 * @return StockMovement
	 */
	public function handle(int $productId, int $warehouseId, int $quantity, ?string $notes = null, ?int $userId = null)
	{
		return DB::transaction(function () use ($productId, $warehouseId, $quantity, $notes, $userId) {
			$inventory = Inventory::where('product_id', $productId)
				->where('warehouse_id', $warehouseId)
				->lockForUpdate()
				->first();

			if (! $inventory || $inventory->quantity < $quantity) {
				throw new \RuntimeException('Insufficient stock');
			}

			$inventory->quantity -= $quantity;
			$inventory->save();

			$movement = StockMovement::create([
				'product_id' => $productId,
				'warehouse_id' => $warehouseId,
				'quantity' => $quantity,
				'movement_type' => 'out',
				'notes' => $notes,
				'user_id' => $userId,
			]);

			return $movement;
		});
	}
}

