<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelNightPrice extends Model
{
    protected $fillable = [
        'hotel_id',
        'price_per_night',
        'valid_from',
        'valid_to',
        'status',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}

