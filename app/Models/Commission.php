<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_order_id',
        'seller_id',
        'order_amount',
        'commission_rate',
        'commission_amount',
        'seller_earnings',
        'status',
    ];

    protected $casts = [
        'order_amount' => 'float',
        'commission_rate' => 'float',
        'commission_amount' => 'float',
        'seller_earnings' => 'float',
    ];

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
