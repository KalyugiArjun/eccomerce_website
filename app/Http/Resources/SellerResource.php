<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_name' => $this->store_name,
            'slug' => $this->slug,
            'store_tagline' => $this->store_tagline,
            'phone' => $this->phone,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'delivery_radius_km' => $this->delivery_radius_km,
            'estimated_delivery_minutes' => $this->estimated_delivery_minutes,
            'local_delivery_available' => $this->local_delivery_available,
            'rating' => $this->rating,
            'trust_score' => $this->trust_score,
            'total_orders' => $this->total_orders,
            'completed_orders' => $this->completed_orders,
            'store_logo' => $this->store_logo,
            'store_banner' => $this->store_banner,
            'about_store' => $this->about_store,
        ];
    }
}
