<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    use HasFactory;

    protected $table = 'trucks';

    protected $fillable = [
        'brand',
        'model',
        'plate',
        'year',
        'company',
        'inspection_issued',
        'inspection_expired',
        'insurance_number',
        'insurance_issued',
        'insurance_expired',
        'insurance_company',
        'license_number',
        'license_issued',
        'license_expired',
        'mapon_box_id',
        'mapon_unit_id',
        'vin',
        'status',
        'is_active',
           'tech_passport_nr',
                'tech_passport_issued',
                'tech_passport_expired',
                'tech_passport_photo',
    ];

   protected $casts = [
    'inspection_issued' => 'date',
    'inspection_expired' => 'date',
    'insurance_issued' => 'date',
    'insurance_expired' => 'date',
    'tech_passport_issued' => 'date',
    'tech_passport_expired' => 'date',
     'license_issued'  => 'date',
    'license_expired' => 'date',
    
];

    public function getDisplayNameAttribute(): string
    {
        return "{$this->brand} {$this->model} ({$this->plate})";
    }

    public function vehicleRuns(): HasMany
{
    return $this->hasMany(\App\Models\VehicleRun::class);
}

public function odometerEvents(): HasMany
{
    return $this->hasMany(\App\Models\OdometerEvent::class);
}
}
