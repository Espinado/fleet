<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // 🌍 Разрешённые страны (из config/countries.php)
        $allowedCountryIds = [16, 17, 13, 21, 8];

        // Для каждого клиента выбираем страну → iso → город
        for ($i = 1; $i <= 18; $i++) {
            $countryId = Arr::random($allowedCountryIds);
            $country   = config("countries.$countryId");
          $iso    = $country['iso'] ?? 'lv';
$cities = config("cities.$iso") ?? [];

// Если города не найдены — fallback в Ригу
if (empty($cities)) {
    $cities = [
        1 => ['name' => 'Rīga'],
        2 => ['name' => 'Liepāja'],
        3 => ['name' => 'Daugavpils'],
    ];
}

            // Выбираем случайный город ID и его название
            $cityId   = array_rand($cities);
            $cityName = $cities[$cityId]['name'] ?? 'Unknown City';

            // Генерируем данные компании (все поля заполняем)
            $companyName = $faker->company() . ' ' . Arr::random(['SIA', 'UAB', 'OÜ', 'AS', 'Kft', 'Sp. z o.o.']);
            $emailDomain = Str::slug(explode(' ', strtolower($companyName))[0]) . '.' . strtolower($iso);

            Client::updateOrCreate(
                ['company_name' => $companyName],
                [
                    'reg_nr'         => strtoupper($iso) . $faker->numerify('#########'),
                    'representative' => $faker->firstName() . ' ' . $faker->lastName(),
                    'jur_country_id' => $countryId,
                    'jur_city_id'    => $cityId,
                    'jur_address'    => $faker->streetAddress(),
                    'jur_post_code'  => $faker->postcode(),
                    'fiz_country_id' => $countryId,
                    'fiz_city_id'    => $cityId,
                    'fiz_address'    => $faker->streetAddress(),
                    'fiz_post_code'  => $faker->postcode(),
                    'bank_name'      => $faker->randomElement(['Swedbank', 'SEB', 'Luminor', 'Citadele', 'Revolut Bank']) . ' ' . $cityName,
                    'swift'          => strtoupper($faker->bothify('??????##')),
                    'email'          => "info@$emailDomain",
                    'phone'          => $faker->e164PhoneNumber(),
                ]
            );
        }

        $this->command->info('✅ Добавлено/обновлено 18 тестовых клиентов с реальными странами и городами.');
    }
}
