<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\TripStep;
use App\Enums\TripStepStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripStep>
 */
class TripStepFactory extends Factory
{
    protected $model = TripStep::class;

    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'type' => $this->faker->randomElement(['loading', 'unloading']),
            'order' => $this->faker->numberBetween(1, 10),
            'address' => $this->faker->address(),
            'date' => now()->addDays($this->faker->numberBetween(1, 5)),
            'time' => '08:00',
            'status' => TripStepStatus::NOT_STARTED,
        ];
    }
}
