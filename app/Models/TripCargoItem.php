<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCargoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_cargo_id',

        // Description
        'description',

        // Quantities
        'packages',          // упаковок
        'pallets',           // паллет
        'units',             // штук

        // Weight
        'net_weight',        // кг (нетто)
        'gross_weight',      // кг (брутто)
        'tonnes',            // т (1 т = 1000 кг)

        // Volume & loading efficiency
        'volume',            // м³
        'loading_meters',    // LM — погрузочные метры

        // Special cargo
        'hazmat',            // ADR (e.g. 3, 4.1, 8)
        'temperature',       // "+2..+6"
        'stackable',         // bool (1/0)

        // Notes
        'instructions',
        'remarks',

        // Price (optional, если когда-нибудь будем считать по позициям)
        'price',
        'tax_percent',
        'tax_amount',
        'price_with_tax',
    ];

    protected $casts = [
        'packages'       => 'integer',
        'pallets'        => 'integer',
        'units'          => 'integer',

        'net_weight'     => 'float',
        'gross_weight'   => 'float',
        'tonnes'         => 'float',
        'volume'         => 'float',
        'loading_meters' => 'float',

        'stackable'      => 'boolean',

        'price'          => 'float',
        'tax_percent'    => 'float',
        'tax_amount'     => 'float',
        'price_with_tax' => 'float',
    ];

    public function cargo()
    {
        return $this->belongsTo(TripCargo::class, 'trip_cargo_id');
    }
}
