<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\TripCargo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripCargo>
 */
class TripCargoFactory extends Factory
{
    protected $model = TripCargo::class;

    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'EUR',
            'tax_percent' => 21,
            'total_tax_amount' => 0,
            'price_with_tax' => 0,
        ];
    }

}
