<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'type',          // carrier|forwarder|mixed (как у тебя)
        'is_broker',     // ✅ новый флаг: посредник (Рона)
        'is_third_party',// ✅ новый флаг: внешняя компания

        'reg_nr',
        'vat_nr',
        'country',
        'city',
        'address',
        'post_code',
        'email',
        'phone',
        'banks_json',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'banks_json'     => 'array',
        'is_system'      => 'boolean',
        'is_active'      => 'boolean',
        'is_broker'      => 'boolean',
        'is_third_party' => 'boolean',
    ];

    /** -----------------------
     *  Relationships
     *  ----------------------*/
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'carrier_company_id');
    }

    public function trucks()
    {
        return $this->hasMany(Truck::class, 'company_id');
    }

    public function trailers()
    {
        return $this->hasMany(Trailer::class, 'company_id');
    }

    /** -----------------------
     *  Helpers
     *  ----------------------*/
    public function isCarrier(): bool
    {
        return $this->type === 'carrier' || $this->type === 'mixed';
    }

    public function isForwarder(): bool
    {
        return $this->type === 'forwarder' || $this->type === 'mixed';
    }

    public function isBroker(): bool
    {
        return (bool) ($this->is_broker ?? false);
    }

    public function isThirdParty(): bool
    {
        return (bool) ($this->is_third_party ?? false);
    }
}
