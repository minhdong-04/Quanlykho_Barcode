<?php

namespace App\Repositories;

use App\Models\StockLog;

class StockLogRepository
{
    public function create(array $data): StockLog
    {
        return StockLog::create($data);
    }

    public function forProduct(int $productId)
    {
        return StockLog::where('product_id', $productId)->get();
    }
}
