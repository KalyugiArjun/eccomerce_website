<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wishlist_id',
        'product_id',
        'added_price',
    ];

    protected $casts = [
        'added_price' => 'float',
    ];

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hasPriceDropped(): bool
    {
        if (!$this->product) return false;
        return $this->product->effective_price < $this->added_price;
    }

    public function getPriceDropAmountAttribute(): float
    {
        if ($this->hasPriceDropped()) {
            return round($this->added_price - $this->product->effective_price, 2);
        }
        return 0.0;
    }
}
