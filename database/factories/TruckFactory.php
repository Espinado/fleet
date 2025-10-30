<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Truck>
 */
class TruckFactory extends Factory
{
    public function definition(): array
    {
        $faker = $this->faker;

        $inspectionIssued = $faker->dateTimeBetween('-2 years', '-1 year');
        $inspectionExpired = (clone $inspectionIssued)->modify('+1 year');

        $insuranceIssued = $faker->dateTimeBetween('-1 year', 'now');
        $insuranceExpired = (clone $insuranceIssued)->modify('+1 year');

        return [
            // 🚚 Основные данные
            'brand' => $faker->randomElement(['Volvo', 'Scania', 'MAN', 'Mercedes', 'DAF', 'Iveco', 'Renault']),
            'model' => $faker->word(),
            'plate' => strtoupper($faker->bothify('??####')),
            'year'  => $faker->year(),

            // 🔧 Техосмотр
            'inspection_issued' => $inspectionIssued->format('Y-m-d'),
            'inspection_expired' => $inspectionExpired->format('Y-m-d'),

            // 🧾 Страховка
            'insurance_number'   => $faker->bothify('TRK-INS-#####'),
            'insurance_issued'   => $insuranceIssued->format('Y-m-d'),
            'insurance_expired'  => $insuranceExpired->format('Y-m-d'),
            'insurance_company'  => $faker->company(),

            // 🪪 VIN и техпаспорт
            'vin' => strtoupper($faker->unique()->bothify('#################')),
            'tech_passport_nr' => 'TP-' . $faker->numerify('#####'),
            'tech_passport_issued' => $faker->dateTimeBetween('-5 years', '-2 years')->format('Y-m-d'),
            'tech_passport_expired' => $faker->dateTimeBetween('+1 years', '+3 years')->format('Y-m-d'),
            'tech_passport_photo' => $faker->imageUrl(640, 480, 'documents'),

            // ⚙️ Статус и принадлежность
            'status'    => 1,
            'is_active' => true,
            'company'   => $faker->numberBetween(1, 2),
        ];
    }
}
