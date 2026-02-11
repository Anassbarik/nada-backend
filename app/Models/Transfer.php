<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Services\DualStorageService;

class Transfer extends Model
{
    protected $fillable = [
        'accommodation_id',
        'client_name',
        'client_phone',
        'client_email',
        'transfer_type',
        'trip_type',
        'transfer_date',
        'pickup_time',
        'pickup_location',
        'dropoff_location',
        'flight_number',
        'flight_time',
        'vehicle_type',
        'vehicle_type_id',
        'passengers',
        'price',
        'return_date',
        'return_time',
        'eticket_path',
        'status',
        'payment_method',
        'beneficiary_type',
        'organizer_id',
        'user_id',
        'created_by',
        'luggages',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'return_date' => 'date',
        'price' => 'decimal:2',
        'passengers' => 'integer',
        'luggages' => 'integer',
        'flight_time' => 'datetime', // Cast flight_time as time/datetime if needed, but schema is time
    ];

    protected static function boot()
    {
        parent::boot();

        // No reference field in the schema provided in the prompt, 
        // but Flight has one. If we need one later we can add it.
        // For now, following the schema strictly.
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

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
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
     * Get transfer type label.
     */
    public function getTransferTypeLabelAttribute(): string
    {
        return match ($this->transfer_type) {
            'airport_hotel' => 'Airport → Hotel',
            'hotel_airport' => 'Hotel → Airport',
            'hotel_event' => 'Hotel → Event',
            'event_hotel' => 'Event → Hotel',
            'city_transfer' => 'City Transfer',
            default => ucfirst(str_replace('_', ' ', $this->transfer_type)),
        };
    }

    /**
     * Get trip type label.
     */
    public function getTripTypeLabelAttribute(): string
    {
        return match ($this->trip_type) {
            'one_way' => 'One Way',
            'round_trip' => 'Round Trip',
            default => ucfirst(str_replace('_', ' ', $this->trip_type)),
        };
    }

    /**
     * Get vehicle type label.
     */
    public function getVehicleTypeLabelAttribute(): string
    {
        return ucfirst($this->vehicle_type);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
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
}
