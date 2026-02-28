<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Trip;

class FillRemainingTripsCarrierCompanySeeder extends Seeder
{
    public function run(): void
    {
        $lakna = Company::where('slug', 'lakna')->first();
        if (!$lakna) return;

        Trip::whereNull('carrier_company_id')
            ->update(['carrier_company_id' => $lakna->id]);
    }
}
