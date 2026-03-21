<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepo
    ) {}

    /**
     * Get all products with optional filters
     */
    public function getAllProducts(array $filters = []): Collection
    {
        $query = Product::with(['inventory', 'suppliers']); // Eager load inventory + suppliers

        // Search by name or SKU
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by barcode
        if (!empty($filters['barcode'])) {
            $query->where('barcode', $filters['barcode']);
        }

        // Filter by low stock (check against inventory quantity)
        if (!empty($filters['low_stock'])) {
            $query->whereHas('inventory', function($q) {
                $q->whereRaw('inventory.quantity <= products.low_stock_threshold');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get product by ID
     */
    public function getProductById(int $id): ?Product
    {
        $product = Product::with(['inventory', 'suppliers'])->find($id);
        if (!$product) {
            return null;
        }

        // Convenience fields for FE forms and detail screens.
        // "supplier_ids" comes from the pivot table `product_supplier`.
        $product->setAttribute('supplier_ids', $product->suppliers->pluck('id')->values());
        $product->setAttribute('supplier_names', $product->suppliers->pluck('name')->values());

        return $product;
    }

    /**
     * Create new product
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function() use ($data) {
            $createData = [
                'sku' => $data['sku'],
                'barcode' => $data['barcode'],
                'name' => $data['name'],
                'unit' => $data['unit'] ?? 'pcs',
                'purchase_price' => $data['purchase_price'] ?? 0,
                'sale_price' => $data['sale_price'] ?? 0,
                'quantity' => 0, // New products start with 0 quantity
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 10,
            ];

            $product = Product::create($createData);

            // Create initial inventory record
            DB::table('inventories')->insert([
                'product_id' => $product->id,
                'quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('product_supplier') && !empty($data['supplier_ids']) && is_array($data['supplier_ids'])) {
                $supplierIds = array_values(array_unique(array_filter($data['supplier_ids'], fn ($v) => $v !== null && $v !== '')));
                $product->suppliers()->sync($supplierIds);
            }

            $fresh = $product->fresh()->load(['inventory', 'suppliers']);
            $fresh->setAttribute('supplier_ids', $fresh->suppliers->pluck('id')->values());
            $fresh->setAttribute('supplier_names', $fresh->suppliers->pluck('name')->values());
            return $fresh;
        });
    }

    /**
     * Update existing product
     */
    public function updateProduct(int $id, array $data): Product
    {
        return DB::transaction(function() use ($id, $data) {
            $product = Product::findOrFail($id);
            
            $product->update([
                'sku' => $data['sku'] ?? $product->sku,
                'barcode' => $data['barcode'] ?? $product->barcode,
                'name' => $data['name'] ?? $product->name,
                'unit' => $data['unit'] ?? $product->unit,
                'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                'sale_price' => $data['sale_price'] ?? $product->sale_price,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? $product->low_stock_threshold,
            ]);

            if (Schema::hasTable('product_supplier') && array_key_exists('supplier_ids', $data) && is_array($data['supplier_ids'])) {
                $supplierIds = array_values(array_unique(array_filter($data['supplier_ids'], fn ($v) => $v !== null && $v !== '')));
                $product->suppliers()->sync($supplierIds);
            }

            $fresh = $product->fresh()->load(['inventory', 'suppliers']);
            $fresh->setAttribute('supplier_ids', $fresh->suppliers->pluck('id')->values());
            $fresh->setAttribute('supplier_names', $fresh->suppliers->pluck('name')->values());
            return $fresh;
        });
    }

    /**
     * Delete product
     */
    public function deleteProduct(int $id): bool
    {
        return DB::transaction(function() use ($id) {
            $product = Product::findOrFail($id);
            
            // Check if product has inventory (prevent deletion if stock exists)
            $inventory = DB::table('inventories')
                ->where('product_id', $id)
                ->first();
            
            if ($inventory && $inventory->quantity > 0) {
                throw new \RuntimeException('Cannot delete product with existing inventory. Current quantity: ' . $inventory->quantity);
            }

            return $product->delete();
        });
    }
}
