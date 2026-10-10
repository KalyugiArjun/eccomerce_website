<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GroupBuyingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'creator_user_id',
        'group_code',
        'target_buyers',
        'current_buyers',
        'original_price',
        'discounted_price',
        'discount_tiers',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'target_buyers' => 'integer',
        'current_buyers' => 'integer',
        'original_price' => 'float',
        'discounted_price' => 'float',
        'discount_tiers' => 'array',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->group_code)) {
                $group->group_code = 'GRP-' . strtoupper(Str::random(8));
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GroupBuyingParticipant::class, 'group_id');
    }

    public function isExpired(): bool
    {
        return now()->gt($this->expires_at) || $this->status === 'expired';
    }

    public function isFull(): bool
    {
        return $this->current_buyers >= $this->target_buyers;
    }

    public function getRemainingBuyersAttribute(): int
    {
        return max(0, $this->target_buyers - $this->current_buyers);
    }

    public function getProgressPercentageAttribute(): int
    {
        return (int) min(100, round(($this->current_buyers / max(1, $this->target_buyers)) * 100));
    }
}
