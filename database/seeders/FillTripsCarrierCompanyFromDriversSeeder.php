<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillTripsCarrierCompanyFromDriversSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            UPDATE trips t
            JOIN drivers d ON d.id = t.driver_id
            SET t.carrier_company_id = d.company_id
            WHERE t.carrier_company_id IS NULL
              AND t.driver_id IS NOT NULL
              AND d.company_id IS NOT NULL
        ");
    }
}
