<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    public function definition(): array
    {
        $faker = $this->faker;

        // === 🌍 Разрешённые страны (из config/countries.php)
        $countryPool = [16, 17, 13, 21, 8]; // LV, LT, HU, EE, DE

        $declaredCountryId = Arr::random($countryPool);
        $actualCountryId   = Arr::random($countryPool);
        $citizenshipId     = Arr::random($countryPool);

        // === Определяем ISO-коды стран
        $declaredIso = config("countries.$declaredCountryId.iso") ?? 'lv';
        $actualIso   = config("countries.$actualCountryId.iso") ?? 'lv';

        // === Получаем города из config/cities/{iso}.php
        $declaredCities = config("cities.$declaredIso") ?? [];
        $actualCities   = config("cities.$actualIso") ?? [];

        // === Fallback если городов нет
        if (empty($declaredCities)) {
            $declaredCities = [
                1 => ['name' => 'Rīga'],
                2 => ['name' => 'Liepāja'],
                3 => ['name' => 'Daugavpils'],
            ];
        }
        if (empty($actualCities)) {
            $actualCities = [
                1 => ['name' => 'Rīga'],
                2 => ['name' => 'Liepāja'],
                3 => ['name' => 'Daugavpils'],
            ];
        }

        $declaredCityId = array_rand($declaredCities);
        $actualCityId   = array_rand($actualCities);

        // === 👤 Персональные данные
        $birthDate = $faker->dateTimeBetween('1970-01-01', '2000-12-31');
        $persCode  = $birthDate->format('ymd') . '-' . $faker->numerify('#####');

        // === 📸 Фото (сидируем все поля фотографий)
        $photoPerson  = 'https://placehold.co/400x500?text=Driver+Photo';
        $photoLicense = 'https://placehold.co/600x400?text=License';
        $photoMedical = 'https://placehold.co/600x400?text=Medical+Certificate';

        // === 🩺 Медосмотр
        $passed  = $faker->dateTimeBetween('-1 month', 'yesterday');
        $expired = (clone $passed)->modify('+' . rand(1, 4) . ' months');

        // === 🪪 Разрешения и документы
        $permitIssuedDate = $faker->optional()->dateTimeBetween('-2 years', '-1 years');
        $permitIssued     = $permitIssuedDate ? $permitIssuedDate->format('Y-m-d') : null;

        return [
            // 📛 Личные данные
            'first_name' => $faker->firstName(),
            'last_name'  => $faker->lastName(),
            'pers_code'  => $persCode,

            // 🌍 Страна и гражданство
            'citizenship_id'      => $citizenshipId,
            'declared_country_id' => $declaredCountryId,
            'declared_city_id'    => $declaredCityId,
            'actual_country_id'   => $actualCountryId,
            'actual_city_id'      => $actualCityId,

            // 🏠 Адреса
            'declared_street'   => $faker->streetName(),
            'declared_building' => $faker->buildingNumber(),
            'declared_room'     => (string) $faker->numberBetween(1, 100),
            'declared_postcode' => $faker->postcode(),
            'actual_street'     => $faker->streetName(),
            'actual_building'   => $faker->buildingNumber(),
            'actual_room'       => (string) $faker->numberBetween(1, 100),
            'actual_postcode'   => $faker->postcode(),

            // 📞 Контакты
            'phone' => $faker->e164PhoneNumber(),
            'email' => $faker->unique()->safeEmail(),

            // 🚗 Водительские данные
            'license_number' => $faker->unique()->bothify('LV#######'),
            'license_issued' => $faker->dateTimeBetween('-10 years', '-5 years')->format('Y-m-d'),
            'license_end'    => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),

            'code95_issued'  => $faker->dateTimeBetween('-5 years', '-2 years')->format('Y-m-d'),
            'code95_end'     => $faker->dateTimeBetween('+1 months', '+6 months')->format('Y-m-d'),
            'permit_issued'  => $permitIssued,
            'permit_expired' => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),

            // 🩺 Медсправки
            'medical_issued'      => $faker->dateTimeBetween('-3 years', '-1 years')->format('Y-m-d'),
            'medical_expired'     => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),
            'declaration_issued'  => $faker->dateTimeBetween('-1 years', '-6 months')->format('Y-m-d'),
            'declaration_expired' => $faker->dateTimeBetween('+15 days', '+6 months')->format('Y-m-d'),

            // 📸 Фото
            'photo'                     => $photoPerson,
            'license_photo'             => $photoLicense,
            'medical_certificate_photo' => $photoMedical,
            'medical_exam_passed'       => $passed->format('Y-m-d'),
            'medical_exam_expired'      => $expired->format('Y-m-d'),

            // ⚙️ Статус
            'status'    => 1,
            'is_active' => true,

            // 🔗 Привязка к компании
            'company_id' => $faker->numberBetween(1, 2),
        ];
    }
}
