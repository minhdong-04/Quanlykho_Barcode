<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\Domain\InsufficientStockException;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\InventoryRepositoryInterface;
use App\Repositories\StockMovementRepositoryInterface;

class StockOutService
{
    protected $productRepo;
    protected $inventoryRepo;
    protected $movementRepo;

    public function __construct(ProductRepositoryInterface $productRepo, InventoryRepositoryInterface $inventoryRepo, StockMovementRepositoryInterface $movementRepo)
    {
        $this->productRepo = $productRepo;
        $this->inventoryRepo = $inventoryRepo;
        $this->movementRepo = $movementRepo;
    }

    public function stockOut(string $barcode, int $quantity, ?int $supplierId = null): array
    {
        return DB::transaction(function() use ($barcode, $quantity, $supplierId) {
            $product = $this->productRepo->findByBarcode($barcode);
            $inventory = $this->inventoryRepo->lockForUpdate($product->id);

            // Kiểm tra tồn kho trước khi trừ
            if ($inventory->quantity < $quantity) {
                throw new InsufficientStockException(
                    "Not enough stock. Available: {$inventory->quantity}, Requested: {$quantity}"
                );
            }

            $beforeQty = $inventory->quantity;
            $inventory->decrement('quantity', $quantity);
            $afterQty = $beforeQty - $quantity;

            // Create stock movement record (primary audit trail)
            $userId = auth()->id() ?? \App\Models\User::orderBy('id')->value('id') ?? 1;

            $movementData = [
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'type'       => 'out',
                'user_id'    => $userId,
            ];

            if (Schema::hasColumn('stock_movements', 'supplier_id')) {
                $movementData['supplier_id'] = $supplierId;
            }

            $movement = $this->movementRepo->create($movementData);

            return [
                'product_id' => $product->id,
                'barcode' => $barcode,
                'quantity' => $quantity,
                'new_quantity' => $afterQty,
                'movement' => $movement,
            ];
        });
    }
}
