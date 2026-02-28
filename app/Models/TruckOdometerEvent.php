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
        'type',
        'odometer_km',
        'source',
        'occurred_at',
        'mapon_at',
        'is_stale',
        'stale_minutes',
        'raw',
        'note',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'mapon_at' => 'datetime',
        'is_stale' => 'boolean',
        'raw' => 'array',
        'odometer_km' => 'decimal:1',
    ];

    // ===========================
    // Types
    // ===========================
    public const TYPE_DEPARTURE = 1;
    public const TYPE_RETURN    = 2;
    public const TYPE_FUEL      = 3; // ✅ заправка

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
}
