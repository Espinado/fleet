<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleRun extends Model
{
    use HasFactory;

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
        'start_can_odom_km' => 'decimal:1',
        'end_can_odom_km'   => 'decimal:1',
        'start_engine_hours' => 'decimal:1',
        'end_engine_hours'   => 'decimal:1',
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
