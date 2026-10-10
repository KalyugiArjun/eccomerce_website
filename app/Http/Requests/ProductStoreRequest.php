<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $productId],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'discount_price' => ['nullable', 'numeric', 'lt:price'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'return_policy' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_featured' => ['nullable', 'boolean'],
            'is_fast_delivery' => ['nullable', 'boolean'],
            'delivery_time_minutes' => ['nullable', 'integer', 'min:10', 'max:300'],
            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['required_with:variants', 'string'],
            'variants.*.color' => ['nullable', 'string'],
            'variants.*.size' => ['nullable', 'string'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:1'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
        ];
    }
}
