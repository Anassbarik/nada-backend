<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventPackage extends Model
{
    protected $table = 'event_packages';
    
    protected $fillable = [
        'name',
        'slug',
        'status',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($eventPackage) {
            if (empty($eventPackage->slug)) {
                $eventPackage->slug = Str::slug($eventPackage->name);
                
                // Ensure uniqueness
                $originalSlug = $eventPackage->slug;
                $count = 1;
                while (static::where('slug', $eventPackage->slug)->exists()) {
                    $eventPackage->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($eventPackage) {
            if ($eventPackage->isDirty('name')) {
                $eventPackage->slug = Str::slug($eventPackage->name);
                
                // Ensure uniqueness
                $originalSlug = $eventPackage->slug;
                $count = 1;
                while (static::where('slug', $eventPackage->slug)->where('id', '!=', $eventPackage->id)->exists()) {
                    $eventPackage->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Get the admin who created this event package.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
