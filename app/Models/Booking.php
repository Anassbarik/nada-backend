<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'event_id',
        'hotel_id',
        'package_id',
        'flight_number',
        'flight_date',
        'flight_time',
        'airport',
        'full_name',
        'company',
        'phone',
        'email',
        'special_instructions',
        'resident_name_1',
        'resident_name_2',
        'guest_name',
        'guest_email',
        'guest_phone',
        'special_requests',
        'booking_reference',
        'checkin_date',
        'checkout_date',
        'guests_count',
        'status',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'flight_date' => 'date',
        'flight_time' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BOOK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(3));
                
                // Ensure uniqueness
                while (static::where('booking_reference', $booking->booking_reference)->exists()) {
                    $booking->booking_reference = 'BOOK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(3));
                }
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
