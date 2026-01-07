<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
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
        'refund_amount',
        'refunded_at',
        'refund_notes',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'flight_date' => 'date',
        'flight_time' => 'datetime',
        'price' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
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

                // If changing TO cancelled or refunded from a non-cancelled/refunded status, increment room count
                if (in_array($newStatus, ['cancelled', 'refunded']) && !in_array($oldStatus, ['cancelled', 'refunded'])) {
                    $package->chambres_restantes = min(
                        $package->quantite_chambres,
                        $package->chambres_restantes + 1
                    );
                    $package->disponibilite = $package->chambres_restantes > 0;
                    $package->save();
                }

                // If changing TO refunded, credit the user's wallet
                if ($newStatus === 'refunded' && $oldStatus !== 'refunded' && $booking->user) {
                    // Load wallet if not already loaded
                    if (!$booking->user->relationLoaded('wallet')) {
                        $booking->user->load('wallet');
                    }

                    // Ensure wallet exists
                    if (!$booking->user->wallet) {
                        $booking->user->wallet()->create(['balance' => 0.00]);
                        $booking->user->refresh();
                    }

                    // Get refund amount (check if refund_amount is being set in this update)
                    // If refund_amount is in the dirty attributes, use it; otherwise use booking price
                    $refundAmount = $booking->getDirty()['refund_amount'] ?? $booking->refund_amount ?? $booking->price ?? ($package->prix_ttc ?? 0);
                    
                    // Credit wallet
                    $booking->user->wallet->increment('balance', $refundAmount);
                }
                // If changing FROM cancelled or refunded to a non-cancelled/refunded status, decrement room count
                elseif (in_array($oldStatus, ['cancelled', 'refunded']) && !in_array($newStatus, ['cancelled', 'refunded'])) {
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

            // If booking was not cancelled or refunded, increment room count when deleted
            if (!in_array($booking->status, ['cancelled', 'refunded'])) {
                $package->chambres_restantes = min(
                    $package->quantite_chambres,
                    $package->chambres_restantes + 1
                );
                $package->disponibilite = $package->chambres_restantes > 0;
                $package->save();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function voucher(): HasOne
    {
        return $this->hasOne(Voucher::class);
    }
}
