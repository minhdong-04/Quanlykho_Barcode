<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Inventory\InventoryService;

class LowStockAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:low-stock {--threshold= : Optional numeric threshold to override reorder_level}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List inventory items at or below their reorder level (low stock)';

    public function handle()
    {
        $threshold = $this->option('threshold');

        $service = new InventoryService();
        $items = $service->checkLowStock($threshold !== null ? (int) $threshold : null);

        if ($items->isEmpty()) {
            $this->info('No low stock items found.');
            return 0;
        }

        $rows = $items->map(function ($inv) {
            return [
                'product_id' => $inv->product_id,
                'product' => $inv->product->name ?? '',
                'warehouse' => $inv->warehouse->name ?? '',
                'quantity' => $inv->quantity,
                'reorder_level' => $inv->reorder_level,
            ];
        })->toArray();

        $this->table(['Product ID','Product','Warehouse','Quantity','Reorder Level'], $rows);

        return 0;
    }
}
