<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',

        // Relations
        'customer_id',
        'shipper_id',
        'consignee_id',

        // Files (order, cmr, invoice)
        'order_file',
        'order_created_at',
        'order_nr',
        'cmr_file',
        'cmr_nr',
        'cmr_created_at',
        'inv_nr',
        'inv_file',
        'inv_created_at',

        // Payment
        'price',
        'tax_percent',
        'total_tax_amount',
        'price_with_tax',
        'currency',
        'payment_terms',
        'payer_type_id',
    ];

    protected $casts = [
        'payment_terms'    => 'date',
        'order_created_at' => 'datetime',
        'cmr_created_at'   => 'datetime',
        'inv_created_at'   => 'datetime',
        'price'            => 'float',
        'tax_percent'      => 'float',
        'total_tax_amount' => 'float',
        'price_with_tax'   => 'float',
    ];

    protected $with = ['customer', 'shipper', 'consignee', 'items'];

    /** ========================
     *  RELATIONS
     * ======================== */

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function customer()
    {
        return $this->belongsTo(Client::class, 'customer_id');
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

    /**
     * Шаги маршрута, к которым привязан этот груз (many-to-many через pivot trip_cargo_step)
     */
   

 public function steps()
{
    return $this->belongsToMany(TripStep::class, 'trip_cargo_step')
        ->withPivot(['role']);
}

}
