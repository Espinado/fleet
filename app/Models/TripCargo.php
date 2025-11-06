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
        'customer_id',
        'cmr_file',
        'cmr_created_at',
        'order_created_at',
        'order_nr',
        'cmr_nr',

        // Loading
        'loading_country_id',
        'loading_city_id',
        'loading_address',
        'loading_date',

        // Unloading
        'unloading_country_id',
        'unloading_city_id',
        'unloading_address',
        'unloading_date',

        // Cargo
        'cargo_description',
        'cargo_packages',
        'cargo_paletes',
        'cargo_tonnes',
        'cargo_weight',
        'cargo_netto_weight',
        'cargo_volume',
        'cargo_marks',
        'cargo_instructions',
        'cargo_remarks',
        'order_file',

        // Payment
        'price',
        'total_tax_amount',
        'tax_percent',
        'price_with_tax',
        'tax_percent'  ,
        'currency',
        'payment_terms',
        'payer_type_id',
        'inv_nr',
        'inv_file',
        'inv_created_at'

    ];

    protected $casts = [
        'loading_date'   => 'date',
        'unloading_date' => 'date',
        'payment_terms'  => 'date',
    ];
    protected $with = ['customer', 'shipper', 'consignee'];

    /** === Связи === */
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

    public function items()
    {
        return $this->hasMany(TripCargoItem::class, 'trip_cargo_id');
    }
    public function customer()
{
    return $this->belongsTo(Client::class, 'customer_id');
}
}
