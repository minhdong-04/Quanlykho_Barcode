<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockOutRequest;
use App\Services\StockOutFeatureService;
use Illuminate\Http\JsonResponse;
use App\Models\IdempotencyKey;

class StockOutFeatureController extends Controller
{
    public function __construct(private StockOutFeatureService $service) {}

    public function store(StockOutRequest $request): JsonResponse
    {
        $data = $request->validated();

        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('request_id');
        if ($idempotencyKey) {
            try {
                IdempotencyKey::create([
                    'key' => $idempotencyKey,
                    'user_id' => $request->user()?->id,
                    'request_method' => $request->method(),
                    'request_path' => $request->path(),
                    'response_data' => null,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                $existing = IdempotencyKey::where('key', $idempotencyKey)->first();
                if ($existing) {
                    if ($existing->response_data) {
                        return response()->json($existing->response_data, 200);
                    }
                    return response()->json(['message' => 'Request is being processed'], 409);
                }
            }
        }

        $ability = $request->user()->currentAccessToken()?->abilities[0] ?? null;
        $result = $this->service->stockOut($data['barcode'], $data['quantity'], $ability, $data['supplier_id'] ?? null);

        if ($idempotencyKey) {
            IdempotencyKey::where('key', $idempotencyKey)->update(['response_data' => $result, 'user_id' => $request->user()?->id]);
        }

        return response()->json($result);
    }
}
