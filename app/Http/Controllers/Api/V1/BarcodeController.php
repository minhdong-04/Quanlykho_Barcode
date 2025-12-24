<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function scan(Request $request)
    {
        $this->validate($request, ['barcode' => 'required|string']);

        $product = Product::where('barcode', $request->barcode)
            ->orWhere('sku', $request->barcode)
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return $product;
    }
}
