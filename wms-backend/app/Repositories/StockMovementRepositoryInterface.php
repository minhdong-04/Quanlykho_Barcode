<?php
namespace App\Repositories;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    public function create(array $data): StockMovement;
    public function findByProduct(int $productId): Collection;
    public function filter(array $filters): LengthAwarePaginator;
}