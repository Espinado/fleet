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
        'type_id',
        'inspection_issued',
        'inspection_expired',
        'insurance_number',
        'insurance_issued',
        'insurance_expired',
        'insurance_company',
        'tir_issued',
        'tir_expired',
        'company',
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
}
