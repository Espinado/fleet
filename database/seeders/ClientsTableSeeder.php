<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Illuminate\Support\Str;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'company_name' => 'Alpha Logistics SIA',
                'reg_nr'       => 'LV40003000111',
                'jur_country_id' => 16, // Latvia
                'jur_city_id'    => 1,
                'jur_address'    => 'Brīvības iela 120',
                'jur_post_code'  => 'LV-1010',
                'email'          => 'info@alpha.lv',
                'phone'          => '+371 20000001',
            ],
            [
                'company_name' => 'Beta Cargo OÜ',
                'reg_nr'       => 'EE10223344',
                'jur_country_id' => 17, // Lithuania
                'jur_city_id'    => 2,
                'jur_address'    => 'Gedimino pr. 10',
                'jur_post_code'  => 'LT-01103',
                'email'          => 'info@beta.ee',
                'phone'          => '+372 5000002',
            ],
            [
                'company_name' => 'Gamma Transport UAB',
                'reg_nr'       => 'LT10998877',
                'jur_country_id' => 12, // Poland
                'jur_city_id'    => 3,
                'jur_address'    => 'Warszawska 24',
                'jur_post_code'  => '00-123',
                'email'          => 'info@gamma.lt',
                'phone'          => '+370 6000003',
            ],
            [
                'company_name' => 'Delta Freight Kft',
                'reg_nr'       => 'HU12345678',
                'jur_country_id' => 13, // Hungary
                'jur_city_id'    => 4,
                'jur_address'    => 'Andrássy út 45',
                'jur_post_code'  => '1061',
                'email'          => 'info@delta.hu',
                'phone'          => '+36 20000004',
            ],
            [
                'company_name' => 'Omega Spedition AS',
                'reg_nr'       => 'EE20001234',
                'jur_country_id' => 16,
                'jur_city_id'    => 5,
                'jur_address'    => 'Tartu mnt 100',
                'jur_post_code'  => '10112',
                'email'          => 'info@omega.ee',
                'phone'          => '+372 5555555',
            ],
            [
                'company_name' => 'Zeta Transport SIA',
                'reg_nr'       => 'LV4000778899',
                'jur_country_id' => 16,
                'jur_city_id'    => 6,
                'jur_address'    => 'Kārļa Ulmaņa gatve 75',
                'jur_post_code'  => 'LV-1045',
                'email'          => 'info@zeta.lv',
                'phone'          => '+371 20000006',
            ],
        ];

        foreach ($clients as $data) {
            Client::updateOrCreate(
                ['company_name' => $data['company_name']],
                $data
            );
        }

        $this->command->info('✅ Добавлено/обновлено 6 тестовых клиентов.');
    }
}
