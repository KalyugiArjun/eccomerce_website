<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupBuyingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'order_id',
        'paid_amount',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'paid_amount' => 'float',
        'joined_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(GroupBuyingGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
