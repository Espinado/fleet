<?php

namespace App\Models;

use App\Enums\OdometerEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdometerEvent extends Model
{
    protected $fillable = [
        'truck_id',
        'vehicle_run_id',
        'trip_id',
        'trip_step_id',
        'event_type',
        'event_at',
        'can_odom_km',
        'can_at',
        'source',
        'is_stale',
    ];

    protected $casts = [
        'event_at'   => 'datetime',
        'can_at'     => 'datetime',
        'is_stale'   => 'boolean',
        'event_type' => OdometerEventType::class, // Laravel enum cast
    ];

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function vehicleRun(): BelongsTo
    {
        return $this->belongsTo(VehicleRun::class, 'vehicle_run_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripStep(): BelongsTo
    {
        return $this->belongsTo(TripStep::class);
    }
}
