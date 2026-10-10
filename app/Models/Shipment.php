<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_order_id',
        'courier_name',
        'tracking_number',
        'shipped_at',
        'estimated_arrival',
        'status',
        'current_location',
        'tracking_events',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'estimated_arrival' => 'datetime',
        'tracking_events' => 'array',
    ];

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }
}
