<?php

namespace App\Livewire\Drivers;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\TruckOdometerEvent;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class ShowDriver extends Component
{
    public Driver $driver;

    /** Период рейсов: по датам выезда из гаража — заезда в гараж. */
    public ?string $tripsPeriodFrom = null;
    public ?string $tripsPeriodTo = null;

    public int $driverTripsPage = 1;
    public int $driverTripsPerPage = 15;

    public function mount(Driver $driver): void
    {
        $this->driver = $driver;
        if ($this->tripsPeriodFrom === null || $this->tripsPeriodTo === null) {
            $this->tripsPeriodTo = Carbon::now()->toDateString();
            $this->tripsPeriodFrom = Carbon::now()->subDays(30)->toDateString();
        }
    }

    public function setTripsPeriod(int $days): void
    {
        $this->tripsPeriodTo = Carbon::now()->toDateString();
        $this->tripsPeriodFrom = Carbon::now()->subDays($days)->toDateString();
        $this->driverTripsPage = 1;
    }

    public function clearTripsPeriod(): void
    {
        $this->tripsPeriodFrom = null;
        $this->tripsPeriodTo = null;
        $this->driverTripsPage = 1;
    }

    public function updatedTripsPeriodFrom(): void
    {
        $this->driverTripsPage = 1;
    }

    public function updatedTripsPeriodTo(): void
    {
        $this->driverTripsPage = 1;
    }

    public function setDriverTripsPage(int $page): void
    {
        $this->driverTripsPage = max(1, $page);
    }

    /**
     * Рейсы водителя за период: от выезда из гаража до заезда в гараж.
     */
    public function getDriverTripsStatsProperty(): array
    {
        $from = $this->tripsPeriodFrom ? Carbon::parse($this->tripsPeriodFrom)->startOfDay() : null;
        $to = $this->tripsPeriodTo ? Carbon::parse($this->tripsPeriodTo)->endOfDay() : null;

        $depSub = TruckOdometerEvent::query()
            ->selectRaw('COALESCE(NULLIF(odometer_km, 0), trips.odo_start_km)')
            ->whereColumn('trip_id', 'trips.id')
            ->where('type', TruckOdometerEvent::TYPE_DEPARTURE)
            ->orderBy('occurred_at', 'asc')
            ->limit(1);
        $retSub = TruckOdometerEvent::query()
            ->selectRaw('COALESCE(NULLIF(odometer_km, 0), trips.odo_end_km)')
            ->whereColumn('trip_id', 'trips.id')
            ->where('type', TruckOdometerEvent::TYPE_RETURN)
            ->orderBy('occurred_at', 'desc')
            ->limit(1);
        $depAtSub = TruckOdometerEvent::query()
            ->select('occurred_at')
            ->whereColumn('trip_id', 'trips.id')
            ->where('type', TruckOdometerEvent::TYPE_DEPARTURE)
            ->orderBy('occurred_at', 'asc')
            ->limit(1);
        $retAtSub = TruckOdometerEvent::query()
            ->select('occurred_at')
            ->whereColumn('trip_id', 'trips.id')
            ->where('type', TruckOdometerEvent::TYPE_RETURN)
            ->orderBy('occurred_at', 'desc')
            ->limit(1);

        $q = Trip::query()
            ->where('driver_id', $this->driver->id)
            ->select('trips.id', 'trips.start_date', 'trips.odo_start_km', 'trips.odo_end_km')
            ->addSelect([
                'departure_odometer' => $depSub,
                'return_odometer' => $retSub,
                'departure_occurred_at' => $depAtSub,
                'return_occurred_at' => $retAtSub,
            ]);

        if ($from) {
            $q->whereExists(function ($ex) use ($from) {
                $ex->selectRaw('1')
                    ->from('truck_odometer_events')
                    ->whereColumn('truck_odometer_events.trip_id', 'trips.id')
                    ->where('truck_odometer_events.type', TruckOdometerEvent::TYPE_DEPARTURE)
                    ->where('truck_odometer_events.occurred_at', '>=', $from);
            });
        }
        if ($to) {
            $q->whereExists(function ($ex) use ($to) {
                $ex->selectRaw('1')
                    ->from('truck_odometer_events')
                    ->whereColumn('truck_odometer_events.trip_id', 'trips.id')
                    ->where('truck_odometer_events.type', TruckOdometerEvent::TYPE_RETURN)
                    ->where('truck_odometer_events.occurred_at', '<=', $to);
            });
        }

        $rows = $q->orderBy('trips.start_date', 'desc')->get();

        $totalKm = 0;
        $trips = [];
        foreach ($rows as $t) {
            $dep = (float) ($t->departure_odometer ?? $t->odo_start_km ?? 0);
            $ret = (float) ($t->return_odometer ?? $t->odo_end_km ?? 0);
            $distanceKm = $ret > $dep ? round($ret - $dep, 1) : 0;
            $totalKm += $distanceKm;
            $depAt = $t->departure_occurred_at ?? null;
            $retAt = $t->return_occurred_at ?? null;
            $trips[] = [
                'id' => $t->id,
                'departure_date' => $depAt ? Carbon::parse($depAt)->format('Y-m-d') : '',
                'return_date' => $retAt ? Carbon::parse($retAt)->format('Y-m-d') : '',
                'distance_km' => $distanceKm,
            ];
        }

        return [
            'total_km' => round($totalKm, 1),
            'trips_count' => $rows->count(),
            'trips' => $trips,
        ];
    }

    public function render()
    {
        $stats = $this->driverTripsStats;
        $trips = $stats['trips'];
        $perPage = max(1, $this->driverTripsPerPage);
        $currentPage = max(1, $this->driverTripsPage);
        $total = count($trips);
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $currentPage = min($currentPage, $lastPage);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($trips, $offset, $perPage);
        $driverTripsPaginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'trips_page']
        );
        $driverTripsPaginator->appends(request()->query());

        return view('livewire.drivers.show-driver', [
            'driverTripsStats' => $stats,
            'driverTripsPaginator' => $driverTripsPaginator,
        ])->layout('layouts.app', [
            'title' => __('app.driver.show.title'),
        ]);
    }
}
