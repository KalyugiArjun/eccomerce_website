<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'business_email' => ['required', 'email', 'max:255'],
            'business_address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'regex:/^[1-9][0-9]{5}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery_radius_km' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'estimated_delivery_minutes' => ['nullable', 'integer', 'min:10', 'max:300'],
            'local_delivery_available' => ['nullable', 'boolean'],
            'pan_number' => ['nullable', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i'],
            'gst_number' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_ifsc' => ['nullable', 'string', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'about_store' => ['nullable', 'string'],
            'store_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please provide a valid 10-digit Indian phone number.',
            'pincode.regex' => 'Please provide a valid 6-digit Indian pincode.',
            'pan_number.regex' => 'Please provide a valid Indian PAN card format (e.g. ABCDE1234F).',
            'gst_number.regex' => 'Please provide a valid 15-character Indian GSTIN format.',
            'bank_ifsc.regex' => 'Please provide a valid 11-character Indian Bank IFSC code.',
        ];
    }
}
