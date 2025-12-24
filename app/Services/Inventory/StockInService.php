<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\LowStockAlert;
use App\Jobs\LowStockAlertJob;
use Illuminate\Support\Facades\DB;

class StockInService
{
	/**
	 * Add stock to inventory and record movement.
	 *
	 * @return StockMovement
	 */
	public function handle(int $productId, int $warehouseId, int $quantity, ?int $supplierId = null, ?string $notes = null, ?int $userId = null)
	{
		return DB::transaction(function () use ($productId, $warehouseId, $quantity, $supplierId, $notes, $userId) {
			$inventory = Inventory::firstOrCreate([
				'product_id' => $productId,
				'warehouse_id' => $warehouseId,
			], ['quantity' => 0, 'reorder_level' => 0]);

			$inventory->quantity += $quantity;
			$inventory->save();

			// If inventory is at or below reorder level, record an alert and dispatch job
			if ($inventory->quantity <= ($inventory->reorder_level ?? 0)) {
				// create alert record if none unnotified exists for this product
				$existing = LowStockAlert::where('product_id', $productId)->where('notified', false)->first();
				if (! $existing) {
					LowStockAlert::create([
						'product_id' => $productId,
						'current_quantity' => $inventory->quantity,
						'reorder_level' => $inventory->reorder_level ?? 0,
						'notified' => false,
					]);
					LowStockAlertJob::dispatch($productId);
				}
			}

			$movement = StockMovement::create([
				'product_id' => $productId,
				'warehouse_id' => $warehouseId,
				'quantity' => $quantity,
				'movement_type' => 'in',
				'supplier_id' => $supplierId,
				'notes' => $notes,
				'user_id' => $userId,
			]);

			return $movement;
		});
	}
}

