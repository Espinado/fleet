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
        'personal_code',
        'citizenship',
        'declared_country',
        'declared_city',
        'declared_street',
        'declared_building',
        'declared_room',
        'declared_postcode',
        'actual_country',
        'actual_city',
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
          'pers_code',
                'photo',
                'license_photo',
                'medical_certificate_photo',
                'medical_exam_passed',
                'medical_exam_expired',
    ];

    protected $casts = [
        'license_issued'     => 'date',
        'license_end'        => 'date',
        'permit_issued'      => 'date',
        'permit_expired'     => 'date',
        'medical_issued'     => 'date',
        'medical_expired'    => 'date',
        'declaration_issued' => 'date',
        'declaration_expired'=> 'date',
        'is_active'          => 'boolean',
        'status' => DriverStatus::class,
    ];
    

    /**
     * Удобный аксессор для ФИО
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

     public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '❓ Unknown';
    }
}
