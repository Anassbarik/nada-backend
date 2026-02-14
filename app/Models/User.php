<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'company',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super-admin';
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super-admin';
    }

    /**
     * Check if user is an organizer.
     */
    public function isOrganizer(): bool
    {
        return $this->role === 'organizer';
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $resource, string $action): bool
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Regular admins need explicit permission
        if ($this->role === 'admin') {
            // Avoid N+1 queries when permissions are already loaded
            if ($this->relationLoaded('permissions')) {
                return $this->permissions
                    ->where('resource', $resource)
                    ->where('action', $action)
                    ->isNotEmpty();
            }

            return $this->permissions()
                ->where('resource', $resource)
                ->where('action', $action)
                ->exists();
        }

        return false;
    }


    /**
     * Get the permissions for the user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'admin_permissions')
            ->withTimestamps();
    }

    /**
     * Get the bookings for the user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the wallet for the user.
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get the accommodations (events) organized by this user.
     */
    public function organizedAccommodations()
    {
        return $this->hasMany(Accommodation::class, 'organizer_id');
    }

    /**
     * Get the resource permissions (sub-permissions) for the user.
     */
    public function resourcePermissions()
    {
        return $this->hasMany(ResourcePermission::class, 'user_id');
    }

    /**
     * Check if user has sub-permission to edit a specific resource.
     */
    public function hasResourcePermission(string $resourceType, int $resourceId): bool
    {
        return $this->resourcePermissions()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->exists();
    }
}
