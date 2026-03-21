<?php
namespace App\Repositories;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }
    
    public function findByProduct(int $productId): Collection
    {
        return StockMovement::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Filter stock movements with advanced search and pagination
     */
    public function filter(array $filters): LengthAwarePaginator
    {
        $query = StockMovement::query()
            ->with(['product', 'user', 'supplier']);

        // Search by product name or barcode
        if (!empty($filters['search'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('barcode', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filter by type (in/out)
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by product
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Filter by supplier
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $filters['per_page'] ?? 20;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}