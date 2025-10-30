<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use Faker\Factory as Faker;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_US');

        // 🔧 Отключаем проверки внешних ключей (иначе truncate упадёт)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Client::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Страны и города (id из config/countries.php и файлов cities/*.php)
        $countryIds = [8, 16, 17, 33, 39]; // Estonia, Latvia, Lithuania, Poland, Germany
        $cityIds    = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        for ($i = 1; $i <= 40; $i++) {
            $countryId = $faker->randomElement($countryIds);
            $cityId    = $faker->randomElement($cityIds);

            $companyName = match ($countryId) {
                16 => $faker->company . ' SIA', // Latvia
                17 => $faker->company . ' UAB', // Lithuania
                8  => $faker->company . ' OÜ',  // Estonia
                33 => $faker->company . ' Sp. z o.o.', // Poland
                39 => $faker->company . ' GmbH', // Germany
                default => $faker->company,
            };

            Client::create([
                'company_name'   => $companyName,
                'reg_nr'         => strtoupper($faker->bothify('LV########')),

                // Юр. адрес (id страны/города)
                'jur_country_id' => $countryId,
                'jur_city_id'    => $cityId,
                'jur_address'    => $faker->streetAddress,
                'jur_post_code'  => $faker->postcode,

                // Фактический адрес (id страны/города)
                'fiz_country_id' => $countryId,
                'fiz_city_id'    => $cityId,
                'fiz_address'    => $faker->streetAddress,
                'fiz_post_code'  => $faker->postcode,

                'bank_name'      => $faker->randomElement(['Swedbank', 'SEB', 'Luminor', 'Revolut', 'Citadele', 'Handelsbank']),
                'swift'          => strtoupper($faker->bothify('ABCDEFG#')),
                'email'          => $faker->unique()->companyEmail,
                'phone'          => '+371 ' . $faker->numberBetween(20000000, 29999999),
                'representative' => $faker->name,
            ]);
        }
    }
}
