<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\DualStorageService;
use Illuminate\Support\Facades\Storage;

class AccommodationContent extends Model
{
    protected $table = 'accommodation_contents';
    
    protected $fillable = [
        'accommodation_id',
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

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * Get hero image URL.
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
}
