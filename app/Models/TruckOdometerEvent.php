<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TruckOdometerEvent extends Model
{


    protected $fillable = [
        'truck_id','driver_id','type','odometer_km','source',
        'occurred_at','mapon_at','is_stale','stale_minutes','raw','note',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'mapon_at' => 'datetime',
        'is_stale' => 'boolean',
        'raw' => 'array',
        'odometer_km' => 'decimal:1',

    ];

    // Константы (пока без Enum, чтобы проще)
  public const TYPE_DEPARTURE = 1;
public const TYPE_RETURN    = 2;
public const TYPE_FUEL      = 3; // ✅ заправка (снимок одометра на момент заправки)

public const SOURCE_CAN     = 1;
public const SOURCE_MILEAGE = 2;
public const SOURCE_MANUAL = 3;
public const SOURCE_FALLBACK_LOCAL = 4;
}
