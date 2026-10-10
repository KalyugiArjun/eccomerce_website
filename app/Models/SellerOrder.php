<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SellerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'sub_order_number',
        'subtotal',
        'shipping_fee',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'platform_commission_rate',
        'platform_commission_amount',
        'seller_net_earnings',
        'status',
        'courier_name',
        'tracking_number',
        'estimated_delivery_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'shipping_fee' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'grand_total' => 'float',
        'platform_commission_rate' => 'float',
        'platform_commission_amount' => 'float',
        'seller_net_earnings' => 'float',
        'estimated_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'delivered' => 'bg-success',
            'out_for_delivery' => 'bg-info text-dark',
            'shipped' => 'bg-primary',
            'processing', 'confirmed' => 'bg-warning text-dark',
            'cancelled', 'returned' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
