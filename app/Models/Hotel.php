<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Hotel extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'stars',
        'slug',
        'location',
        'location_url',
        'duration',
        'description',
        'inclusions',
        'website',
        'rating',
        'review_count',
        'status',
        'created_by',
    ];

    protected $casts = [
        'event_id' => 'integer',
        'created_by' => 'integer',
        'stars' => 'integer',
        'rating' => 'decimal:2',
        'inclusions' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hotel) {
            if (empty($hotel->slug)) {
                $hotel->slug = Str::slug($hotel->name);
                
                // Ensure uniqueness within the event
                $originalSlug = $hotel->slug;
                $count = 1;
                while (static::where('event_id', $hotel->event_id)
                    ->where('slug', $hotel->slug)
                    ->exists()) {
                    $hotel->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($hotel) {
            if ($hotel->isDirty('name')) {
                $hotel->slug = Str::slug($hotel->name);
                
                // Ensure uniqueness within the event
                $originalSlug = $hotel->slug;
                $count = 1;
                while (static::where('event_id', $hotel->event_id)
                    ->where('slug', $hotel->slug)
                    ->where('id', '!=', $hotel->id)
                    ->exists()) {
                    $hotel->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function hotelPackages(): HasMany
    {
        return $this->hasMany(HotelPackage::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(HotelImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary image for this hotel.
     */
    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('status', 'active')->orderBy('sort_order')->first();
    }

    /**
     * Get rating stars breakdown for display.
     */
    public function getRatingStarsAttribute()
    {
        $rating = $this->rating ?? 0;
        $full = floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        
        return [
            'full' => (int) $full,
            'half' => (int) $half,
            'empty' => 5 - $full - $half,
            'text' => $rating ? number_format($rating, 1) : null,
            'raw' => $rating,
        ];
    }

    /**
     * Get the admin who created this hotel.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can edit this hotel.
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
     * Check if a user can delete this hotel.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }
}
