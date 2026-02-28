<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $items = config('companies', []);

        foreach ($items as $slug => $data) {
            Company::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'      => $data['name'] ?? $slug,
                    'type'      => $data['type'] ?? 'carrier',
                    'reg_nr'    => $data['reg_nr'] ?? null,
                    'vat_nr'    =>'LV'.$data['reg_nr'] ?? null,

                    'country'   => $data['country'] ?? null,
                    'city'      => $data['city'] ?? null,
                    'address'   => $data['address'] ?? null,
                    'post_code' => $data['post_code'] ?? null,

                    'email'     => $data['email'] ?? null,
                    'phone'     => $data['phone'] ?? null,

                    'banks_json'=> !empty($data['bank']) ? json_encode($data['bank']) : null,

                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
