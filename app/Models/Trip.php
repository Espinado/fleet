<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TripStatus;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        // === Expeditor Snapshot ===
        'expeditor_id', 'expeditor_name', 'expeditor_reg_nr',
        'expeditor_country', 'expeditor_city', 'expeditor_address',
        'expeditor_post_code', 'expeditor_email', 'expeditor_phone',
        'expeditor_bank_id', 'expeditor_bank', 'expeditor_iban', 'expeditor_bic',

        // Transport
        'driver_id', 'truck_id', 'trailer_id',

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
    ];

    /** ========================
     *  RELATIONS
     * ======================== */

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function trailer()
    {
        return $this->belongsTo(Trailer::class);
    }

    public function cargos()
    {
        return $this->hasMany(TripCargo::class)->orderBy('id');
    }

   public function steps()
{
    return $this->hasMany(TripStep::class)
        ->orderByRaw('`order` ASC')
        ->orderBy('id');
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
}
