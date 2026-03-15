<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_order_id',
        'customer_id',
        'shipper_id',
        'consignee_id',
        'weight_kg',
        'net_weight',
        'gross_weight',
        'tonnes',
        'volume_m3',
        'loading_meters',
        'pallets',
        'packages',
        'units',
        'description',
        'customs_code',
        'hazmat',
        'temperature',
        'stackable',
        'instructions',
        'remarks',
        'quoted_price',
        'requested_date_from',
        'requested_date_to',
    ];

    protected $casts = [
        'weight_kg'            => 'decimal:2',
        'net_weight'           => 'decimal:2',
        'gross_weight'         => 'decimal:2',
        'tonnes'               => 'decimal:3',
        'volume_m3'            => 'decimal:3',
        'loading_meters'       => 'decimal:2',
        'quoted_price'         => 'decimal:2',
        'requested_date_from'  => 'date',
        'requested_date_to'    => 'date',
        'stackable'            => 'boolean',
    ];

    public function transportOrder(): BelongsTo
    {
        return $this->belongsTo(TransportOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'shipper_id');
    }

    public function consignee(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'consignee_id');
    }
}
