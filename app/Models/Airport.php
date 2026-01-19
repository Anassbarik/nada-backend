<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Airport extends Model
{
    protected $fillable = [
        'accommodation_id',
        'name',
        'code',
        'city',
        'country',
        'description',
        'distance_from_venue',
        'distance_unit',
        'sort_order',
        'active',
        'created_by',
    ];

    protected $casts = [
        'accommodation_id' => 'integer',
        'created_by' => 'integer',
        'distance_from_venue' => 'decimal:2',
        'sort_order' => 'integer',
        'active' => 'boolean',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * Get the admin who created this airport.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can edit this airport.
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
     * Check if a user can delete this airport.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }
}
