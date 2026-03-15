<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_order_id',
        'type',
        'country_id',
        'city_id',
        'address',
        'date',
        'time',
        'contact_phone',
        'order',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function transportOrder(): BelongsTo
    {
        return $this->belongsTo(TransportOrder::class);
    }
}
