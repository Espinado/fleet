<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TripStepStatus;
use Illuminate\Support\Str;

class TripStep extends Model
{
    protected $fillable = [
        'trip_id',
        'type',        // loading | unloading
        'client_id',   // ответственный клиент за точку
        'country_id',
        'city_id',
        'address',
        'date',
        'time',        // строка, удобнее в формах
        'order',
        'notes',

        // 🔥 добавляем:
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => TripStepStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /** ========================
     *  RELATIONS
     * ======================== */

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function documents()
    {
        return $this->hasMany(TripDocument::class, 'step_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Грузы, которые связаны с этим шагом (many-to-many через pivot trip_cargo_step)
     */
   

  public function cargos()
{
    return $this->belongsToMany(TripCargo::class, 'trip_cargo_step')
        ->withPivot(['role']);
}






public function stepDocuments()
{
    return $this->hasMany(TripStepDocument::class, 'trip_step_id');
}

public function typeLabel(): string
{
    return ($this->type === 'loading')
        ? '📦 Iekraušana'
        : '📤 Izkraušana';
}

public function addressLine(): string
{
    // подстрой под свои поля/формат
    $country = $this->country_id ? (getCountryNameById((int)$this->country_id) ?? null) : null;
    $city    = $this->city_id    ? (getCityNameById((int)$this->city_id) ?? null)       : null;

    return collect([
        $city,
        $country,
        $this->address,
    ])->filter()->implode(', ');
}

/**
 * Объединённая дата/время для вывода (у тебя date = date, time = string)
 */
public function dateTimeLabel(): string
{
    $d = $this->date?->format('d.m.Y') ?? '—';
    $t = $this->time ? trim($this->time) : null;

    return $t ? "{$d} {$t}" : $d;
}

public function shortLabel(): string
{
    return $this->typeLabel() . ' • ' . $this->addressLine() . ' • ' . $this->dateTimeLabel();
}

public function odometerEvents(): HasMany
{
    return $this->hasMany(\App\Models\OdometerEvent::class, 'trip_step_id');
}

}
