<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === Очередность имеет значение ===
        $this->call([
            ClientsTableSeeder::class,
            TruckSeeder::class,
            TrailerSeeder::class,
            DriverSeeder::class,
            FleetExtraSeeder::class,
            CompaniesSeeder::class,
            AdminUserSeeder::class,
            TripsWithItemsSeeder::class,
        ]);
    }
}
