<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCargo extends Model
{
    use HasFactory;

   protected $fillable = [
    'trip_id',
    'shipper_id',
    'consignee_id',

    // === Loading ===
    'loading_country_id',
    'loading_city_id',
    'loading_address',
    'loading_date',

    // === Unloading ===
    'unloading_country_id',
    'unloading_city_id',
    'unloading_address',
    'unloading_date',

    // === Cargo ===
    'cargo_description',
    'cargo_packages',
    'cargo_weight',
    'cargo_volume',
    'cargo_marks',
    'cargo_instructions',
    'cargo_remarks',

    // === Payment ===
    'price',
    'currency',
    'payment_terms',
    'payer_type_id',
];


    protected $casts = [
        'loading_date' => 'date',
        'unloading_date' => 'date',
        'payment_terms' => 'date',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function shipper()
    {
        return $this->belongsTo(Client::class, 'shipper_id');
    }

    public function consignee()
    {
        return $this->belongsTo(Client::class, 'consignee_id');
    }
}
