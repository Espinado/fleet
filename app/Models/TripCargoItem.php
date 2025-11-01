<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCargoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_cargo_id',
        'description',
        'packages',
        'cargo_paletes',
        'cargo_tonnes',
        'weight',
        'cargo_netto_weight',
        'volume',
        'price',
        'tax_percent',
        'tax_amount',
        'price_with_tax',
        'instructions',
        'remarks',
    ];

    public function cargo()
    {
        return $this->belongsTo(TripCargo::class, 'trip_cargo_id');
    }
}
