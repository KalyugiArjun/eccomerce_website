<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'discount_price',
        'tax_percent',
        'stock',
        'weight',
        'dimensions',
        'warranty',
        'return_policy',
        'thumbnail',
        'images',
        'video_url',
        'status',
        'is_featured',
        'is_fast_delivery',
        'delivery_time_minutes',
        'rating',
        'total_reviews',
        'views_count',
    ];

    protected $casts = [
        'price' => 'float',
        'discount_price' => 'float',
        'tax_percent' => 'float',
        'stock' => 'integer',
        'weight' => 'float',
        'is_featured' => 'boolean',
        'is_fast_delivery' => 'boolean',
        'delivery_time_minutes' => 'integer',
        'rating' => 'float',
        'total_reviews' => 'integer',
        'views_count' => 'integer',
        'images' => 'array',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class)->orderBy('created_at', 'desc');
    }

    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved')->orderBy('created_at', 'desc');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function groupBuyingGroups(): HasMany
    {
        return $this->hasMany(GroupBuyingGroup::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return ($this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price)
            ? (float) $this->discount_price
            : (float) $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ($this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price) {
            return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Similar/alternative sellers offering same or similar product name/category
     */
    public function getAlternativeSellers()
    {
        return Product::with('seller')
            ->where('id', '!=', $this->id)
            ->where(function ($q) {
                $q->where('category_id', $this->category_id)
                  ->orWhere('name', 'LIKE', '%' . substr($this->name, 0, 15) . '%');
            })
            ->where('status', 'published')
            ->where('stock', '>', 0)
            ->get();
    }
}
