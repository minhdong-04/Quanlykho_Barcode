<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StockService
{
    public function stockIn(array $payload): bool
    {
        // placeholder: use transactions and create StockIn, Inventory updates, logs
        DB::beginTransaction();
        try {
            // implement actual logic
            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function stockOut(array $payload): bool
    {
        return $this->stockIn($payload);
    }
}
