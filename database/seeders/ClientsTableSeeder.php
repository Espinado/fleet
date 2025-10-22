<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Faker\Factory as Faker;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        // 🇪🇺 Европейские компании
        $faker = Faker::create('en_US');

        // страны для выбора
        $countries = [
            'Latvia', 'Lithuania', 'Estonia', 'Poland', 'Germany'
        ];

        // Очистим таблицу перед заполнением (чтобы не дублировать)
        Client::truncate();

        for ($i = 1; $i <= 40; $i++) {
            $country = $faker->randomElement($countries);
            $city = $faker->city;
            $companyName = match ($country) {
                'Latvia' => $faker->company . ' SIA',
                'Lithuania' => $faker->company . ' UAB',
                'Estonia' => $faker->company . ' OÜ',
                'Poland' => $faker->company . ' Sp. z o.o.',
                'Germany' => $faker->company . ' GmbH',
                default => $faker->company,
            };

            Client::create([
                'company_name'    => $companyName,
                'reg_nr'          => strtoupper($faker->bothify('LV########')),
                'jur_country'     => $country,
                'jur_city'        => $city,
                'jur_address'     => $faker->streetAddress,
                'jur_post_code'   => $faker->postcode,
                'fiz_country'     => $country,
                'fiz_city'        => $city,
                'fiz_address'     => $faker->streetAddress,
                'fiz_post_code'   => $faker->postcode,
                'bank_name'       => $faker->randomElement([
                    'Swedbank', 'SEB Bank', 'Luminor', 'Revolut', 'Citadele', 'Handelsbank'
                ]),
                'swift'           => strtoupper($faker->bothify('ABCDEFG#')),
                'email'           => $faker->unique()->companyEmail,
                'phone'           => '+371 ' . $faker->numberBetween(20000000, 29999999),
                'representative'  => $faker->name,
            ]);
        }
    }
}
