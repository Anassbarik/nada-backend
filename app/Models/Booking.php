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
        'created_by',
        'accommodation_id',
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
        'resident_name_3',
        'booker_is_resident',
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
        'payment_type',
        'wallet_amount',
        'bank_amount',
        'refund_amount',
        'refunded_at',
        'refund_notes',
        'payment_document_path',
        'flight_ticket_path',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'flight_date' => 'date',
        'flight_time' => 'datetime',
        'price' => 'decimal:2',
        'wallet_amount' => 'decimal:2',
        'bank_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'booker_is_resident' => 'boolean',
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

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * Alias for accommodation() for backward compatibility.
     * The table was renamed from events to accommodations.
     */
    public function event(): BelongsTo
    {
        return $this->accommodation();
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

    /**
     * Get the admin who created this booking.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can edit this booking.
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if ($this->created_by && $this->created_by === $user->id) {
            return true;
        }
        return false;
    }

    /**
     * Check if a user can delete this booking.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }

    /**
     * Get payment document URL.
     */
    public function getPaymentDocumentUrlAttribute(): ?string
    {
        if (!$this->payment_document_path) {
            return null;
        }
        return \App\Services\DualStorageService::url($this->payment_document_path);
    }

    /**
     * Get flight ticket URL.
     */
    public function getFlightTicketUrlAttribute(): ?string
    {
        if (!$this->flight_ticket_path) {
            return null;
        }
        return \App\Services\DualStorageService::url($this->flight_ticket_path);
    }
}
