<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\DualStorageService;
use Illuminate\Support\Facades\Storage;

class HotelImage extends Model
{
    protected $fillable = [
        'hotel_id',
        'path',
        'alt_text',
        'sort_order',
        'status',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        // Delete file from storage when image is deleted
        static::deleting(function ($image) {
            if ($image->path) {
                DualStorageService::delete($image->path, 'public');
            }
        });
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the full URL for the hotel image.
     */
    public function getUrlAttribute()
    {
        if (!$this->path) {
            return null;
        }
        
        return DualStorageService::url($this->path);
    }

    /**
     * Get the admin who created this image.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can edit this image.
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
     * Check if a user can delete this image.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }
}
