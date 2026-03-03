<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\Truck;
use App\Models\Trailer;
use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        $exp = config('companies.1', [
            'name' => 'Test Expeditor',
            'reg_nr' => 'LV123',
            'country' => 'Latvia',
            'city' => 'Riga',
            'address' => 'Street 1',
            'post_code' => 'LV-1001',
            'email' => 'test@test.lv',
            'phone' => '+37120000000',
        ]);
        $bank = $exp['bank'][1] ?? ['name' => 'Bank', 'iban' => 'LV00', 'bic' => 'BIC'];

        return [
            'expeditor_id' => 1,
            'expeditor_name' => $exp['name'],
            'expeditor_reg_nr' => $exp['reg_nr'] ?? null,
            'expeditor_country' => $exp['country'] ?? null,
            'expeditor_city' => $exp['city'] ?? null,
            'expeditor_address' => $exp['address'] ?? null,
            'expeditor_post_code' => $exp['post_code'] ?? null,
            'expeditor_email' => $exp['email'] ?? null,
            'expeditor_phone' => $exp['phone'] ?? null,
            'expeditor_bank_id' => 1,
            'expeditor_bank' => $bank['name'] ?? null,
            'expeditor_iban' => $bank['iban'] ?? null,
            'expeditor_bic' => $bank['bic'] ?? null,
            'driver_id' => Driver::factory(),
            'truck_id' => Truck::factory(),
            'trailer_id' => Trailer::factory(),
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'currency' => 'EUR',
            'status' => TripStatus::PLANNED,
        ];
    }
}
