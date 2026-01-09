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
}
