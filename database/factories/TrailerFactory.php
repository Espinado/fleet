<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trailer>
 */
class TrailerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'brand' => $this->faker->randomElement(['Krone', 'Schmitz', 'Kögel']),
            'plate' => strtoupper($this->faker->bothify('??####')),
            'year' => $this->faker->year(),
            'inspection_issued' => $this->faker->dateTimeBetween('-2 years', '-1 year'),
            'inspection_expired' =>$this->faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'insurance_number' => $this->faker->bothify('TR-INS-#####'),
            'insurance_issued' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'insurance_expired' =>$this->faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'insurance_company' => $this->faker->company(),
            'tir_issued' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'tir_expired' => $this->faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'vin' => strtoupper($this->faker->unique()->bothify('#################')),
            'status' => 1,
            'is_active' => true,
        ];
    }
}
