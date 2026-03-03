<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Client> */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company(),
            'reg_nr' => $this->faker->numerify('########'),
            'representative' => $this->faker->name(),
            'jur_country_id' => 16,
            'jur_city_id' => 1,
            'jur_address' => $this->faker->streetAddress(),
            'jur_post_code' => $this->faker->postcode(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->e164PhoneNumber(),
        ];
    }
}
