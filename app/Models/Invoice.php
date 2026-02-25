<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'trip_id',
        'trip_cargo_id',
        'invoice_no',
        'issued_at',
        'due_date',
        'payer_type_id',
        'payer_client_id',
        'currency',
        'subtotal',
        'tax_percent',
        'tax_total',
        'total',
        'pdf_file',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_date'  => 'date',
        'subtotal'  => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total'     => 'decimal:2',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(TripCargo::class, 'trip_cargo_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'payer_client_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    // --- Computed helpers (status without DB column) ---
    public function getPaidTotalAttribute(): string
    {
        // returns string decimal (same as decimal cast style)
        return (string) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): string
    {
        $paid = (float) $this->paid_total;
        $total = (float) $this->total;
        return number_format($total - $paid, 2, '.', '');
    }
  
}
