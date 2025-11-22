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

    public function cargos()
{
    return $this->trip->cargos()->where(function ($q) {
        if ($this->type === 'loading') {
            $q->where('loading_country_id', $this->country_id)
              ->where('loading_city_id', $this->city_id)
              ->where('loading_address', $this->address);
        } else {
            $q->where('unloading_country_id', $this->country_id)
              ->where('unloading_city_id', $this->city_id)
              ->where('unloading_address', $this->address);
        }
    });
}

public function documents()
{
    return $this->hasMany(TripDocument::class, 'step_id');
}
}
