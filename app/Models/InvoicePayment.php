<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'paid_at',
        'amount',
        'currency',
        'method',
        'reference',
        'note',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'amount'  => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
