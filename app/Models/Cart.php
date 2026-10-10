<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class)->with(['product', 'seller', 'variant']);
    }

    /**
     * Group items by seller for multi-vendor checkout and display
     */
    public function itemsGroupedBySeller(): Collection
    {
        return $this->items->groupBy('seller_id');
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getTaxAmountAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            $taxPercent = $item->product ? $item->product->tax_percent : 18.0;
            return ($item->price * $item->quantity) * ($taxPercent / 100);
        });
    }

    /**
     * Estimated shipping fee: Free delivery if over ₹499 per seller, else ₹40 standard Indian delivery fee
     */
    public function getShippingFeeAttribute(): float
    {
        $grouped = $this->itemsGroupedBySeller();
        $totalShipping = 0;
        foreach ($grouped as $sellerId => $sellerItems) {
            $sellerSubtotal = $sellerItems->sum(fn($i) => $i->price * $i->quantity);
            if ($sellerSubtotal < 499) {
                $totalShipping += 40.0;
            }
        }
        return (float) $totalShipping;
    }

    public function getGrandTotalAttribute(): float
    {
        return round($this->subtotal + $this->tax_amount + $this->shipping_fee, 2);
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
