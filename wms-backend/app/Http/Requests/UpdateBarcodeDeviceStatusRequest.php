<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarcodeDeviceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins can toggle device status
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'device_id' => 'required|exists:barcode_devices,id',
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required' => 'Device ID is required',
            'device_id.exists' => 'Device not found',
        ];
    }
}
