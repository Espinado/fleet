<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trailer extends Model
{
    use HasFactory;

    protected $table = 'trailers';

    protected $fillable = [
        'brand',
        'plate',
        'year',
        'type_id',
        'inspection_issued',
        'inspection_expired',
        'insurance_number',
        'insurance_issued',
        'insurance_expired',
        'insurance_company',
        'tir_issued',
        'tir_expired',
        'company_id',
        'vin',
        'status',
        'is_active',
         'tech_passport_nr',
                'tech_passport_issued',
                'tech_passport_expired',
                'tech_passport_photo',
        'next_service_km',
        'next_service_date',
        'service_interval_km',
        'service_interval_months',
    ];

    protected $casts = [
        'inspection_issued' => 'date',
        'inspection_expired' => 'date',
        'insurance_issued' => 'date',
        'insurance_expired' => 'date',
        'tir_issued' => 'date',
        'tir_expired' => 'date',
        'next_service_date' => 'date',
    ];

    public function getTypeKeyAttribute(): ?string
{
    $id = $this->type_id;
    return $id ? config("trailer-types.types.$id") : null;
}

public function getTypeLabelAttribute(): ?string
{
    $key = $this->type_key;
    return $key ? config("trailer-types.labels.$key", $key) : null;
}

public function getTypeIconAttribute(): ?string
{
    $key = $this->type_key;
    return $key ? config("trailer-types.icons.$key") : null;
}

/** URL для отображения фото техпаспорта (поддержка и storage path, и внешнего URL из сидера). */
public function getTechPassportPhotoUrlAttribute(): ?string
{
    $value = $this->tech_passport_photo;
    if ($value === null || $value === '') {
        return null;
    }
    if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
        return $value;
    }
    $path = str_replace('public/', '', $value);
    return asset('storage/' . $path);
}

public function company()
{
    return $this->belongsTo(\App\Models\Company::class);
}

public function maintenanceRecords(): HasMany
{
    return $this->hasMany(\App\Models\VehicleMaintenance::class, 'trailer_id')->orderByDesc('performed_at');
}
}
