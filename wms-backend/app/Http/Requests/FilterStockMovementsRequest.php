<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterStockMovementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->tokenCan('admin');
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'type' => 'nullable|in:in,out',
            'user_id' => 'nullable|integer|exists:users,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'date_from' => 'nullable|date|date_format:Y-m-d',
            'date_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from',
            'sort_by' => 'nullable|in:created_at,quantity,type',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'Ngày kết thúc phải >= ngày bắt đầu',
            'user_id.exists' => 'Người dùng không tồn tại',
            'product_id.exists' => 'Sản phẩm không tồn tại',
        ];
    }

    public function getFilters(): array
    {
        return [
            'search' => $this->input('search'),
            'type' => $this->input('type'),
            'user_id' => $this->input('user_id'),
            'product_id' => $this->input('product_id'),
            'supplier_id' => $this->input('supplier_id'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_order' => $this->input('sort_order', 'desc'),
            'per_page' => $this->input('per_page', 20),
            'page' => $this->input('page', 1),
        ];
    }
}
