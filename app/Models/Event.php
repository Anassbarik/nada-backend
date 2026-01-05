<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'venue',
        'start_date',
        'end_date',
        'website_url',
        'organizer_logo',
        'logo_path',
        'banner_path',
        'description',
        'menu_links',
        'status',
    ];

    protected $casts = [
        'menu_links' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);
                
                // Ensure uniqueness
                $originalSlug = $event->slug;
                $count = 1;
                while (static::where('slug', $event->slug)->exists()) {
                    $event->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('name')) {
                $event->slug = Str::slug($event->name);
                
                // Ensure uniqueness
                $originalSlug = $event->slug;
                $count = 1;
                while (static::where('slug', $event->slug)->where('id', '!=', $event->id)->exists()) {
                    $event->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(EventContent::class);
    }

    /**
     * Scope a query to only include the latest events.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get formatted date range for display.
     */
    public function getFormattedDatesAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->format('d M Y') . ' - ' . $this->end_date->format('d M Y');
    }

    /**
     * Get compact formatted date range (e.g., "04-06 Feb 2026").
     */
    public function getCompactDatesAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        if ($this->start_date->format('M Y') === $this->end_date->format('M Y')) {
            // Same month: "04-06 Feb 2026"
            return $this->start_date->format('d') . '-' . $this->end_date->format('d M Y');
        } else {
            // Different months: "04 Feb - 06 Mar 2026"
            return $this->start_date->format('d M') . ' - ' . $this->end_date->format('d M Y');
        }
    }

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    /**
     * Get banner URL.
     */
    public function getBannerUrlAttribute()
    {
        return $this->banner_path ? Storage::disk('public')->url($this->banner_path) : null;
    }

    /**
     * Get organizer logo URL.
     */
    public function getOrganizerLogoUrlAttribute()
    {
        return $this->organizer_logo ? Storage::disk('public')->url($this->organizer_logo) : null;
    }
}
