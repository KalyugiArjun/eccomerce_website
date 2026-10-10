<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'alternate_phone',
        'house_no',
        'street',
        'area',
        'landmark',
        'city',
        'district',
        'state',
        'pincode',
        'address_type',
        'is_default',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->house_no,
            $this->street,
            $this->area,
            $this->landmark ? 'Near ' . $this->landmark : null,
            $this->city,
            $this->district,
            $this->state . ' - ' . $this->pincode,
        ]);
        return implode(', ', $parts);
    }
}
