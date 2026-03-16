<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Livewire\Trips\ViewTrip;
use Illuminate\Console\Command;

class SyncTripCargosFromOrders extends Command
{
    protected $signature = 'trips:sync-cargos-from-orders
                            {--trip= : Only sync this trip ID}';

    protected $description = 'Sync TripCargos from linked TransportOrders for trips where orders > cargos (run once after deploy if needed)';

    public function handle(): int
    {
        $tripId = $this->option('trip');

        $query = Trip::whereHas('transportOrders');
        if ($tripId !== null && $tripId !== '') {
            $query->where('id', (int) $tripId);
        }
        $trips = $query->get();

        $this->info('Trips with linked orders: ' . $trips->count());

        $synced = 0;
        foreach ($trips as $trip) {
            $component = new ViewTrip();
            $component->mount($trip);
            $synced++;
            $this->line("  Trip #{$trip->id}: synced.");
        }

        $this->info("Done. Processed {$synced} trip(s).");
        return self::SUCCESS;
    }
}
