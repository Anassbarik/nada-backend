<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleClass extends Model
{
    protected $fillable = [
        'name',
        'created_by',
    ];

    public function vehicleTypes()
    {
        return $this->hasMany(VehicleType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
