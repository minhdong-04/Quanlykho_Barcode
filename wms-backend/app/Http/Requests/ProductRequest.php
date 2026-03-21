<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admin and manager can create/update products
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        $role = $user->role ?? $user->type ?? '';
        return in_array($role, ['admin', 'manager']);
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        $rules = [
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $productId],
            'barcode' => ['required', 'string', 'max:100', 'unique:products,barcode,' . $productId],
            'name' => ['required', 'string', 'max:255'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['integer', 'exists:suppliers,id'],
            'unit' => ['nullable', 'string', 'max:20'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'reorder_level' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'Mã SKU là bắt buộc',
            'sku.unique' => 'Mã SKU đã tồn tại',
            'barcode.required' => 'Mã vạch là bắt buộc',
            'barcode.unique' => 'Mã vạch đã tồn tại',
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'purchase_price.numeric' => 'Giá mua phải là số',
            'purchase_price.min' => 'Giá mua phải lớn hơn hoặc bằng 0',
            'sale_price.numeric' => 'Giá bán phải là số',
            'sale_price.min' => 'Giá bán phải lớn hơn hoặc bằng 0',
        ];
    }
}
