<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trailer;
use App\Models\Company;

class TrailerSeeder extends Seeder
{
    public function run(): void
    {
        $carrierCompanies = Company::where('type', 'carrier')
            ->where(function ($q) {
                $q->where('is_third_party', false)->orWhereNull('is_third_party');
            })
            ->pluck('id')->toArray();
        if (empty($carrierCompanies)) {
            $carrierCompanies = Company::where('type', 'carrier')->pluck('id')->toArray();
        }

        $count = 0;
        foreach ($carrierCompanies as $companyId) {
            Trailer::factory()->count(4)->create(['company_id' => $companyId]);
            $count += 4;
        }
        $this->command->info("Created {$count} trailers.");
    }
}
