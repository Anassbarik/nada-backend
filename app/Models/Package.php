<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    protected $table = 'hotel_packages';
    
    protected $fillable = [
        'hotel_id',
        'nom_package',
        'type_chambre',
        'check_in',
        'check_out',
        'occupants',
        'prix_ht',
        'prix_ttc',
        'quantite_chambres',
        'chambres_restantes',
        'disponibilite',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'occupants' => 'integer',
        'prix_ht' => 'decimal:2',
        'prix_ttc' => 'decimal:2',
        'quantite_chambres' => 'integer',
        'chambres_restantes' => 'integer',
        'disponibilite' => 'boolean',
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
     * Get the resident count based on occupants.
     * Simple (1 pers) → 0, Double (2 pers) → 1, Triple (3+ pers) → 2
     */
    public function getResidentCountAttribute()
    {
        if ($this->occupants <= 1) {
            return 0;
        } elseif ($this->occupants == 2) {
            return 1;
        } else {
            return 2; // 3+ occupants
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($package) {
            // Auto-calculate prix_ttc from prix_ht (20% VAT)
            if ($package->isDirty('prix_ht') || $package->prix_ht !== null) {
                $package->prix_ttc = $package->prix_ht * 1.20;
            }

            // Auto-calculate disponibilite from chambres_restantes
            if ($package->isDirty('chambres_restantes') || $package->chambres_restantes !== null) {
                $package->disponibilite = $package->chambres_restantes > 0;
            }
        });
    }
}
