<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Partner extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'url',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the full URL for the partner logo.
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo_path) {
            return null;
        }
        
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/storage/' . ltrim($this->logo_path, '/');
    }
}
