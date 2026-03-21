<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterStockMovementsRequest;
use App\Http\Requests\StockMovementLogsRequest;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;

class StockMovementHistoryController extends Controller
{
    public function __construct(
        private StockMovementService $service
    ) {}

    /**
     * Get filtered stock movements with advanced search
     */
    public function index(FilterStockMovementsRequest $request): JsonResponse
    {
        $filters = $request->getFilters();
        $movements = $this->service->getFilteredMovements($filters);
        $formatted = $this->service->formatMovements($movements);

        return response()->json($formatted);
    }

    /**
     * Recent stock movement logs for scanner sidebar.
     * Requirement: all authenticated users can view.
     */
    public function logs(StockMovementLogsRequest $request): JsonResponse
    {
        $filters = $request->getFilters();
        $movements = $this->service->getFilteredMovements($filters);
        $formatted = $this->service->formatMovements($movements);

        return response()->json($formatted);
    }

    /**
     * Export stock movements as CSV
     */
    public function export(FilterStockMovementsRequest $request): JsonResponse
    {
        $filters = $request->getFilters();
        $csv = $this->service->exportAsCSV($filters);

        return response()->json([
            'csv' => $csv,
            'filename' => 'stock-movements-' . now()->format('Y-m-d_His') . '.csv',
        ]);
    }
}
