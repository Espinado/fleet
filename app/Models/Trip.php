<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        // === Expeditor Snapshot ===
        'expeditor_id', 'expeditor_name', 'expeditor_reg_nr',
        'expeditor_country', 'expeditor_city', 'expeditor_address',
        'expeditor_post_code', 'expeditor_email', 'expeditor_phone',
        'expeditor_bank_id', 'expeditor_bank', 'expeditor_iban', 'expeditor_bic',
        'started_at', 'ended_at', 'odo_start_km', 'odo_end_km', 'carrier_company_id',

        // Transport
        'driver_id', 'truck_id', 'trailer_id', 'vehicle_run_id', 'cont_nr', 'seal_nr', 'customs',
        'customs_address',

        // Dates
        'start_date', 'end_date',

        // Currency
        'currency',

        // Status
        'status',

        // Misc
        'notes',
        'tracking_token',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'status'     => TripStatus::class,
         'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /** ========================
     *  RELATIONS
     * ======================== */

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }



protected static function booted(): void
{
    static::addGlobalScope('company', function (Builder $builder) {

        // Берём пользователя из web, если нет — из driver
        $user = Auth::guard('web')->user() ?? Auth::guard('driver')->user();

        if (!$user) {
            return;
        }

        // ✅ админ видит всё
        if (($user->role ?? null) === 'admin') {
            return;
        }

        // ✅ driver видит только свои рейсы (по driver_id)
        if (($user->role ?? null) === 'driver' && $user->driver) {
            $builder->where('driver_id', (int) $user->driver->id);
            return;
        }

        // ✅ остальные роли — по компании (как было)
        if (empty($user->company_id)) {
            $builder->whereRaw('1=0');
            return;
        }

        $builder->where('carrier_company_id', (int) $user->company_id);
    });
}

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function trailer()
    {
        return $this->belongsTo(Trailer::class);
    }



 public function steps()
{
    return $this->hasMany(TripStep::class)
        ->orderBy('order')
        ->orderBy('id');
}

public function cargos()
{
    return $this->hasMany(TripCargo::class);
}



    public function history()
    {
        return $this->hasMany(TripStatusHistory::class);
    }

    public function documents()
    {
        return $this->hasMany(TripDocument::class);
    }

    /** Один рейс может объединять несколько заказов (консолидация). */
    public function transportOrders()
    {
        return $this->hasMany(TransportOrder::class);
    }

    /** Первый заказ рейса (обратная совместимость). */
    public function transportOrder()
    {
        return $this->hasOne(TransportOrder::class)->oldest('id');
    }

    /** ========================
     *  ACCESSORS
     * ======================== */

    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof TripStatus
            ? $this->status->label()
            : TripStatus::from($this->status)->label();
    }

    /** ========================
     *  MAIN CLIENT (from cargos)
     * ======================== */

    public function client()
    {
        return $this->hasOneThrough(
            Client::class,
            TripCargo::class,
            'trip_id',      // TripCargo.trip_id
            'id',           // Client.id
            'id',           // Trip.id
            'customer_id'   // TripCargo.customer_id
        );
    }

   public function expenses()
{
    return $this->hasMany(\App\Models\TripExpense::class);
}

public function vehicleRun(): BelongsTo
{
    return $this->belongsTo(\App\Models\VehicleRun::class, 'vehicle_run_id');
}

public function odometerEvents(): HasMany
{
    return $this->hasMany(\App\Models\TruckOdometerEvent::class, 'trip_id');
}

public function scopeActiveForDriver($query, int $driverId)
{
    return $query
        ->where('driver_id', $driverId)
        ->whereHas('vehicleRun', function ($q) {
            $q->where('status', 'open');
        });
}

    /**
     * Добавить заказ в рейс: создать TripStep и TripCargo из OrderStep/OrderCargo, привязать заказ (trip_id, status CONVERTED).
     * Вызывается из ViewTrip::addOrdersToTrip и из ShowOrder::addOrderToTrip.
     */
    public function appendOrder(TransportOrder $order): void
    {
        $order->loadMissing(['steps' => fn ($q) => $q->orderBy('order')], 'cargos');

        $maxOrder = (int) $this->steps()->max('order');
        $newStepIdsByType = ['loading' => null, 'unloading' => null];

        foreach ($order->steps as $os) {
            $maxOrder++;
            $dbStep = TripStep::create([
                'trip_id'         => $this->id,
                'order'           => $maxOrder,
                'type'            => $os->type ?? 'loading',
                'country_id'      => $os->country_id,
                'city_id'         => $os->city_id,
                'address'         => $os->address,
                'contact_phone_1'  => $os->contact_phone,
                'contact_phone_2'  => null,
                'date'            => $os->date,
                'time'            => $os->time,
                'notes'           => $os->notes,
            ]);
            $type = $os->type ?? 'loading';
            if (($type === 'loading' && $newStepIdsByType['loading'] === null) || ($type === 'unloading' && $newStepIdsByType['unloading'] === null)) {
                $newStepIdsByType[$type] = $dbStep->id;
            }
        }

        $loadingStepId = $newStepIdsByType['loading'] ?? $this->steps()->where('type', 'loading')->orderBy('order')->value('id');
        $unloadingStepId = $newStepIdsByType['unloading'] ?? $this->steps()->where('type', 'unloading')->orderBy('order')->value('id');

        $customerId = $order->customer_id;
        if ($customerId === null && $order->cargos->isNotEmpty()) {
            $customerId = $order->cargos->first()->customer_id;
        }

        foreach ($order->cargos as $oc) {
            $price = (float) ($oc->quoted_price ?? 0);
            $taxPercent = 21.0;
            $tax = \App\Helpers\CalculateTax::calculate($price, $taxPercent);

            $cargo = TripCargo::create([
                'trip_id'             => $this->id,
                'transport_order_id'  => $order->id,
                'customer_id'         => $oc->customer_id,
                'shipper_id'          => $oc->shipper_id ?? $oc->customer_id,
                'consignee_id'        => $oc->consignee_id ?? $oc->customer_id,
                'price'               => $price,
                'tax_percent'         => $taxPercent,
                'total_tax_amount'    => $tax['tax_amount'],
                'price_with_tax'      => $tax['price_with_tax'],
                'currency'            => 'EUR',
                'payment_terms'       => null,
                'payment_days'        => 30,
                'payer_type_id'       => null,
                'commercial_invoice_nr' => null,
                'commercial_invoice_amount' => null,
            ]);

            $cargo->items()->create([
                'description'     => $oc->description ?? '',
                'customs_code'    => $oc->customs_code,
                'packages'        => (int) ($oc->packages ?? 0),
                'pallets'         => (int) ($oc->pallets ?? 0),
                'units'           => (int) ($oc->units ?? 0),
                'net_weight'      => (float) ($oc->net_weight ?? $oc->weight_kg ?? 0),
                'gross_weight'    => (float) ($oc->gross_weight ?? $oc->weight_kg ?? 0),
                'tonnes'          => (float) ($oc->tonnes ?? 0),
                'volume'          => (float) ($oc->volume_m3 ?? 0),
                'loading_meters'  => (float) ($oc->loading_meters ?? 0),
                'hazmat'          => $oc->hazmat ?? '',
                'temperature'     => $oc->temperature ?? '',
                'stackable'       => (bool) ($oc->stackable ?? false),
                'instructions'    => $oc->instructions ?? '',
                'remarks'         => $oc->remarks ?? '',
            ]);

            $pivot = [];
            if ($loadingStepId) {
                $pivot[$loadingStepId] = ['role' => 'loading'];
            }
            if ($unloadingStepId && $unloadingStepId !== $loadingStepId) {
                $pivot[$unloadingStepId] = ['role' => 'unloading'];
            }
            if ($pivot) {
                $cargo->steps()->attach($pivot);
            }
        }

        if ($order->cargos->isEmpty()) {
            $cargo = TripCargo::create([
                'trip_id'             => $this->id,
                'transport_order_id'  => $order->id,
                'customer_id'         => $customerId,
                'shipper_id'          => $customerId,
                'consignee_id'        => $customerId,
                'price'               => 0,
                'tax_percent'         => 21.0,
                'total_tax_amount'    => 0,
                'price_with_tax'      => 0,
                'currency'            => 'EUR',
                'payment_terms'       => null,
                'payment_days'        => 30,
                'payer_type_id'       => null,
                'commercial_invoice_nr' => null,
                'commercial_invoice_amount' => null,
            ]);
            $cargo->items()->create([
                'description' => '', 'customs_code' => null, 'packages' => 0, 'pallets' => 0, 'units' => 0,
                'net_weight' => 0, 'gross_weight' => 0, 'tonnes' => 0, 'volume' => 0, 'loading_meters' => 0,
                'hazmat' => '', 'temperature' => '', 'stackable' => false, 'instructions' => '', 'remarks' => '',
            ]);
            $pivot = [];
            if ($loadingStepId) {
                $pivot[$loadingStepId] = ['role' => 'loading'];
            }
            if ($unloadingStepId && $unloadingStepId !== $loadingStepId) {
                $pivot[$unloadingStepId] = ['role' => 'unloading'];
            }
            if ($pivot) {
                $cargo->steps()->attach($pivot);
            }
        }

        $order->update([
            'trip_id' => $this->id,
            'status'  => \App\Enums\OrderStatus::CONVERTED->value,
        ]);
    }

    /** Найти рейс по публичному токену отслеживания (без учёта глобального scope). */
    public static function findByTrackingToken(string $token): ?self
    {
        return static::withoutGlobalScopes()->where('tracking_token', $token)->first();
    }

    /** Сгенерировать и сохранить токен для публичной ссылки отслеживания. */
    public function enableTracking(): string
    {
        if ($this->tracking_token) {
            return $this->tracking_token;
        }
        $this->tracking_token = \Illuminate\Support\Str::random(48);
        $this->saveQuietly();
        return $this->tracking_token;
    }

    /** Отключить публичное отслеживание. */
    public function disableTracking(): void
    {
        $this->tracking_token = null;
        $this->saveQuietly();
    }
public function invoices()
{
    return $this->hasMany(\App\Models\Invoice::class);
}
public function carrierCompany()
{
    return $this->belongsTo(\App\Models\Company::class, 'carrier_company_id');
}

public function expeditorCompany()
{
    return $this->belongsTo(\App\Models\Company::class, 'expeditor_id');
}
public function getSchemeKeyAttribute(): string
{
    $carrier = $this->carrierCompany;

    // Если перевозчик — третья сторона, это не собственный транспорт (показываем 3. PUSE, а не ĪPAŠUMS)
    if ($carrier?->is_third_party) return 'third_party';
    if ($this->driver_id) return 'own';
    return 'resell';
}
public function getSchemeLabelAttribute(): string
{
    return match ($this->scheme_key) {
        'own' => 'СВОИ',
        'third_party' => '3RD PARTY',
        default => 'ПЕРЕПРОДАЖА',
    };
}

public function getSchemeBadgeClassAttribute(): string
{
    return match ($this->scheme_key) {
        'own' => 'bg-green-100 text-green-800',
        'third_party' => 'bg-red-100 text-red-800',
        default => 'bg-amber-100 text-amber-900',
    };
}

/**
 * Проверка: есть ли другой рейс с тем же водителем в пересекающиеся даты.
 * Один рейс = один водитель; в один период водитель не может быть в двух рейсах.
 *
 * @param int $driverId
 * @param string|\Carbon\Carbon|null $startDate Y-m-d или Carbon
 * @param string|\Carbon\Carbon|null $endDate Y-m-d или Carbon (если null — считается как startDate)
 * @param int|null $excludeTripId Исключить рейс из проверки (при редактировании)
 */
public static function hasOverlappingDriver(int $driverId, $startDate, $endDate, ?int $excludeTripId = null): bool
{
    $start = $startDate ? (\Carbon\Carbon::parse($startDate))->format('Y-m-d') : null;
    $end = $endDate ? (\Carbon\Carbon::parse($endDate))->format('Y-m-d') : $start;
    if ($start === null) {
        return false;
    }

    $q = static::query()
        ->where('driver_id', $driverId)
        ->where(function ($b) use ($start, $end) {
            $b->where('start_date', '<=', $end)
                ->where(function ($b2) use ($start) {
                    $b2->where('end_date', '>=', $start)
                        ->orWhereNull('end_date');
                });
        });
    if ($excludeTripId !== null) {
        $q->where('id', '!=', $excludeTripId);
    }
    return $q->exists();
}

/**
 * Проверка: есть ли другой рейс с той же машиной в пересекающиеся даты.
 * Один рейс = одна машина; в один период машина не может быть в двух рейсах.
 *
 * @param int $truckId
 * @param string|\Carbon\Carbon|null $startDate
 * @param string|\Carbon\Carbon|null $endDate
 * @param int|null $excludeTripId
 */
public static function hasOverlappingTruck(int $truckId, $startDate, $endDate, ?int $excludeTripId = null): bool
{
    $start = $startDate ? (\Carbon\Carbon::parse($startDate))->format('Y-m-d') : null;
    $end = $endDate ? (\Carbon\Carbon::parse($endDate))->format('Y-m-d') : $start;
    if ($start === null) {
        return false;
    }

    $q = static::query()
        ->where('truck_id', $truckId)
        ->where(function ($b) use ($start, $end) {
            $b->where('start_date', '<=', $end)
                ->where(function ($b2) use ($start) {
                    $b2->where('end_date', '>=', $start)
                        ->orWhereNull('end_date');
                });
        });
    if ($excludeTripId !== null) {
        $q->where('id', '!=', $excludeTripId);
    }
    return $q->exists();
}
}
