<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'billing_address',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'shipping_fee',
        'tax_amount',
        'grand_total',
        'payment_method',
        'payment_status',
        'order_status',
        'is_gift',
        'gift_recipient_name',
        'gift_message',
        'gift_wrap',
        'hide_price',
        'scheduled_delivery_date',
        'notes',
        'placed_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'shipping_fee' => 'float',
        'tax_amount' => 'float',
        'grand_total' => 'float',
        'is_gift' => 'boolean',
        'gift_wrap' => 'boolean',
        'hide_price' => 'boolean',
        'scheduled_delivery_date' => 'date',
        'placed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->order_status) {
            'delivered' => 'bg-success',
            'out_for_delivery' => 'bg-info text-dark',
            'shipped' => 'bg-primary',
            'processing', 'confirmed' => 'bg-warning text-dark',
            'cancelled', 'returned' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
