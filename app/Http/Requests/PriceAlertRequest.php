<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'target_price' => ['required', 'numeric', 'min:1'],
        ];
    }
}
