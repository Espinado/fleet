<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Trip;
use App\Models\TripStep;

class SortTripSteps extends Command
{
    protected $signature = 'trip:sort-steps';
    protected $description = 'Sort trip steps automatically by date + type';

    public function handle()
    {
        $trips = Trip::with('steps')->get();

        foreach ($trips as $trip) {

            $steps = TripStep::where('trip_id', $trip->id)
                ->get()
                ->sort(function($a, $b) {
                    // Сортируем по дате
                    $cmp = strtotime($a->date) <=> strtotime($b->date);
                    if ($cmp !== 0) return $cmp;

                    // Одинаковая дата → loading первее unloading
                    return $a->type === 'loading' ? -1 : 1;
                })
                ->values();

            foreach ($steps as $i => $s) {
                $s->update(['order' => $i + 1]);
            }

            $this->info("✔ Trip {$trip->id} sorted");
        }

        $this->info("🚀 All trips sorted successfully!");
    }
}
