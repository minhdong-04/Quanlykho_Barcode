<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string',
            'sku' => 'sometimes|nullable|string',
            'barcode' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'reorder_level' => 'sometimes|nullable|integer',
        ];
    }
}
