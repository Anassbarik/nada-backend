<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelPackage extends Model
{
    protected $table = 'hotel_packages';
    
    protected $fillable = [
        'hotel_id',
        'name',
        'duration_days',
        'total_price',
        'description',
        'max_guests',
        'available_from',
        'available_to',
        'status',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'available_from' => 'date',
        'available_to' => 'date',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the event through the hotel relationship.
     */
    public function getEventAttribute()
    {
        return $this->hotel->event;
    }

    /**
     * Get the formatted price with currency.
     */
    public function getFormattedPriceAttribute()
    {
        if ($this->total_price === null) {
            return null;
        }
        return number_format($this->total_price, 2, '.', ' ') . ' MAD';
    }

    /**
     * Get the currency code.
     */
    public function getCurrencyAttribute()
    {
        return 'MAD';
    }
}
