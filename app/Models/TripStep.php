<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripStep extends Model
{
    protected $fillable = [
        'trip_id',
        'type',        // loading | unloading
        'client_id',   // ответственный клиент за точку
        'country_id',
        'city_id',
        'address',
        'date',
        'time',        // строка, удобнее в формах
        'order',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** ========================
     *  RELATIONS
     * ======================== */

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function documents()
    {
        return $this->hasMany(TripDocument::class, 'step_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Грузы, которые связаны с этим шагом (many-to-many через pivot trip_cargo_step)
     */
   

    public function cargos()
{
    return $this->belongsToMany(TripCargo::class, 'trip_cargo_step')
        ->withPivot('role');
}

}
