<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        try {
            return response()->json(Product::paginate(20));
        } catch (\Throwable $e) {
            logger()->error('Product index failed', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'index_failed'], 500);
        }
    }

    public function store(Request $request)
    {
        logger()->info('Product store payload', $request->all());

        $data = $request->all();
        $rules = (new StoreProductRequest())->rules();
        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        logger()->info('Product store extracted', $data);
        try {
            $product = Product::create($data);
            logger()->info('Product created', $product->toArray());
            return response()->json($product, 201);
        } catch (\Throwable $e) {
            logger()->error('Product create failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'create_failed'], 500);
        }
    }
    public function show($id)
    {
        logger()->info('Product show start', ['route_param' => request()->route('product')]);
        $product = Product::findOrFail($id);
        logger()->info('Product show id', ['id' => $product->id ?? null]);
        logger()->info('Product attributes', $product->getAttributes());
        try {
            file_put_contents(storage_path('logs/debug_product_show.json'), json_encode($product->toArray(), JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        } catch (\Throwable $e) {
            logger()->error('write debug file failed', ['err' => $e->getMessage()]);
        }
        return response()->json($product->toArray());
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->only(['name','sku','barcode','description','reorder_level']));
        return response()->json($product->fresh());
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->noContent();
    }

}
