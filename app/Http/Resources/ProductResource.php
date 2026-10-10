<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'effective_price' => $this->effective_price,
            'discount_percentage' => $this->discount_percentage,
            'tax_percent' => $this->tax_percent,
            'stock' => $this->stock,
            'in_stock' => $this->is_in_stock,
            'thumbnail' => $this->thumbnail,
            'images' => $this->images,
            'rating' => $this->rating,
            'total_reviews' => $this->total_reviews,
            'is_fast_delivery' => $this->is_fast_delivery,
            'delivery_time_minutes' => $this->delivery_time_minutes,
            'seller' => [
                'id' => $this->seller->id,
                'store_name' => $this->seller->store_name,
                'slug' => $this->seller->slug,
                'rating' => $this->seller->rating,
                'trust_score' => $this->seller->trust_score,
                'city' => $this->seller->city,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'variants' => $this->variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'color' => $v->color,
                    'size' => $v->size,
                    'price' => $v->effective_price,
                    'stock' => $v->stock,
                ];
            }),
        ];
    }
}
