<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'venue',
        'location',
        'google_maps_url',
        'start_date',
        'end_date',
        'website_url',
        'organizer_logo',
        'logo_path',
        'banner_path',
        'description',
        'menu_links',
        'status',
        'created_by',
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

    public function airports(): HasMany
    {
        return $this->hasMany(Airport::class)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the admin who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can view this event (read-only access).
     * All admins can view events, but only certain ones can edit.
     */
    public function canBeViewedBy(User $user): bool
    {
        // All authenticated admins can view events
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Check if a user can edit this event.
     * 
     * Rules:
     * - Super-admins can edit everything
     * - Regular admins can ONLY edit events THEY created
     * - Events created by super-admins can only be edited by super-admins
     */
    public function canBeEditedBy(User $user): bool
    {
        // Super-admin can edit everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Non-admins cannot edit
        if (!$user->isAdmin()) {
            return false;
        }

        // If event was created by a super admin, only super admins can edit it
        if ($this->created_by) {
            $creator = $this->creator;
            if ($creator && $creator->isSuperAdmin()) {
                return false; // Regular admins cannot edit super admin events
            }
        }

        // Regular admins can ONLY edit their own events
        if ($this->created_by && (int) $this->created_by === (int) $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can delete this event.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
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
        if (!$this->logo_path) {
            return null;
        }
        
        return \App\Services\DualStorageService::url($this->logo_path);
    }

    /**
     * Get banner URL.
     */
    public function getBannerUrlAttribute()
    {
        if (!$this->banner_path) {
            return null;
        }
        
        return \App\Services\DualStorageService::url($this->banner_path);
    }

    /**
     * Get organizer logo URL.
     */
    public function getOrganizerLogoUrlAttribute()
    {
        if (!$this->organizer_logo) {
            return null;
        }
        
        return \App\Services\DualStorageService::url($this->organizer_logo);
    }
}
