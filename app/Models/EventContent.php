<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\DualStorageService;
use Illuminate\Support\Facades\Storage;

class EventContent extends Model
{
    protected $fillable = [
        'event_id',
        'page_type',
        'type', // Alias for page_type for simpler API
        'hero_image',
        'sections',
        'sections_en',
        'sections_fr',
        'content', // Simple longText content field
        'created_by',
    ];

    protected $casts = [
        'sections' => 'array',
        'sections_en' => 'array',
        'sections_fr' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the full URL for the hero image.
     */
    public function getHeroImageUrlAttribute()
    {
        if (!$this->hero_image) {
            return null;
        }
        
        return DualStorageService::url($this->hero_image);
    }

    /**
     * Get the admin who created this content.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can edit this content.
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
     * Check if a user can delete this content.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }
}
