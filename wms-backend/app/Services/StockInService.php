<?php

namespace App\Services;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\InventoryRepositoryInterface;
use App\Repositories\StockMovementRepositoryInterface;
use App\Jobs\CheckLowStockAlert;
use App\Models\Product as ProductModel;
use App\Exceptions\Domain\InvalidBarcodeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class StockInService
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
        private InventoryRepositoryInterface $inventoryRepo,
        private StockMovementRepositoryInterface $movementRepo
    ) {}

    public function stockIn(string $barcode, int $quantity, ?int $supplierId = null, ?string $notes = null): array
    {
        return DB::transaction(function () use ($barcode, $quantity, $supplierId, $notes) {
            
            // 1. Find product
            $product = $this->productRepo->findByBarcode($barcode);

            if (! $product) {
                // Auto-create a minimal product record if not found (scanner-friendly behavior)
                // Generate a short unique SKU
                $sku = 'SKU-' . strtoupper(Str::random(6));
                $tries = 0;
                while (ProductModel::where('sku', $sku)->exists() && $tries++ < 6) {
                    $sku = 'SKU-' . strtoupper(Str::random(6));
                }

                $product = ProductModel::create([
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'name' => "Unknown product {$barcode}",
                    'unit' => null,
                    'purchase_price' => 0,
                    'sale_price' => 0,
                    'quantity' => 0,
                    'low_stock_threshold' => 10,
                ]);
            }

            // 2. Lock inventory row (some installs use a separate `inventories` table).
            $inventory = null;
            try {
                $inventory = $this->inventoryRepo->lockForUpdate($product->id);
            } catch (\Exception $e) {
                // repository may fail if table missing; we'll fallback to product quantity
                $inventory = null;
            }

            // 3. Update quantity. If no separate inventory record exists, update product.quantity directly.
            if ($inventory) {
                $oldQty = $inventory->quantity;
                $inventory->increment('quantity', $quantity);
                $newQty = $inventory->quantity;
            } else {
                // fallback: lock the product row and update its `quantity` column
                $prodModel = \App\Models\Product::where('id', $product->id)->lockForUpdate()->first();
                if (! $prodModel) {
                    throw new \RuntimeException("Inventory record not found and product lock failed");
                }
                $oldQty = $prodModel->quantity;
                $prodModel->increment('quantity', $quantity);
                $newQty = $prodModel->quantity;
            }

            
            // 4. Create stock movement (primary audit trail)
            $userId = auth()->id() ?? \App\Models\User::orderBy('id')->value('id') ?? 1;

            $movementData = [
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'type'       => 'in',
                'user_id'    => $userId,
            ];

            if (Schema::hasColumn('stock_movements', 'supplier_id')) {
                $movementData['supplier_id'] = $supplierId;
            }

            $movement = $this->movementRepo->create($movementData);

            // 5. Dispatch async alert job
            CheckLowStockAlert::dispatch($product->id);

            return [
                'product'      => $product,
                'old_quantity' => $oldQty,
                'new_quantity' => $newQty,
                'movement'     => $movement,
            ];
        });
    }
}
