<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        // === Expeditor Snapshot ===
        'expeditor_id', 'expeditor_name', 'expeditor_reg_nr',
        'expeditor_country', 'expeditor_city', 'expeditor_address',
        'expeditor_post_code', 'expeditor_email', 'expeditor_phone',
        'expeditor_bank_id', 'expeditor_bank', 'expeditor_iban', 'expeditor_bic',
         'started_at', 'ended_at', 'odo_start_km', 'odo_end_km', 'carrier_company_id',

        // Transport
        'driver_id', 'truck_id', 'trailer_id', 'vehicle_run_id',  'cont_nr', 'seal_nr','customs',
        'customs_address',

        // Dates
        'start_date', 'end_date',

        // Currency
        'currency',

        // Status
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'status'     => TripStatus::class,
         'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /** ========================
     *  RELATIONS
     * ======================== */

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

 protected static function booted(): void
{
    static::addGlobalScope('company', function (Builder $builder) {
        $user = Auth::user();

        if (!$user) {
            return; // console/seed/queue
        }

        // ✅ админ видит всё
        if (($user->role ?? null) === 'admin') {
            return;
        }

        // если компания не задана — ничего не показываем
        if (empty($user->company_id)) {
            $builder->whereRaw('1=0');
            return;
        }

        // фильтр по компании перевозчика
        $builder->where('carrier_company_id', (int) $user->company_id);
    });
}

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function trailer()
    {
        return $this->belongsTo(Trailer::class);
    }



 public function steps()
{
    return $this->hasMany(TripStep::class)
        ->orderBy('order')
        ->orderBy('id');
}

public function cargos()
{
    return $this->hasMany(TripCargo::class);
}



    public function history()
    {
        return $this->hasMany(TripStatusHistory::class);
    }

    public function documents()
    {
        return $this->hasMany(TripDocument::class);
    }

    /** ========================
     *  ACCESSORS
     * ======================== */

    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof TripStatus
            ? $this->status->label()
            : TripStatus::from($this->status)->label();
    }

    /** ========================
     *  MAIN CLIENT (from cargos)
     * ======================== */

    public function client()
    {
        return $this->hasOneThrough(
            Client::class,
            TripCargo::class,
            'trip_id',      // TripCargo.trip_id
            'id',           // Client.id
            'id',           // Trip.id
            'customer_id'   // TripCargo.customer_id
        );
    }

   public function expenses()
{
    return $this->hasMany(\App\Models\TripExpense::class);
}

public function vehicleRun(): BelongsTo
{
    return $this->belongsTo(\App\Models\VehicleRun::class, 'vehicle_run_id');
}

public function odometerEvents(): HasMany
{
    return $this->hasMany(\App\Models\TruckOdometerEvent::class);
}

public function scopeActiveForDriver($query, int $driverId)
{
    return $query
        ->where('driver_id', $driverId)
        ->whereHas('vehicleRun', function ($q) {
            $q->where('status', 'open');
        });
}
public function invoices()
{
    return $this->hasMany(\App\Models\Invoice::class);
}

}
