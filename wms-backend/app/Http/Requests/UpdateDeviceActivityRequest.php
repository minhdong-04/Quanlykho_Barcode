<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
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
