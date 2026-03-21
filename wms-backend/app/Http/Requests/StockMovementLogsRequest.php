<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Endpoint is still protected by auth:sanctum middleware;
        // requirement: all authenticated users can view logs.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'type' => 'nullable|in:in,out',
            'user_id' => 'nullable|integer|exists:users,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'date_from' => 'nullable|date|date_format:Y-m-d',
            'date_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:50',
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
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            // Sidebar log: always recent-first
            'sort_by' => 'created_at',
            'sort_order' => 'desc',
            'per_page' => $this->input('per_page', 20),
            'page' => $this->input('page', 1),
        ];
    }
}
