<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'sku' => 'nullable|string',
            'barcode' => 'nullable|string',
            'description' => 'nullable|string',
            'reorder_level' => 'nullable|integer',
        ];
    }
}
