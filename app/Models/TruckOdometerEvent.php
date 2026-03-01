<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TruckOdometerEvent extends Model
{
    use HasFactory;

    protected $table = 'truck_odometer_events';

    protected $fillable = [
    'truck_id',
    'driver_id',
    'trip_id',
    'trip_step_id',
    'trip_expense_id',

    'type',
    'odometer_km',
    'source',
    'occurred_at',
    'mapon_at',
    'is_stale',
    'stale_minutes',
    'raw',
    'note',

    'expense_category',
    'expense_amount',
    'step_status',
];

  protected $casts = [
    'occurred_at' => 'datetime',
    'mapon_at' => 'datetime',
    'is_stale' => 'boolean',
    'raw' => 'array',
    'odometer_km' => 'decimal:1',
    'expense_amount' => 'decimal:2',
];

    // ===========================
    // Types
    // ===========================
   public const TYPE_DEPARTURE = 1;
public const TYPE_RETURN    = 2;
public const TYPE_EXPENSE   = 3; // вместо TYPE_FUEL (расход любой категории)
public const TYPE_STEP      = 4; // смена статуса шага
public const TYPE_FUEL = self::TYPE_EXPENSE;
    // ===========================
    // Sources
    // ===========================
    public const SOURCE_CAN            = 1;
    public const SOURCE_MILEAGE        = 2;
    public const SOURCE_MANUAL         = 3;
    public const SOURCE_FALLBACK_LOCAL = 4;

    // ===========================
    // Relations (optional but useful)
    // ===========================
    public function fuelExpense(): HasOne
    {
        return $this->hasOne(TripExpense::class, 'truck_odometer_event_id');
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function trip(): BelongsTo
{
    return $this->belongsTo(\App\Models\Trip::class, 'trip_id');
}

public function step(): BelongsTo
{
    return $this->belongsTo(\App\Models\TripStep::class, 'trip_step_id');
}

public function expense(): BelongsTo
{
    return $this->belongsTo(\App\Models\TripExpense::class, 'trip_expense_id');
}
}
