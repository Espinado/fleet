<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Driver;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
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

        $n = 0;
        foreach ($carrierCompanies as $companyId) {
            for ($i = 0; $i < 4; $i++) {
                $n++;
                $driver = Driver::factory()->create(['company_id' => $companyId]);

                $user = User::updateOrCreate(
                    ['email' => 'driver' . $driver->id . '@fleet.test'],
                    [
                        'name'     => $driver->first_name . ' ' . $driver->last_name,
                        'password' => Hash::make('driver123'),
                        'role'     => 'driver',
                    ]
                );

                $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $driver->update([
                    'user_id'   => $user->id,
                    'login_pin' => $pin,
                    'license_number' => $driver->license_number ?? 'LV' . $driver->id . rand(10000, 99999),
                    'license_issued' => $driver->license_issued ?? '2020-01-01',
                    'license_end'    => $driver->license_end ?? '2030-01-01',
                ]);

                $this->command->info("Driver #{$driver->id} {$driver->first_name} {$driver->last_name} — PIN: {$pin}");
            }
        }
        $this->command->info("Created {$n} drivers.");
    }
}
