<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Services\DualStorageService;

class Flight extends Model
{
    protected $fillable = [
        'accommodation_id',
        'full_name',
        'flight_class',
        'flight_category',
        'departure_date',
        'departure_time',
        'arrival_date',
        'arrival_time',
        'departure_flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_price_ttc',
        'return_date',
        'return_departure_time',
        'return_arrival_date',
        'return_arrival_time',
        'return_flight_number',
        'return_departure_airport',
        'return_arrival_airport',
        'return_price_ttc',
        'reference',
        'eticket_path',
        'eticket',
        'ticket_reference',
        'beneficiary_type',
        'status',
        'payment_method',
        'organizer_id',
        'client_email',
        'user_id',
        'credentials_pdf_path',
        'created_by',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
        'return_date' => 'date',
        'return_arrival_date' => 'date',
        'departure_price_ttc' => 'decimal:2',
        'return_price_ttc' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($flight) {
            if (empty($flight->reference)) {
                $flight->reference = 'FLIGHT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

                // Ensure uniqueness
                while (static::where('reference', $flight->reference)->exists()) {
                    $flight->reference = 'FLIGHT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
                }
            }
        });

        static::deleting(function ($flight) {
            $flight->bookings()->each(function ($booking) {
                // Check if the booking has other components
                $hasOtherComponents = $booking->package_id || $booking->transfer_id;

                if ($hasOtherComponents) {
                    // Just remove the flight from the booking
                    $booking->update(['flight_id' => null]);
                } else {
                    // Only flight was in this booking, remove it completely
                    $booking->delete();
                }
            });
        });
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get eTicket URL.
     */
    public function getEticketUrlAttribute(): ?string
    {
        if (!$this->eticket_path) {
            return null;
        }
        return DualStorageService::url($this->eticket_path);
    }

    /**
     * Get credentials PDF URL.
     */
    public function getCredentialsPdfUrlAttribute(): ?string
    {
        if (!$this->credentials_pdf_path) {
            return null;
        }
        return DualStorageService::url($this->credentials_pdf_path);
    }

    /**
     * Get flight class label.
     */
    public function getFlightClassLabelAttribute(): string
    {
        return match ($this->flight_class) {
            'economy' => 'Economy',
            'business' => 'Business',
            'first' => 'First Class',
            default => ucfirst($this->flight_class),
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            default => ucfirst($this->status ?? 'pending'),
        };
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabelAttribute(): ?string
    {
        if (!$this->payment_method) {
            return null;
        }

        return match ($this->payment_method) {
            'wallet' => 'Portefeuille',
            'bank' => 'Virement Bancaire',
            'both' => 'Mixte (Portefeuille + Virement)',
            default => ucfirst($this->payment_method),
        };
    }

    /**
     * Get flight category label.
     */
    public function getFlightCategoryLabelAttribute(): string
    {
        return match ($this->flight_category ?? 'one_way') {
            'one_way' => 'Aller Simple (One Way)',
            'round_trip' => 'Aller-Retour (Round Trip)',
            default => ucfirst($this->flight_category ?? 'one_way'),
        };
    }

    /**
     * Get total price (departure + return if round trip).
     */
    public function getTotalPriceAttribute(): float
    {
        $total = (float) ($this->departure_price_ttc ?? 0);
        if ($this->flight_category === 'round_trip' && $this->return_price_ttc) {
            $total += (float) $this->return_price_ttc;
        }
        return $total;
    }
}
