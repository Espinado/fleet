<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripStep extends Model
{
     protected $fillable = [
        'trip_id',
        'trip_cargo_id',
        'type',
        'country_id',
        'city_id',
        'address',
        'date',
        'order',
        'sequence',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function cargo()
    {
        return $this->belongsTo(TripCargo::class, 'trip_cargo_id');
    }
}
