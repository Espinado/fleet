<?php

namespace App\Livewire\Trailers;

use App\Models\Trip;
use App\Models\Trailer;
use App\Models\TruckOdometerEvent;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class ShowTrailer extends Component
{
    public Trailer $trailer;

    /** Пробег за период: по датам выезда из гаража — заезда в гараж. */
    public ?string $mileagePeriodFrom = null;
    public ?string $mileagePeriodTo = null;

    public int $mileageTripsPage = 1;
    public int $mileageTripsPerPage = 15;

    public function mount(Trailer $trailer): void
    {
        $this->trailer = $trailer;
        if ($this->mileagePeriodFrom === null || $this->mileagePeriodTo === null) {
            $this->mileagePeriodTo = Carbon::now()->toDateString();
            $this->mileagePeriodFrom = Carbon::now()->subDays(30)->toDateString();
        }
    }

    public function setMileagePeriod(int $days): void
    {
        $this->mileagePeriodTo = Carbon::now()->toDateString();
        $this->mileagePeriodFrom = Carbon::now()->subDays($days)->toDateString();
        $this->mileageTripsPage = 1;
    }

    public function clearMileagePeriod(): void
    {
        $this->mileagePeriodFrom = null;
        $this->mileagePeriodTo = null;
        $this->mileageTripsPage = 1;
    }

    public function updatedMileagePeriodFrom(): void
    {
        $this->mileageTripsPage = 1;
    }

    public function updatedMileagePeriodTo(): void
    {
        $this->mileageTripsPage = 1;
    }

    public function setMileageTripsPage(int $page): void
    {
        $this->mileageTripsPage = max(1, $page);
    }

    /**
     * Пробег по прицепу за период: рейсы с этим прицепом, от выезда из гаража до заезда в гараж.
     */
    public function getTrailerMileageStatsProperty(): array
    {
        $from = $this->mileagePeriodFrom ? Carbon::parse($this->mileagePeriodFrom)->startOfDay() : null;
        $to = $this->mileagePeriodTo ? Carbon::parse($this->mileagePeriodTo)->endOfDay() : null;

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
            ->where('trailer_id', $this->trailer->id)
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
        $mileageStats = $this->trailerMileageStats;
        $trips = $mileageStats['trips'];
        $perPage = max(1, $this->mileageTripsPerPage);
        $currentPage = max(1, $this->mileageTripsPage);
        $total = count($trips);
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $currentPage = min($currentPage, $lastPage);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($trips, $offset, $perPage);
        $mileageTripsPaginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'mileage_page']
        );
        $mileageTripsPaginator->appends(request()->query());

        return view('livewire.trailers.show-trailer', compact('mileageStats', 'mileageTripsPaginator'))
            ->layout('layouts.app', [
                'title' => __('app.trailer.show.title'),
            ]);
    }

    public function destroy()
    {
        if ($this->trailer) {
            $this->trailer->delete();

            session()->flash('success', __('app.trailer.show.deleted_success'));
            return redirect()->route('trailers.index');
        }

        session()->flash('error', __('app.trailer.show.deleted_error'));
        return redirect()->route('trailers.index');
    }
}
