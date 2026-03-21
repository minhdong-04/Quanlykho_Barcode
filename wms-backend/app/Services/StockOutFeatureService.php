<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\InventoryRepositoryInterface;
use App\Repositories\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Domain\InsufficientStockException;
use Illuminate\Support\Str;
use App\Models\StockOut;
use Illuminate\Support\Facades\Schema;

class StockOutFeatureService
{
    protected $productRepo;
    protected $inventoryRepo;
    protected $movementRepo;

    public function __construct(
        ProductRepositoryInterface $productRepo,
        InventoryRepositoryInterface $inventoryRepo,
        StockMovementRepositoryInterface $movementRepo
    ) {
        $this->productRepo = $productRepo;
        $this->inventoryRepo = $inventoryRepo;
        $this->movementRepo = $movementRepo;
    }

    public function stockOut(string $barcode, int $quantity, string $ability, ?int $supplierId = null): array
    {
        $user = Auth::user();
        if (!$user || !$user->tokenCan($ability)) {
            abort(403, 'Forbidden: insufficient ability');
        }

        return DB::transaction(function () use ($barcode, $quantity, $user, $supplierId) {
            $product = $this->productRepo->findByBarcode($barcode);
            $inventory = $this->inventoryRepo->lockForUpdate($product->id);

            if ($inventory->quantity < $quantity) {
                throw new InsufficientStockException(
                    "Not enough stock. Available: {$inventory->quantity}, Requested: {$quantity}"
                );
            }

            $beforeQty = $inventory->quantity;
            $inventory->decrement('quantity', $quantity);
            $afterQty = $beforeQty - $quantity;

            $userId = $user->id ?? \App\Models\User::orderBy('id')->value('id') ?? 1;

            $movementData = [
                'product_id' => $product->id,
                'user_id' => $userId,
                'type' => 'out',
                'quantity' => $quantity,
                'note' => 'Stock out via API',
            ];

            if (Schema::hasColumn('stock_movements', 'supplier_id')) {
                $movementData['supplier_id'] = $supplierId;
            }

            $movement = $this->movementRepo->create($movementData);

            // persist stock_out record
            try {
                $ref = 'OUT-' . strtoupper(Str::random(8));
                $stockOut = StockOut::create([
                    'reference_code' => $ref,
                    'created_by' => $userId,
                    'note' => 'Processed by feature API',
                ]);
            } catch (\Exception $e) {
                logger()->error('Failed to create StockOut record: '.$e->getMessage());
                $stockOut = null;
            }

            return [
                'product_id' => $product->id,
                'barcode' => $barcode,
                'quantity' => $quantity,
                'new_quantity' => $afterQty,
                'movement_id' => $movement->id,
                'stock_out_id' => $stockOut->id ?? null,
            ];
        });
    }
}
