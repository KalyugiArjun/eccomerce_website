<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'payout_number',
        'gross_sales',
        'commission_deducted',
        'refunds_deducted',
        'net_amount',
        'bank_details_snapshot',
        'status',
        'transaction_reference',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'gross_sales' => 'float',
        'commission_deducted' => 'float',
        'refunds_deducted' => 'float',
        'net_amount' => 'float',
        'bank_details_snapshot' => 'array',
        'processed_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
