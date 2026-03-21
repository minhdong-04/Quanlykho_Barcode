<?php
namespace App\Repositories;
use App\Models\Product;
class ProductRepository implements ProductRepositoryInterface
{

public function findByBarcode(string $barcode): ?Product
{
        return Product::where('barcode', $barcode)->firstOrFail();
}

}
