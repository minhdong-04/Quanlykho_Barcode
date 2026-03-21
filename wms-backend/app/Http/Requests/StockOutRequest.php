<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => 'required|string|exists:products,barcode',
            'quantity' => 'required|integer|min:1',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'barcode.exists' => 'Product not found with this barcode',
            'quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
