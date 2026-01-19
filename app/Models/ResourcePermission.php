<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourcePermission extends Model
{
    protected $fillable = [
        'resource_type',
        'resource_id',
        'user_id',
    ];

    /**
     * Get the admin who has this sub-permission.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
