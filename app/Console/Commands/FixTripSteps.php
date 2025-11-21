<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Trip;
use App\Models\TripStep;

class FixTripSteps extends Command
{
    protected $signature = 'fix:trip-steps';
    protected $description = 'Rebuild trip_steps for old trips';

    public function handle()
    {
        $trips = Trip::with('cargos')->get();

        foreach ($trips as $trip) {
            foreach ($trip->cargos as $cargo) {

                // Убедимся что погрузка есть
                TripStep::updateOrCreate(
                    [
                        'trip_id'       => $trip->id,
                        'trip_cargo_id' => $cargo->id,
                        'type'          => 'loading',
                    ],
                    [
                        'country_id' => $cargo->loading_country_id,
                        'city_id'    => $cargo->loading_city_id,
                        'address'    => $cargo->loading_address,
                        'date'       => $cargo->loading_date,
                    ]
                );

                // И разгрузка
                TripStep::updateOrCreate(
                    [
                        'trip_id'       => $trip->id,
                        'trip_cargo_id' => $cargo->id,
                        'type'          => 'unloading',
                    ],
                    [
                        'country_id' => $cargo->unloading_country_id,
                        'city_id'    => $cargo->unloading_city_id,
                        'address'    => $cargo->unloading_address,
                        'date'       => $cargo->unloading_date,
                    ]
                );
            }
        }

        $this->info('✔ All trip steps fixed.');
    }
}
