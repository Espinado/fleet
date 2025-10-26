<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'reg_nr',
        'jur_country_id',
        'jur_city_id',
        'jur_address',
        'jur_post_code',
        'fiz_country_id',
        'fiz_city_id',
        'fiz_address',
        'fiz_post_code',
        'bank_name',
        'swift',
        'email',
        'phone',
        'representative',
    ];

    public function shipments()
{
    return $this->hasMany(Trip::class, 'shipper_id');
}

public function receptions()
{
    return $this->hasMany(Trip::class, 'consignee_id');
}
}
