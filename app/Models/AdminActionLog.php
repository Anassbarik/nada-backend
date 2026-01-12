<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActionLog extends Model
{
    protected $fillable = [
        'user_id',
        'route_name',
        'method',
        'action_key',
        'entity_key',
        'url',
        'subject_type',
        'subject_id',
        'target_label',
        'ip',
        'user_agent',
        'status_code',
        'outcome',
        'details',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


