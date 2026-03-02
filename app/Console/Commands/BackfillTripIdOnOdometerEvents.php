<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\TruckOdometerEvent;
use Illuminate\Console\Command;

class BackfillTripIdOnOdometerEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example:
     *  php artisan odometer:backfill-trip-id
     */
    protected $signature = 'odometer:backfill-trip-id {--dry-run : Only show what would be updated}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill trip_id on TruckOdometerEvent using trip date range and driver/truck match.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . 'Backfilling trip_id on truck_odometer_events…');

        $query = TruckOdometerEvent::query()
            ->whereNull('trip_id')
            ->whereNotNull('driver_id')
            ->whereNotNull('truck_id')
            ->orderBy('occurred_at');

        $total = $query->count();

        if ($total === 0) {
            $this->info('Nothing to backfill. All events already have trip_id or missing driver/truck.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} events without trip_id.");

        $updated = 0;
        $skippedNoTrip = 0;
        $skippedAmbiguous = 0;

        $query->chunkById(500, function ($events) use (&$updated, &$skippedNoTrip, &$skippedAmbiguous, $dryRun) {
            /** @var TruckOdometerEvent $event */
            foreach ($events as $event) {
                if (!$event->occurred_at) {
                    $skippedNoTrip++;
                    continue;
                }

                // Ищем рейсы этого водителя и грузовика, в диапазон дат которых попадает событие
                $candidateTrips = Trip::withoutGlobalScopes()
                    ->where('driver_id', $event->driver_id)
                    ->where('truck_id', $event->truck_id)
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->whereDate('start_date', '<=', $event->occurred_at->toDateString())
                    ->whereDate('end_date', '>=', $event->occurred_at->toDateString())
                    ->orderBy('start_date')
                    ->get();

                if ($candidateTrips->isEmpty()) {
                    $skippedNoTrip++;
                    continue;
                }

                if ($candidateTrips->count() > 1) {
                    // слишком неоднозначно — лучше промолчать
                    $skippedAmbiguous++;
                    continue;
                }

                $trip = $candidateTrips->first();

                if (!$trip) {
                    $skippedNoTrip++;
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf(
                        '[DRY-RUN] Would link event #%d (%s %s km) to trip #%d (%s → %s)',
                        $event->id,
                        $event->occurred_at,
                        $event->odometer_km ?? '—',
                        $trip->id,
                        $trip->start_date?->toDateString() ?? 'null',
                        $trip->end_date?->toDateString() ?? 'null'
                    ));
                } else {
                    $event->trip_id = $trip->id;
                    $event->save();
                }

                $updated++;
            }
        });

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Linked {$updated} events to trips.");
        $this->info("Skipped (no matching trip): {$skippedNoTrip}");
        $this->info("Skipped (ambiguous matches): {$skippedAmbiguous}");

        return self::SUCCESS;
    }
}

