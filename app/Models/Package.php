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
        'created_by',
    ];

    protected $casts = [
        'hotel_id' => 'integer',
        'created_by' => 'integer',
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
     * Get the resource permissions (sub-permissions) for this package.
     */
    public function resourcePermissions()
    {
        return $this->hasMany(ResourcePermission::class, 'resource_id')
            ->where('resource_type', 'package');
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

    /**
     * Get the admin who created this package.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can edit this package.
     * 
     * Rules:
     * - Super-admins can edit everything
     * - Regular admins can edit packages THEY created
     * - Regular admins can edit packages if they have sub-permission OR if they can edit the parent hotel/event
     */
    public function canBeEditedBy(User $user): bool
    {
        // Super-admin can edit everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Check if user can edit the parent hotel or event
        if ($this->hotel && $this->hotel->canBeEditedBy($user)) {
            return true;
        }

        // Check if user has sub-permission for this package
        if ($user->hasResourcePermission('package', $this->id)) {
            return true;
        }

        // Regular admins can edit packages they created
        if ($this->created_by && $this->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can delete this package.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }
}
