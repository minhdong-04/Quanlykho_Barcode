<?php

namespace App\Services;

use App\Repositories\StockMovementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementService
{
    public function __construct(
        private StockMovementRepositoryInterface $repository
    ) {}

    /**
     * Get filtered stock movements with pagination
     */
    public function getFilteredMovements(array $filters): LengthAwarePaginator
    {
        return $this->repository->filter($filters);
    }

    /**
     * Format movement data for API response
     */
    public function formatMovements(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'next' => $paginator->nextPageUrl(),
                'prev' => $paginator->previousPageUrl(),
            ],
        ];
    }

    /**
     * Export movements as CSV (for admin downloads)
     */
    public function exportAsCSV(array $filters): string
    {
        $movements = $this->repository->filter(['per_page' => 10000, ...$filters]);
        
        $csv = "Mã sản phẩm,Tên sản phẩm,Barcode,Nhà cung cấp,Loại giao dịch,Số lượng,Người thực hiện,Thời gian\n";
        
        foreach ($movements as $movement) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%d,%s,%s\n",
                $movement->product->sku ?? '',
                $movement->product->name ?? '',
                $movement->product->barcode ?? '',
                $movement->supplier->name ?? '',
                $movement->type === 'in' ? 'Nhập' : 'Xuất',
                $movement->quantity,
                $movement->user->name ?? 'Hệ thống',
                $movement->created_at->format('Y-m-d H:i:s')
            );
        }
        
        return $csv;
    }
}
