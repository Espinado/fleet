<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trailer>
 */
class TrailerFactory extends Factory
{
    public function definition(): array
    {
        $faker = $this->faker;

        $inspectionIssued = $faker->dateTimeBetween('-2 years', '-1 year');
        $inspectionExpired = (clone $inspectionIssued)->modify('+1 year');

        $insuranceIssued = $faker->dateTimeBetween('-1 year', 'now');
        $insuranceExpired = (clone $insuranceIssued)->modify('+1 year');

        $tirIssued = $faker->dateTimeBetween('-2 years', '-1 years');
        $tirExpired = (clone $tirIssued)->modify('+2 years');

        return [
            // 🚛 Основные данные
            'brand' => $faker->randomElement(['Krone', 'Schmitz', 'Kögel', 'Fliegl', 'Wielton']),
            'plate' => strtoupper($faker->bothify('??####')),
            'year'  => $faker->year(),
            'vin'   => strtoupper($faker->unique()->bothify('#################')),

            // 🪪 Техпаспорт
            'tech_passport_nr' => 'TP-' . $faker->numerify('#####'),
            'tech_passport_issued' => $faker->dateTimeBetween('-6 years', '-3 years')->format('Y-m-d'),
            'tech_passport_expired' => $faker->dateTimeBetween('+1 years', '+3 years')->format('Y-m-d'),
            'tech_passport_photo' => 'https://placehold.co/600x400?text=Tech+Passport',

            // 🔧 Техосмотр
            'inspection_issued' => $inspectionIssued->format('Y-m-d'),
            'inspection_expired' => $inspectionExpired->format('Y-m-d'),

            // 🧾 Страховка
            'insurance_number'   => $faker->bothify('TRL-INS-#####'),
            'insurance_company'  => $faker->company(),
            'insurance_issued'   => $insuranceIssued->format('Y-m-d'),
            'insurance_expired'  => $insuranceExpired->format('Y-m-d'),

            // 🚛 TIR (международный допуск)
            'tir_issued' => $tirIssued->format('Y-m-d'),
            'tir_expired' => $tirExpired->format('Y-m-d'),

            // ⚙️ Статус и принадлежность
            'status'    => 1,
            'is_active' => true,
            'company'   => $faker->numberBetween(1, 2),
        ];
    }
}
