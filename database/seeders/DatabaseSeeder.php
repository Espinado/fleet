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
            TripsWithCargosSeeder::class,
            AdminUserSeeder::class,
            TripsWithCargosSeeder::class,
        ]);
    }
}
