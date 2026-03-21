<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterBarcodeDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|in:bluetooth,usb,camera',
            'browser_fingerprint' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'device_name.required' => 'Device name is required',
            'device_name.max' => 'Device name cannot exceed 255 characters',
            'device_type.required' => 'Device type is required',
            'device_type.in' => 'Device type must be bluetooth, usb, or camera',
            'browser_fingerprint.required' => 'Browser fingerprint is required',
            'browser_fingerprint.max' => 'Browser fingerprint cannot exceed 255 characters',
        ];
    }
}
