<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SellerService
{
    /**
     * Register a new seller store application
     */
    public function registerSeller(User $user, array $data): Seller
    {
        $slug = Str::slug($data['store_name']) . '-' . rand(100, 999);

        $seller = Seller::create([
            'user_id' => $user->id,
            'store_name' => $data['store_name'],
            'slug' => $slug,
            'store_tagline' => $data['store_tagline'] ?? null,
            'phone' => $data['phone'] ?? $user->phone,
            'business_email' => $data['business_email'] ?? $user->email,
            'business_address' => $data['business_address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'pincode' => $data['pincode'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'delivery_radius_km' => $data['delivery_radius_km'] ?? 15.0,
            'estimated_delivery_minutes' => $data['estimated_delivery_minutes'] ?? 40,
            'local_delivery_available' => !empty($data['local_delivery_available']),
            'gst_number' => $data['gst_number'] ?? null,
            'pan_number' => $data['pan_number'] ?? null,
            'bank_account_name' => $data['bank_account_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_ifsc' => $data['bank_ifsc'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'store_logo' => $data['store_logo'] ?? null,
            'status' => 'pending', // Requires admin approval
            'rating' => 5.0,
            'trust_score' => 98.0,
            'about_store' => $data['about_store'] ?? null,
            'return_policy' => $data['return_policy'] ?? '7 Days Replacement / Return Policy',
        ]);

        // Update user role to seller
        $user->update(['role' => 'seller']);

        return $seller;
    }

    /**
     * Find nearby sellers within a given radius using Haversine formula
     */
    public function getNearbySellers(float $lat, float $lng, float $maxRadiusKm = 25.0): Collection
    {
        $sellers = Seller::where('status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $sellers->map(function ($seller) use ($lat, $lng) {
            $seller->distance = $seller->distanceFrom($lat, $lng);
            return $seller;
        })
        ->filter(fn($seller) => $seller->distance <= $maxRadiusKm)
        ->sortBy('distance')
        ->values();
    }

    /**
     * Recalculate seller trust score and performance metrics
     */
    public function updateTrustScore(Seller $seller): void
    {
        $seller->trust_score = $seller->calculateTrustScore();
        $seller->save();
    }
}
