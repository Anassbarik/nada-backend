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
        'price',
        'status',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'flight_date' => 'date',
        'flight_time' => 'datetime',
        'price' => 'decimal:2',
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

        // Handle room count updates when booking status changes
        static::updating(function ($booking) {
            // Only process if status is being changed
            if ($booking->isDirty('status')) {
                $oldStatus = $booking->getOriginal('status');
                $newStatus = $booking->status;

                // Load package if not already loaded
                if (!$booking->relationLoaded('package')) {
                    $booking->load('package');
                }

                $package = $booking->package;

                if (!$package) {
                    return; // No package associated, skip room count update
                }

                // If changing TO cancelled from a non-cancelled status, increment room count
                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    $package->chambres_restantes = min(
                        $package->quantite_chambres,
                        $package->chambres_restantes + 1
                    );
                    $package->disponibilite = $package->chambres_restantes > 0;
                    $package->save();
                }
                // If changing FROM cancelled to a non-cancelled status, decrement room count
                elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                    if ($package->chambres_restantes > 0) {
                        $package->chambres_restantes = max(0, $package->chambres_restantes - 1);
                        $package->disponibilite = $package->chambres_restantes > 0;
                        $package->save();
                    }
                }
            }
        });

        // Handle room count update when booking is deleted (if it wasn't cancelled)
        static::deleting(function ($booking) {
            // Load package if not already loaded
            if (!$booking->relationLoaded('package')) {
                $booking->load('package');
            }

            $package = $booking->package;

            if (!$package) {
                return; // No package associated, skip room count update
            }

            // If booking was not cancelled, increment room count when deleted
            if ($booking->status !== 'cancelled') {
                $package->chambres_restantes = min(
                    $package->quantite_chambres,
                    $package->chambres_restantes + 1
                );
                $package->disponibilite = $package->chambres_restantes > 0;
                $package->save();
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
