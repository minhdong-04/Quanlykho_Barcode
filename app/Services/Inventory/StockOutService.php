<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\StockLog;
use App\Models\LowStockAlert;
use App\Jobs\LowStockAlertJob;
use Illuminate\Support\Facades\DB;

class StockOutService
{
	/**
	 * Remove stock from inventory and record movement.
	 *
	 * @throws \RuntimeException when insufficient stock
	 * @return StockLog
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

			// If inventory is at or below reorder level, record an alert and dispatch job
			if ($inventory->quantity <= ($inventory->reorder_level ?? 0)) {
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

			$movement = StockLog::create([
				'product_id' => $productId,
				'warehouse_id' => $warehouseId,
				'quantity_change' => -1 * abs($quantity),
				'action' => 'out',
				'reference_id' => null,
				'notes' => $notes,
				'user_id' => $userId,
			]);

			return $movement;
		});
	}
}

