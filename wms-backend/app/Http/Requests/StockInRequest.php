<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StockInRequest extends FormRequest
{

public function rules(): array
{
    return [
        // allow barcode even when product doesn't exist; service will create product if needed
        'barcode' => 'required|string',
        'quantity' => 'required|integer|min:1|max:10000',
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'notes' => 'nullable|string|max:500',
    ];
}

public function messages(): array
{
    return [
        'barcode.required' => 'Vui lòng cung cấp mã vạch',
        'quantity.required' => 'Vui lòng nhập số lượng',
    ];
}
}

   

