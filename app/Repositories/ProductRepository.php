<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function all(array $filters = [])
    {
        return Product::query()->paginate(20);
    }
}
