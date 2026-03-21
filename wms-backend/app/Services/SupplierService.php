<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    /**
     * @return Collection<int, Supplier>
     */
    public function list(): Collection
    {
        return Supplier::query()->latest()->get();
    }

    public function getForShow(Supplier $supplier): Supplier
    {
        return $supplier->load(['products']);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier;
    }

    public function delete(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier) {
            // Defensive cleanup to avoid FK/pivot issues on environments without proper cascades.
            // - `product_supplier` pivot is expected to cascade, but we detach to be safe.
            // - `stock_movements.supplier_id` is nullable and should be set null on delete.
            try {
                $supplier->products()->detach();
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                DB::table('stock_movements')
                    ->where('supplier_id', $supplier->id)
                    ->update(['supplier_id' => null]);
            } catch (\Throwable $e) {
                // ignore
            }

            $supplier->delete();
        });
    }
}
