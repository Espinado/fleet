<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trailer extends Model
{
    use HasFactory;

    protected $table = 'trailers';

    protected $fillable = [
        'brand',
        'plate',
        'year',
        'inspection_issued',
        'inspection_expired',
        'insurance_number',
        'insurance_issued',
        'insurance_expired',
        'insurance_company',
        'tir_issued',
        'tir_expired',
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
        'tir_issued' => 'date',
        'tir_expired' => 'date',
    ];
}
