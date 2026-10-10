<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['nullable', 'exists:addresses,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'house_no' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'street' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'area' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'state' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'pincode' => ['required_without:address_id', 'nullable', 'string', 'regex:/^[1-9][0-9]{5}$/'],
            'payment_method' => ['required', 'in:cod,razorpay,upi,card'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            
            // Gift Mode Features (Section 31)
            'is_gift' => ['nullable', 'boolean'],
            'gift_recipient_name' => ['nullable', 'required_if:is_gift,1', 'string', 'max:255'],
            'gift_message' => ['nullable', 'string', 'max:500'],
            'gift_wrap' => ['nullable', 'boolean'],
            'hide_price' => ['nullable', 'boolean'],
            'scheduled_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
