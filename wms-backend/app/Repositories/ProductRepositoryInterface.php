<?php

namespace App\Repositories;

use App\Models\Product;

interface ProductRepositoryInterface
{
    public function findByBarcode(string $barcode): ?Product;
}
