<?php

namespace Database\Seeders;

use App\Models\Trailer;
use App\Models\Truck;
use App\Models\VehicleMaintenance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = [
            'Tehniskā apskate',
            'Eļļas maiņa un filtri',
            'Bremžu disku maiņa',
            'Gumiju maiņa',
            'Apkopes darbi',
            'Remonts pēc nobraukuma',
            'Gaisa filtra maiņa',
            'Dīzeļa filtra maiņa',
        ];

        $trucks = Truck::query()->whereNotNull('company_id')->limit(15)->get();
        foreach ($trucks as $truck) {
            $count = rand(2, 5);
            $lastPerformed = Carbon::today()->subMonths(rand(3, 12));
            for ($i = 0; $i < $count; $i++) {
                $performedAt = $lastPerformed->copy()->subDays(rand(30, 120));
                $odometer = rand(50000, 450000) + ($i * rand(5000, 15000));
                $hasCost = (bool) rand(0, 1);
                VehicleMaintenance::create([
                    'company_id' => $truck->company_id,
                    'truck_id' => $truck->id,
                    'trailer_id' => null,
                    'performed_at' => $performedAt,
                    'odometer_km' => $odometer,
                    'description' => $descriptions[array_rand($descriptions)],
                    'cost' => $hasCost ? round(rand(50, 800) + (rand(0, 99) / 100), 2) : null,
                ]);
                $lastPerformed = $performedAt;
            }
            $nextService = Carbon::today()->addDays(rand(14, 90));
            $truck->update([
                'next_service_date' => $nextService,
                'next_service_km' => ($truck->next_service_km ?? rand(400000, 500000)) + rand(5000, 15000),
            ]);
        }

        $trailers = Trailer::query()->whereNotNull('company_id')->limit(10)->get();
        foreach ($trailers as $trailer) {
            $count = rand(1, 3);
            $lastPerformed = Carbon::today()->subMonths(rand(2, 8));
            for ($i = 0; $i < $count; $i++) {
                $performedAt = $lastPerformed->copy()->subDays(rand(60, 180));
                $hasCost = (bool) rand(0, 1);
                VehicleMaintenance::create([
                    'company_id' => $trailer->company_id,
                    'truck_id' => null,
                    'trailer_id' => $trailer->id,
                    'performed_at' => $performedAt,
                    'odometer_km' => rand(50000, 300000),
                    'description' => $descriptions[array_rand($descriptions)],
                    'cost' => $hasCost ? round(rand(30, 400) + (rand(0, 99) / 100), 2) : null,
                ]);
                $lastPerformed = $performedAt;
            }
            $trailer->update([
                'next_service_date' => Carbon::today()->addDays(rand(14, 120)),
            ]);
        }

        $this->command->info('Maintenance records and next_service dates created.');
    }
}
