<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'], // Valid 10-digit Indian mobile number
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'preferred_city' => ['nullable', 'string', 'max:100'],
            'preferred_pincode' => ['nullable', 'string', 'regex:/^[1-9][0-9]{5}$/'], // 6-digit Indian PIN
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.',
            'preferred_pincode.regex' => 'Please enter a valid 6-digit Indian pincode.',
        ];
    }
}
