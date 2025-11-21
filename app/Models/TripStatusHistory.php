<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripStatusHistory extends Model
{
   protected $table = 'trip_status_history';

    protected $fillable = [
        'trip_id',
        'driver_id',
        'status',
        'time'
    ];

    public $timestamps = true;

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}