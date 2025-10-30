<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

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
        $faker = $this->faker;

        // === Персональный код ===
        $birthDate  = $faker->dateTimeBetween('1970-01-01', '2000-12-31');
        $datePart   = $birthDate->format('ymd'); // например: 850321
        $randomPart = $faker->numerify('#####'); // например: 12345
        $persCode   = $datePart . '-' . $randomPart;

        // === Даты медосмотра ===
        $passed  = $faker->dateTimeBetween('-1 month', 'yesterday');
        $expired = (clone $passed)->modify('+' . rand(1, 4) . ' months');

        return [
            // 📛 Личные данные
            'first_name' => $faker->firstName(),
            'last_name'  => $faker->lastName(),
            'pers_code'  => $persCode,

            // 🌍 Страна и гражданство (числовые ID)
            'citizenship_id'      => $faker->numberBetween(1, 15),
            'declared_country_id' => $faker->numberBetween(1, 17),
            'declared_city_id'    => $faker->numberBetween(1, 13),
            'actual_country_id'   => $faker->numberBetween(1, 15),
            'actual_city_id'      => $faker->numberBetween(1, 13),

            // 🏠 Адреса
            'declared_street'   => $faker->streetName(),
            'declared_building' => $faker->buildingNumber(),
            'declared_room'     => $faker->numberBetween(1, 100),
            'declared_postcode' => $faker->postcode(),
            'actual_street'     => $faker->streetName(),
            'actual_building'   => $faker->buildingNumber(),
            'actual_room'       => $faker->numberBetween(1, 100),

            // 📞 Контакты
            'phone' => $faker->phoneNumber(),
            'email' => $faker->unique()->safeEmail(),

            // 🚗 Водительские данные
            'license_number' => $faker->unique()->bothify('LV#######'),
            'license_issued' => $faker->dateTimeBetween('-10 years', '-5 years'),
            'license_end'    => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),

            'code95_issued'  => $faker->date(),
            'code95_end'     => $faker->date(),
            'permit_issued'  => $faker->optional()->date(),
            'permit_expired' => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),

            // 🩺 Медсправки
            'medical_issued'  => $faker->dateTimeBetween('-3 years', '-1 years'),
            'medical_expired' => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'declaration_issued'  => $faker->date(),
            'declaration_expired' => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),

            // 📸 Фото и документы
            'photo'                      => $faker->imageUrl(640, 480, 'people'),
            'license_photo'              => $faker->imageUrl(640, 480, 'documents'),
            'medical_certificate_photo'  => $faker->imageUrl(640, 480, 'documents'),
            'medical_exam_passed'        => $passed->format('Y-m-d'),
            'medical_exam_expired'       => $expired->format('Y-m-d'),

            // ⚙️ Прочее
            'status'    => 1,
            'is_active' => true,

            // 🔗 Привязка к компании-экспедитору
            'company' => $faker->numberBetween(1, 2),
        ];
    }
}
