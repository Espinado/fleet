<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverEvent extends Model
{
    protected $fillable = [
        'driver_id','user_id','trip_id',
        'channel','event','name','path','method',
        'status_code','duration_ms','ip','user_agent','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
