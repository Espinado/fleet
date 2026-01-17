<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\DriverStatus;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'drivers';

    protected $fillable = [
        'first_name',
        'last_name',
        'pers_code',
        'company',
        'citizenship_id',
        'declared_country_id',
        'declared_city_id',
        'declared_street',
        'declared_building',
        'declared_room',
        'declared_postcode',
        'actual_country_id',
        'actual_city_id',
        'actual_street',
        'actual_building',
        'actual_room',
        'phone',
        'email',
        'license_number',
        'license_issued',
        'license_end',
        'code95_issued',
        'code95_end',
        'permit_issued',
        'permit_expired',
        'medical_issued',
        'medical_expired',
        'declaration_issued',
        'declaration_expired',
        'status',
        'is_active',
        'photo',
        'license_photo',
        'medical_certificate_photo',
        'medical_exam_passed',
        'medical_exam_expired',
        'login_pin',
        'user_id',
    ];

    protected $casts = [
        'code95_issued'      => 'date',
        'code95_end'         => 'date',
        'license_issued'      => 'date',
        'license_end'         => 'date',
        'permit_issued'       => 'date',
        'permit_expired'      => 'date',
        'medical_issued'      => 'date',
        'medical_expired'     => 'date',
        'declaration_issued'  => 'date',
        'declaration_expired' => 'date',
        'is_active' => 'boolean',
        'status'    => DriverStatus::class, // ОК: enum<int>
    ];

    /**
     * 🧾 Удобный аксессор для ФИО
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * 🚦 Аксессор для текстовой метки статуса
     */
    public function getStatusLabelAttribute(): string
    {
        // status — это DriverStatus|nullable благодаря casts
        return $this->status?->label() ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'gray';
    }

 public function getPhotoUrlAttribute(): ?string
{
    if (!$this->photo) {
        return null;
    }

    // Если фото уже полный URL
    if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
        return $this->photo;
    }

    // Проверяем, не содержит ли путь "public/"
    $path = str_replace('public/', '', $this->photo);

    // Строим корректный URL для публичного доступа
    return asset('storage/' . $path);
}

public function user()
{
    return $this->belongsTo(User::class);
}

public function activeTrip()
{
    return $this->hasOne(\App\Models\Trip::class, 'driver_id', 'id')
        ->whereNull('end_date')        // ✅ активный = не завершён по дате
        ->latest('id');
}


}