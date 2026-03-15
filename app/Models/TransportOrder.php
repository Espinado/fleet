<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'order_date',
        'expeditor_id',
        'customer_id',
        'requested_date_from',
        'requested_date_to',
        'quoted_price',
        'currency',
        'status',
        'trip_id',
        'notes',
        'customs',
        'customs_address',
    ];

    protected $casts = [
        'order_date'          => 'date',
        'requested_date_from' => 'date',
        'requested_date_to'   => 'date',
        'quoted_price'        => 'decimal:2',
        'status'              => OrderStatus::class,
        'customs'             => 'boolean',
    ];

    public function expeditor(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'expeditor_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(OrderStep::class)->orderBy('order')->orderBy('id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(OrderCargo::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof OrderStatus
            ? $this->status->label()
            : OrderStatus::tryFrom($this->status)?->label() ?? (string) $this->status;
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $last = static::query()
            ->whereRaw('YEAR(created_at) = ?', [$year])
            ->orderByDesc('id')
            ->value('number');
        $seq = 1;
        if ($last && preg_match('/^TO-\d{4}-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }
        return sprintf('TO-%s-%05d', $year, $seq);
    }
}
