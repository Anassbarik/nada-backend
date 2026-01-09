<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'resource',
        'action',
        'name',
        'description',
    ];

    /**
     * Get the admins that have this permission.
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_permissions')
            ->withTimestamps();
    }

    /**
     * Get permission key (resource.action format).
     */
    public function getKeyAttribute(): string
    {
        return $this->resource . '.' . $this->action;
    }
}
