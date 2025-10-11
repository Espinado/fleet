<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

         $birthDate = $this->faker->dateTimeBetween('1970-01-01', '2000-12-31');
        $datePart  = $birthDate->format('ymd'); // например 850321
        $randomPart = $this->faker->numerify('#####'); // например 12345
        $persCode = $datePart . '-' . $randomPart;

        // Даты медосмотра
        $passed  = $this->faker->dateTimeBetween('-1 month', 'yesterday');
        $expired = (clone $passed)->modify('+'.rand(1, 4).' months');
        return [
             'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            
            'citizenship' => $this->faker->country(),
            'declared_country' => $this->faker->country(),
            'declared_city' => $this->faker->city(),
            'declared_street' => $this->faker->streetName(),
            'declared_building' => $this->faker->buildingNumber(),
           'declared_room' =>  $this->faker->numberBetween(1, 100),
            'actual_room'   => $this->faker->numberBetween(1, 100),
            'declared_postcode' => $this->faker->postcode(),
            'actual_country' => $this->faker->country(),
            'actual_city' => $this->faker->city(),
            'actual_street' => $this->faker->streetName(),
            'actual_building' => $this->faker->buildingNumber(),

            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'license_number' => $this->faker->unique()->bothify('LV#######'),
            'license_issued' => $this->faker->dateTimeBetween('-10 years', '-5 years'),
            'license_end' => $this->faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'code95_issued' => $this->faker->date(),
            'code95_end' => $this->faker->date(),
            'permit_issued' => $this->faker->optional()->date(),
            'permit_expired' => $this->faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'medical_issued' => $this->faker->dateTimeBetween('-3 years', '-1 years'),
            'medical_expired' => $this->faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'declaration_issued' => $this->faker->date(),
            'declaration_expired' => $this->faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'status' => 1,
            'is_active' => true,
             'pers_code' => $persCode,
            'photo' => $this->faker->imageUrl(640, 480, 'people'),
            'license_photo' => $this->faker->imageUrl(640, 480, 'documents'),
            'medical_certificate_photo' => $this->faker->imageUrl(640, 480, 'documents'),
            'medical_exam_passed' => $passed->format('Y-m-d'),
            'medical_exam_expired' => $expired->format('Y-m-d'),
        ];
    }
}
