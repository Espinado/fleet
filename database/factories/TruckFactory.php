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

        $licenseIssued = $faker->dateTimeBetween('-3 years', '-1 year');
        $licenseExpired = (clone $licenseIssued)->modify('+2 years');

        return [
            // 🚚 Основные данные
            'brand' => $faker->randomElement(['Volvo', 'Scania', 'MAN', 'Mercedes', 'DAF', 'Iveco', 'Renault']),
            'model' => ucfirst($faker->word()),
            'plate' => strtoupper($faker->bothify('??####')),
            'year'  => $faker->year(),

            // 🪪 Лицензия тягача (тахо/допуск)
            'license_number' => 'TRK-' . $faker->unique()->numerify('######'),
            'license_issued' => $licenseIssued->format('Y-m-d'),
            'license_expired' => $licenseExpired->format('Y-m-d'),

            // 📍 Mapon (опционально)
            'mapon_box_id'  => $faker->optional(0.6)->numerify('box-#####'),
            'mapon_unit_id' => $faker->optional(0.6)->numerify('unit-#####'),
            'can_available' => $faker->boolean(0.7),

            // 🔧 Техосмотр
            'inspection_issued' => $inspectionIssued->format('Y-m-d'),
            'inspection_expired' => $inspectionExpired->format('Y-m-d'),

            // 🧾 Страховка
            'insurance_number'   => $faker->bothify('TRK-INS-#####'),
            'insurance_issued'   => $insuranceIssued->format('Y-m-d'),
            'insurance_expired'  => $insuranceExpired->format('Y-m-d'),
            'insurance_company'  => $faker->company(),

            // 🪪 VIN и техпаспорт + фото
            'vin' => strtoupper($faker->unique()->bothify('#################')),
            'tech_passport_nr' => 'TP-' . $faker->numerify('#####'),
            'tech_passport_issued' => $faker->dateTimeBetween('-5 years', '-2 years')->format('Y-m-d'),
            'tech_passport_expired' => $faker->dateTimeBetween('+1 years', '+3 years')->format('Y-m-d'),
            'tech_passport_photo' => 'https://placehold.co/600x400?text=Truck+Tech+Passport',

            // ⚙️ Статус и принадлежность
            'status'     => 1,
            'is_active'  => true,
            'company_id' => $faker->numberBetween(1, 2),
        ];
    }
}
