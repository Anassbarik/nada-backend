<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $fillable = [
        'name',
        'vehicle_class_id',
        'max_passengers',
        'max_luggages',
        'created_by',
    ];

    public function vehicleClass()
    {
        return $this->belongsTo(VehicleClass::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }
}
