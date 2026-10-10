<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'store_tagline',
        'phone',
        'business_email',
        'business_address',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'delivery_radius_km',
        'estimated_delivery_minutes',
        'local_delivery_available',
        'gst_number',
        'pan_number',
        'bank_account_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_name',
        'store_logo',
        'store_banner',
        'commission_rate',
        'status',
        'rating',
        'total_reviews',
        'trust_score',
        'total_orders',
        'completed_orders',
        'cancelled_orders',
        'on_time_deliveries',
        'return_count',
        'return_policy',
        'about_store',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'delivery_radius_km' => 'float',
        'estimated_delivery_minutes' => 'integer',
        'local_delivery_available' => 'boolean',
        'commission_rate' => 'float',
        'rating' => 'float',
        'trust_score' => 'float',
        'total_reviews' => 'integer',
        'total_orders' => 'integer',
        'completed_orders' => 'integer',
        'cancelled_orders' => 'integer',
        'on_time_deliveries' => 'integer',
        'return_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SellerDocument::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(SellerPayout::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Calculate Distance in Kilometers from given lat/lng using Haversine formula
     */
    public function distanceFrom(float $lat, float $lng): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 999.0;
        }

        $earthRadius = 6371; // km
        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 1);
    }

    /**
     * Calculate dynamic trust score based on fulfillment, ratings, and return metrics
     */
    public function calculateTrustScore(): float
    {
        if ($this->total_orders === 0) {
            return 95.0;
        }

        $completionRate = ($this->completed_orders / max($this->total_orders, 1)) * 40; // 40 pts
        $ratingRate = ($this->rating / 5.0) * 30; // 30 pts
        $onTimeRate = ($this->on_time_deliveries / max($this->completed_orders, 1)) * 20; // 20 pts
        $lowReturnRate = (1.0 - ($this->return_count / max($this->completed_orders, 1))) * 10; // 10 pts

        $score = round($completionRate + $ratingRate + $onTimeRate + max(0, $lowReturnRate), 1);
        return min(100.0, max(50.0, $score));
    }
}
