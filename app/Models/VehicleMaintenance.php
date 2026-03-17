<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    protected $table = 'vehicle_maintenance';

    protected $fillable = [
        'company_id',
        'truck_id',
        'trailer_id',
        'performed_at',
        'odometer_km',
        'description',
        'cost',
    ];

    protected $casts = [
        'performed_at' => 'date',
        'cost' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Trailer::class);
    }

    /** Марка и номер (тягач или прицеп) для отображения в журнале. */
    public function getVehicleNameAttribute(): string
    {
        if ($this->truck_id) {
            $t = $this->truck;
            return $t ? trim(($t->brand ?? '') . ' ' . ($t->model ?? '') . ' ' . ($t->plate ?? '')) : '—';
        }
        if ($this->trailer_id) {
            $t = $this->trailer;
            return $t ? trim(($t->brand ?? '') . ' ' . ($t->plate ?? '')) : '—';
        }
        return '—';
    }

    /** Тип ТС: truck или trailer. */
    public function getVehicleTypeAttribute(): string
    {
        return $this->truck_id ? 'truck' : 'trailer';
    }
}
