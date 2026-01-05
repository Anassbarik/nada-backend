<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EventContent extends Model
{
    protected $fillable = [
        'event_id',
        'page_type',
        'type', // Alias for page_type for simpler API
        'hero_image',
        'sections',
        'content', // Simple longText content field
    ];

    protected $casts = [
        'sections' => 'array',
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
        return $this->hero_image ? Storage::disk('public')->url($this->hero_image) : null;
    }
}
