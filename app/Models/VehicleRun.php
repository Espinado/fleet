<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleRun extends Model
{
    protected $fillable = [
        'truck_id',
        'driver_id',
        'started_at',
        'ended_at',
        'start_can_odom_km',
        'end_can_odom_km',
        'start_engine_hours',
        'end_engine_hours',
        'status',
        'close_reason',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'vehicle_run_id');
    }

    public function odometerEvents(): HasMany
    {
        return $this->hasMany(OdometerEvent::class, 'vehicle_run_id');
    }
}
