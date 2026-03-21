<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StockOutService;
use App\Http\Requests\StockOutRequest;
use Illuminate\Http\JsonResponse;
use App\Exceptions\Domain\InsufficientStockException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StockOutController extends Controller {
    public function __construct(private StockOutService $service) {}

    /**
     * Process stock out transaction (primary endpoint)
     */
    public function store(StockOutRequest $request): JsonResponse
    {
        try {
            $result = $this->service->stockOut(
                $request->barcode,
                (int) $request->quantity,
                $request->input('supplier_id')
            );
            
            return response()->json([
                'message' => 'Stock out successful',
                'data' => $result,
            ], 201);
            
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            logger()->error('StockOutController@store error: '.$e->getMessage());
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}
