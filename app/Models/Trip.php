<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TripStatus;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'expeditor_id','expeditor_name','expeditor_reg_nr','expeditor_country','expeditor_city',
        'expeditor_address','expeditor_post_code','expeditor_email','expeditor_phone',
        'driver_id','truck_id','trailer_id','client_id',
        'route_from','route_to','start_date','end_date','cargo',
        'price','currency','status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'status'     => TripStatus::class, // ✅ enum cast
    ];

    // === Связи ===
    public function client()  { return $this->belongsTo(Client::class); }
    public function driver()  { return $this->belongsTo(Driver::class); }
    public function truck()   { return $this->belongsTo(Truck::class); }
    public function trailer() { return $this->belongsTo(Trailer::class); }

    // === Отображаемое имя статуса ===
    public function getStatusLabelAttribute(): string
    {
        $status = $this->status instanceof TripStatus
            ? $this->status
            : TripStatus::from($this->status);

        return $status->label();
    }
}
