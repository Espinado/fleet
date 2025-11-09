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
        'expeditor_post_code', 'expeditor_email', 'expeditor_phone', 'expeditor_bank_id',
'expeditor_bank',
'expeditor_iban',
'expeditor_bic',

        // === Relations ===
        'driver_id', 'truck_id', 'trailer_id',
        'shipper_id', 'consignee_id',

        // === Route ===
        'origin_country_id', 'origin_city_id', 'origin_address',
        'destination_country_id', 'destination_city_id', 'destination_address',

        // === Cargo ===
        'cargo_description', 'cargo_packages', 'cargo_weight', 'cargo_volume',
        'cargo_marks', 'cargo_instructions', 'cargo_remarks',

        // === Payment ===
        'price', 'currency', 'payment_terms', 'payer_type_id',

        // === Other ===
        'start_date', 'end_date', 'status',
    ];

   protected $casts = [
    'start_date' => 'date',
    'end_date'   => 'date',
    'payment_terms' => 'date',
    'status'     => TripStatus::class,
];
    // === Relations ===
    public function driver()    { return $this->belongsTo(Driver::class); }
    public function truck()     { return $this->belongsTo(Truck::class); }
    public function trailer()   { return $this->belongsTo(Trailer::class); }
    public function shipper()   { return $this->belongsTo(Client::class, 'shipper_id'); }
    public function consignee() { return $this->belongsTo(Client::class, 'consignee_id'); }

    // === Accessors ===
    public function getStatusLabelAttribute(): string
    {
        $status = $this->status instanceof TripStatus
            ? $this->status
            : TripStatus::from($this->status);

        return $status->label();
    }

    // === Scope ===
    public function scopeForClient($query, int $clientId)
    {
        return $query->where('shipper_id', $clientId)
                     ->orWhere('consignee_id', $clientId);
    }

    public function client()
{
    // Возвращает shipper по умолчанию, если нужен «старый» client
    return $this->belongsTo(Client::class, 'shipper_id');
}

public function cargos()
{
    return $this->hasMany(TripCargo::class);
}
}
