<?php

namespace App\Livewire\Trucks;

use App\Models\Trip;
use App\Models\Truck;
use App\Models\TruckOdometerEvent;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Services\Services\MaponService;

class ShowTruck extends Component
{
    public Truck $truck;

    /** Пробег за период: даты фильтра по выезду из гаража — заезду в гараж (по умолчанию последние 30 дней). */
    public ?string $mileagePeriodFrom = null;
    public ?string $mileagePeriodTo = null;

    /** Пагинация таблицы рейсов в блоке пробега. */
    public int $mileageTripsPage = 1;
    public int $mileageTripsPerPage = 15;

    // Mapon UI fields (CAN only)
    public ?float $maponCanMileageKm = null;     // CAN odometer (km)
    public ?string $maponUnitName = null;
    public ?string $maponError = null;

    // Stale status (configured)
    public bool $maponCanStale = false;
    public ?int $maponCanDaysAgo = null;

    // Meta
    public ?string $maponLastUpdate = null;      // unit.last_update
    public ?string $maponCanAt = null;           // can.odom.gmt

    // Position (for map)
    public ?float $maponLat = null;
    public ?float $maponLng = null;
    /** State: 'standing' | 'moving' */
    public ?string $maponStateName = null;
    /** Speed km/h (when moving) */
    public ?float $maponSpeed = null;
    /** Tooltip text for map marker (state + speed) */
    public string $maponMarkerTooltip = '';

    public function mount(Truck $truck): void
    {
        $this->truck = $truck;
        $this->loadMaponData();
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
        $this->resetPageMileageTrips();
    }

    public function updatedMileagePeriodTo(): void
    {
        $this->resetPageMileageTrips();
    }

    public function resetPageMileageTrips(): void
    {
        $this->mileageTripsPage = 1;
    }

    public function setMileageTripsPage(int $page): void
    {
        $this->mileageTripsPage = max(1, $page);
    }

    /**
     * Button handler: clear cache and load fresh data
     */
    public function refreshMaponData(): void
    {
        $unitId = $this->truck->mapon_unit_id ?? null;

        if ($unitId) {
            Cache::forget($this->cacheKey($unitId));
        }

        $this->loadMaponData();
    }

public function loadMaponData(): void
{
    // reset so nothing "sticks"
    $this->maponError = null;
    $this->maponCanMileageKm = null;
    $this->maponUnitName = null;
    $this->maponLastUpdate = null;
    $this->maponCanAt = null;
    $this->maponLat = null;
    $this->maponLng = null;
    $this->maponStateName = null;
    $this->maponSpeed = null;
    $this->maponMarkerTooltip = '';

    $this->maponCanStale = false;
    $this->maponCanDaysAgo = null;

    $unitId = $this->truck->mapon_unit_id ?? null;

    if (!$unitId) {
        $this->maponError = 'mapon_unit_id не задан для данного трака.';
        return;
    }

    // cache учитывает компанию (ключ Mapon разный)
    $companyId = (int) ($this->truck->company_id ?? 0);
    $cacheKey = $this->cacheKey($unitId) . ':company:' . $companyId;

    $result = Cache::remember($cacheKey, now()->addMinutes(5), function () {
        try {
            /** @var MaponService $svc */
            $svc = app(MaponService::class);

            return $svc->getUnitDataForTruck($this->truck, 'can');
        } catch (\Throwable $e) {
            $unitId = $this->truck->mapon_unit_id ?? 'null';
            \Log::warning("MaponService getUnitDataForTruck failed unit_id={$unitId}: " . $e->getMessage());
            return null;
        }
    });

    if (!is_array($result)) {
        $this->maponError = 'Не удалось получить данные из Mapon.';
        return;
    }

    $this->maponUnitName = $result['label']
        ?? $result['number']
        ?? ($result['vehicle_title'] ?? null)
        ?? '—';

    $this->maponLastUpdate = $result['last_update'] ?? null;

    if (isset($result['lat']) && isset($result['lng'])) {
        $this->maponLat = (float) $result['lat'];
        $this->maponLng = (float) $result['lng'];
    }

    $rawState = (string) ($result['state']['name'] ?? $result['movement_state']['name'] ?? '');
    $this->maponSpeed = isset($result['speed']) && $result['speed'] !== null ? (float) $result['speed'] : null;
    // Движение: явный статус "moving"/"driving" или скорость > 0 (как на карте)
    $this->maponStateName = in_array(strtolower($rawState), ['moving', 'driving'], true)
        || ($this->maponSpeed !== null && $this->maponSpeed > 0)
        ? 'moving'
        : 'standing';

    if ($this->maponStateName === 'moving' && $this->maponSpeed !== null) {
        $this->maponMarkerTooltip = __('app.truck.show.mapon_moving') . ', ' . (int) round($this->maponSpeed) . ' ' . __('app.truck.show.mapon_kmh');
    } elseif ($this->maponStateName === 'moving') {
        $this->maponMarkerTooltip = __('app.truck.show.mapon_moving');
    } else {
        $this->maponMarkerTooltip = __('app.truck.show.mapon_standing');
    }

    // --------------------------------------
    // ODOMETER: CAN → fallback mileage
    // --------------------------------------
    $canValue = data_get($result, 'can.odom.value');
    $canAt    = data_get($result, 'can.odom.gmt');

    if ($canValue !== null && $canValue !== '') {
        // ✅ CAN odometer (км)
        $this->maponCanMileageKm = round((float) $canValue, 1);

        // timestamp CAN, если нет — берём last_update
        $this->maponCanAt = !empty($canAt)
            ? (string) $canAt
            : ($this->maponLastUpdate ? (string) $this->maponLastUpdate : null);

    } else {
        // ✅ CAN нет (Lakna) → используем mileage
        $rawMileage = data_get($result, 'mileage');

        if ($rawMileage === null || $rawMileage === '') {
            // ❌ вот это уже реальная ошибка
            $this->maponError = 'Mapon не вернул ни CAN odometer, ни mileage.';
            return;
        }

        // mileage обычно в метрах → конвертим в км
        $this->maponCanMileageKm = round(((float) $rawMileage) / 1000, 1);

        // timestamp берём last_update
        $this->maponCanAt = $this->maponLastUpdate ? (string) $this->maponLastUpdate : null;
    }

    // --------------------------------------
    // stale logic via config/mapon.php
    // --------------------------------------
    if ($this->maponCanAt) {
        try {
            $now = now();
            $at  = \Carbon\Carbon::parse($this->maponCanAt);

            $this->maponCanDaysAgo = $at->diffInDays($now);

            $thresholdDays    = (int) config('mapon.can_stale_days', 2);
            $thresholdMinutes = (int) config('mapon.can_stale_minutes', 30);

            $isStaleByDays = $thresholdDays > 0 && $at->diffInDays($now) >= $thresholdDays;
            $isStaleByMin  = $thresholdMinutes > 0 && $at->diffInMinutes($now) >= $thresholdMinutes;

            $this->maponCanStale = $isStaleByDays || $isStaleByMin;
        } catch (\Throwable $e) {
            \Log::warning("Mapon time parse failed: {$this->maponCanAt}. " . $e->getMessage());
        }
    }
}




    protected function cacheKey(int|string $unitId): string
    {
        return "mapon:unit:{$unitId}:data:can";
    }

    /**
     * Пробег по тягачу за период: от выезда из гаража до заезда в гараж (одометр и даты по событиям).
     * Фильтр по датам: выезд из гаража ≥ date_from, заезд в гараж ≤ date_to.
     */
    public function getTruckMileageStatsProperty(): array
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
            ->where('truck_id', $this->truck->id)
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

    public function destroy()
    {
        if ($this->truck) {
            $this->truck->delete();
            session()->flash('success', __('app.truck.show.deleted_success'));
            return redirect()->route('trucks.index');
        }

        session()->flash('error', __('app.truck.show.deleted_error'));
        return redirect()->route('trucks.index');
    }

    public function render()
    {
        $mileageStats = $this->truckMileageStats;
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

        return view('livewire.trucks.show-truck', compact('mileageStats', 'mileageTripsPaginator'))
            ->layout('layouts.app', [
                'title' => __('app.truck.show.title'),
            ]);
    }
}
