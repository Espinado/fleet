<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\User;

class SyncDriverUsersCompanySeeder extends Seeder
{
    public function run(): void
    {
        $drivers = Driver::query()
            ->whereNotNull('user_id')
            ->whereNotNull('company_id')
            ->get(['user_id', 'company_id']);

        foreach ($drivers as $driver) {
            User::query()
                ->where('id', $driver->user_id)
                ->where('role', 'driver')
                ->where('email', '!=', 'sovsem@deneg.net')
                ->update(['company_id' => $driver->company_id]);
        }
    }
}
