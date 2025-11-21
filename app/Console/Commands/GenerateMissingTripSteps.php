<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Trip;
use App\Models\TripStep;

class GenerateMissingTripSteps extends Command
{
    protected $signature = 'trips:generate-steps';
    protected $description = 'Generate TripSteps for all existing trips based on cargos';

    public function handle()
    {
        $trips = Trip::with('cargos')->get();

        foreach ($trips as $trip) {

            foreach ($trip->cargos as $cargo) {

                // Пропускаем, если шаги уже созданы
                $existing = TripStep::where('trip_id', $trip->id)
                                    ->where('trip_cargo_id', $cargo->id)
                                    ->exists();

                if ($existing) continue;

                // CREATE LOADING STEP
                TripStep::create([
                    'trip_id'        => $trip->id,
                    'trip_cargo_id'  => $cargo->id,
                    'type'           => 'loading',
                    'country'        => $cargo->loadingCountry->name ?? null,
                    'city'           => $cargo->loadingCity->name ?? null,
                    'address'        => $cargo->loading_address,
                    'date'           => $cargo->loading_date,
                    'order'          => 0,
                ]);

                // CREATE UNLOADING STEP
                TripStep::create([
                    'trip_id'        => $trip->id,
                    'trip_cargo_id'  => $cargo->id,
                    'type'           => 'unloading',
                    'country'        => $cargo->unloadingCountry->name ?? null,
                    'city'           => $cargo->unloadingCity->name ?? null,
                    'address'        => $cargo->unloading_address,
                    'date'           => $cargo->unloading_date,
                    'order'          => 0,
                ]);
            }

            $this->info("Steps regenerated for trip {$trip->id}");
        }

        return Command::SUCCESS;
    }
}
