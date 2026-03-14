<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    /**
     * Наши компании (без конфига): перевозчики и экспедитор.
     */
    public function run(): void
    {
        $ourCompanies = [
            [
                'slug'   => 'carrier-1',
                'name'   => 'SIA Mūsu Trans',
                'type'   => 'carrier',
                'reg_nr' => '40003123456',
                'country'=> 'Latvia',
                'city'   => 'Riga',
                'address'=> 'Brīvības iela 1',
                'post_code' => 'LV-1010',
                'email'  => 'info@musu-trans.lv',
                'phone'  => '+371 20000001',
                'banks_json' => [
                    1 => ['name' => 'Swedbank', 'iban' => 'LV64HABA0551017151860', 'bic' => 'HABALV22'],
                ],
            ],
            [
                'slug'   => 'carrier-2',
                'name'   => 'SIA Krava Logistika',
                'type'   => 'carrier',
                'reg_nr' => '40003999888',
                'country'=> 'Latvia',
                'city'   => 'Riga',
                'address'=> 'Dzirnavu iela 10',
                'post_code' => 'LV-1012',
                'email'  => 'info@krava-log.lv',
                'phone'  => '+371 20000002',
                'banks_json' => [
                    1 => ['name' => 'Citadele', 'iban' => 'LV64PARX0000001234567', 'bic' => 'PARXLV22'],
                ],
            ],
            [
                'slug'   => 'expeditor-1',
                'name'   => 'SIA Ekspedītors',
                'type'   => 'expeditor',
                'reg_nr' => '40203451221',
                'country'=> 'Latvia',
                'city'   => 'Riga',
                'address'=> 'Valerijas Seiles iela 7A',
                'post_code' => 'LV-1019',
                'email'  => 'info@ekspeditors.lv',
                'phone'  => '+371 20000003',
                'banks_json' => [
                    1 => ['name' => 'Swedbank', 'iban' => 'LV40HABA0551045751481', 'bic' => 'HABALV22'],
                ],
            ],
        ];

        foreach ($ourCompanies as $data) {
            Company::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'       => $data['name'],
                    'type'       => $data['type'],
                    'reg_nr'     => $data['reg_nr'] ?? null,
                    'vat_nr'     => isset($data['reg_nr']) ? 'LV' . $data['reg_nr'] : null,
                    'country'    => $data['country'] ?? null,
                    'city'       => $data['city'] ?? null,
                    'address'    => $data['address'] ?? null,
                    'post_code'  => $data['post_code'] ?? null,
                    'email'      => $data['email'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                    'banks_json' => isset($data['banks_json']) ? $data['banks_json'] : null,
                    'is_system'  => false,
                    'is_active'  => true,
                ]
            );
        }

        $this->command->info('Created ' . count($ourCompanies) . ' companies (2 carriers, 1 expeditor).');
    }
}
