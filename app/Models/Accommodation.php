<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property int|null $organizer_id
 * @property int|null $created_by
 */
class Accommodation extends Model
{
    protected $table = 'accommodations';

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
        'description_en',
        'description_fr',
        'menu_links',
        'status',
        'show_flight_prices_public',
        'show_flight_prices_client_dashboard',
        'show_flight_prices_organizer_dashboard',
        'created_by',
        'organizer_id',
        'commission_percentage',
        'show_transfer_prices_public',
        'show_transfer_prices_client_dashboard',
        'show_transfer_prices_organizer_dashboard',
    ];

    protected $casts = [
        'menu_links' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'show_flight_prices_public' => 'boolean',
        'show_flight_prices_client_dashboard' => 'boolean',
        'show_flight_prices_organizer_dashboard' => 'boolean',
        'commission_percentage' => 'decimal:2',
        'show_transfer_prices_public' => 'boolean',
        'show_transfer_prices_client_dashboard' => 'boolean',
        'show_transfer_prices_organizer_dashboard' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($accommodation) {
            if (empty($accommodation->slug)) {
                $accommodation->slug = Str::slug($accommodation->name);

                // Ensure uniqueness
                $originalSlug = $accommodation->slug;
                $count = 1;
                while (static::where('slug', $accommodation->slug)->exists()) {
                    $accommodation->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::created(function ($accommodation) {
            // Seed content pages from seafood4africa accommodation as default
            $referenceAccommodation = static::where('slug', 'seafood4africa')
                ->orWhere('name', 'LIKE', '%Seafood%')
                ->first();

            if ($referenceAccommodation) {
                $referenceContents = $referenceAccommodation->contents;

                foreach ($referenceContents as $referenceContent) {
                    // Copy the content structure with both English and French translations
                    AccommodationContent::create([
                        'accommodation_id' => $accommodation->id,
                        'page_type' => $referenceContent->page_type,
                        'hero_image' => null,
                        'sections' => $referenceContent->sections, // Keep original as fallback
                        'sections_en' => $referenceContent->sections_en ?? $referenceContent->sections,
                        'sections_fr' => $referenceContent->sections_fr ?? $referenceContent->sections,
                        'content' => $referenceContent->content,
                        'created_by' => $accommodation->created_by,
                    ]);
                }
            }
        });

        static::updating(function ($accommodation) {
            if ($accommodation->isDirty('name')) {
                $accommodation->slug = Str::slug($accommodation->name);

                // Ensure uniqueness
                $originalSlug = $accommodation->slug;
                $count = 1;
                while (static::where('slug', $accommodation->slug)->where('id', '!=', $accommodation->id)->exists()) {
                    $accommodation->slug = $originalSlug . '-' . $count;
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
        return $this->hasMany(AccommodationContent::class);
    }

    public function airports(): HasMany
    {
        return $this->hasMany(Airport::class)->orderBy('sort_order')->orderBy('name');
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    /**
     * Get the resource permissions (sub-permissions) for this accommodation.
     */
    public function resourcePermissions()
    {
        return $this->hasMany(ResourcePermission::class, 'resource_id')
            ->where('resource_type', 'event');
    }

    /**
     * Get the admin who created this accommodation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the organizer for this accommodation.
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Check if a user can view this accommodation (read-only access).
     * All admins can view accommodations, but only certain ones can edit.
     */
    public function canBeViewedBy(User $user): bool
    {
        // All authenticated admins can view accommodations
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Check if a user can edit this accommodation.
     * 
     * Rules:
     * - Super-admins can edit everything
     * - Regular admins can edit accommodations THEY created
     * - Regular admins can edit accommodations if they have sub-permission (granted by super-admin)
     * - Accommodations created by super-admins can only be edited by super-admins or those with sub-permissions
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

        // Check if user has sub-permission for this accommodation
        if ($user->hasResourcePermission('event', $this->id)) {
            return true;
        }

        // If accommodation was created by a super admin, only super admins or those with sub-permissions can edit it
        if ($this->created_by) {
            $creator = $this->creator;
            if ($creator && $creator->isSuperAdmin()) {
                return false; // Regular admins without sub-permission cannot edit super admin accommodations
            }
        }

        // Regular admins can edit their own accommodations
        if ($this->created_by && (int) $this->created_by === (int) $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can delete this accommodation.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }

    /**
     * Check if a user can manage flights for this accommodation.
     * 
     * Rules:
     * - Super-admins can manage all flights
     * - Regular admins need flights permissions AND (created the accommodation OR have sub-permission)
     */
    public function canManageFlightsBy(User $user): bool
    {
        // Super-admin can manage everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Non-admins cannot manage flights
        if (!$user->isAdmin()) {
            return false;
        }

        // If accommodation was created by a super admin, check sub-permissions first
        // Resource permissions can grant access even without main flights.view permission
        if ($this->created_by) {
            $creator = $this->creator;
            if ($creator && $creator->isSuperAdmin()) {
                // If user has resource permission for this accommodation, they can manage flights
                if ($user->hasResourcePermission('flight', $this->id)) {
                    return true;
                }
            }
        }

        // Check if user has flights permissions (main permission check)
        if ($user->hasPermission('flights', 'view')) {
            // If accommodation was created by a super admin and user has main permission,
            // they can manage if they also have resource permission
            if ($this->created_by) {
                $creator = $this->creator;
                if ($creator && $creator->isSuperAdmin()) {
                    return $user->hasResourcePermission('flight', $this->id);
                }
            }

            // Regular admins can manage flights for accommodations they created
            return $this->created_by === $user->id;
        }

        // No permission (neither main nor resource)
        return false;
    }

    /**
     * Check if a user can manage transfers for this accommodation.
     * 
     * Rules:
     * - Super-admins can manage all transfers
     * - Regular admins need transfers permissions AND (created the accommodation OR have sub-permission)
     */
    public function canManageTransfersBy(User $user): bool
    {
        // Super-admin can manage everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Non-admins cannot manage transfers
        if (!$user->isAdmin()) {
            return false;
        }

        // If accommodation was created by a super admin, check sub-permissions first
        // Resource permissions can grant access even without main transfers.view permission
        if ($this->created_by) {
            $creator = $this->creator;
            if ($creator && $creator->isSuperAdmin()) {
                // If user has resource permission for this accommodation, they can manage transfers
                if ($user->hasResourcePermission('transfer', $this->id)) {
                    return true;
                }
            }
        }

        // Check if user has transfers permissions (main permission check)
        // Note: Assuming 'transfers' permission resource exists or will be added
        if ($user->hasPermission('transfers', 'view')) {
            // If accommodation was created by a super admin and user has main permission,
            // they can manage if they also have resource permission
            if ($this->created_by) {
                $creator = $this->creator;
                if ($creator && $creator->isSuperAdmin()) {
                    return $user->hasResourcePermission('transfer', $this->id);
                }
            }

            // Regular admins can manage transfers for accommodations they created
            return $this->created_by === $user->id;
        }

        // No permission (neither main nor resource)
        return false;
    }

    /**
     * Scope a query to only include the latest accommodations.
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
