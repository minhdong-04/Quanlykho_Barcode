<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockInRequest;
use App\Services\StockInService;
use App\Exceptions\Domain\InvalidBarcodeException;
use Illuminate\Http\JsonResponse;

class StockInController extends Controller
{
    public function __construct(
        private StockInService $service
    ) {}
    
    /**
     * Process stock in transaction (primary endpoint)
     */
    public function store(StockInRequest $request): JsonResponse
    {
        try {
            $result = $this->service->stockIn(
                $request->barcode,
                $request->quantity,
                $request->input('supplier_id'),
                $request->notes ?? null
            );
            
            return response()->json([
                'message' => 'Stock in successful',
                'data' => $result,
            ], 201);
            
        } catch (InvalidBarcodeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            logger()->error('StockInController@store error: '.$e->getMessage());
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}