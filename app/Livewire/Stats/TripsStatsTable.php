<?php

namespace App\Livewire\Stats;

use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TripsStatsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public ?string $dateFrom = null;
    public ?string $dateTo   = null;

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'dateFrom'      => ['except' => null],
        'dateTo'        => ['except' => null],
        'page'          => ['except' => 1],
    ];

    public function mount(): void
    {
        // по умолчанию показываем всё
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function clearDates(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function quickRange(int $days): void
    {
        $this->dateFrom = Carbon::now()->subDays($days)->toDateString();
        $this->dateTo   = Carbon::now()->toDateString();
        $this->resetPage();
    }

    public function getActiveFiltersCountProperty(): int
    {
        $n = 0;
        if (filled($this->search))   $n++;
        if (filled($this->dateFrom)) $n++;
        if (filled($this->dateTo))   $n++;
        return $n;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    private function query(): Builder
    {
        $q = Trip::query()
            ->with(['truck', 'driver'])
            ->select('trips.*');

        // 🔎 Фильтр по датам
        if ($this->dateFrom) {
            $q->whereDate('trips.start_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $q->whereDate('trips.start_date', '<=', $this->dateTo);
        }

        // 🔎 Поиск
        if (($s = trim($this->search)) !== '') {
            $q->where(function (Builder $qq) use ($s) {

                if (is_numeric($s)) {
                    $qq->orWhere('trips.id', (int) $s);
                }

                $qq->orWhereHas('driver', function (Builder $d) use ($s) {
                    $d->where('first_name', 'like', "%{$s}%")
                      ->orWhere('last_name', 'like', "%{$s}%")
                      ->orWhere('pers_code', 'like', "%{$s}%");
                });

                $qq->orWhereHas('truck', function (Builder $t) use ($s) {
                    $t->where('plate', 'like', "%{$s}%")
                      ->orWhere('brand', 'like', "%{$s}%")
                      ->orWhere('model', 'like', "%{$s}%")
                      ->orWhere('license_number', 'like', "%{$s}%");
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 💰 ФРАХТ
        |--------------------------------------------------------------------------
        */
        $q->addSelect([
            'freight_total' => TripCargo::query()
                ->selectRaw('COALESCE(SUM(price), 0)')
                ->whereColumn('trip_id', 'trips.id'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 💸 РАСХОДЫ
        |--------------------------------------------------------------------------
        */
        $q->addSelect([
            'expenses_total' => TripExpense::query()
                ->selectRaw('COALESCE(SUM(amount), 0)')
                ->whereColumn('trip_id', 'trips.id'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 📈 PROFIT = freight - expenses
        |--------------------------------------------------------------------------
        */
        $q->selectRaw('
            (
                COALESCE((SELECT SUM(price) FROM trip_cargos WHERE trip_cargos.trip_id = trips.id), 0)
                -
                COALESCE((SELECT SUM(amount) FROM trip_expenses WHERE trip_expenses.trip_id = trips.id), 0)
            ) AS profit
        ');

        $q->addSelect([
            // 🕒 Timestamp:
            // 1) явное событие выезда/заезда из гаража
            // 2) started_at / ended_at из рейса
            // 3) как fallback — самое раннее / позднее событие по этому trip_id (step/expense и т.д.)
            'departure_at' => DB::raw(sprintf(
                'COALESCE(
                    (SELECT toe.occurred_at
                     FROM truck_odometer_events toe
                     WHERE toe.trip_id = trips.id
                       AND toe.type = %d
                     ORDER BY toe.occurred_at ASC
                     LIMIT 1),
                    trips.started_at,
                    (SELECT MIN(te2.occurred_at)
                     FROM truck_odometer_events te2
                     WHERE te2.trip_id = trips.id)
                )',
                TruckOdometerEvent::TYPE_DEPARTURE
            )),

            'return_at' => DB::raw(sprintf(
                'COALESCE(
                    (SELECT toe.occurred_at
                     FROM truck_odometer_events toe
                     WHERE toe.trip_id = trips.id
                       AND toe.type = %d
                     ORDER BY toe.occurred_at DESC
                     LIMIT 1),
                    trips.ended_at,
                    (SELECT MAX(te2.occurred_at)
                     FROM truck_odometer_events te2
                     WHERE te2.trip_id = trips.id)
                )',
                TruckOdometerEvent::TYPE_RETURN
            )),

            // 🚛 Odometer: из события, но если 0 или события нет — из trips.*
            'departure_odometer' => TruckOdometerEvent::query()
                ->selectRaw('COALESCE(NULLIF(odometer_km, 0), trips.odo_start_km)')
                ->whereColumn('trip_id', 'trips.id')
                ->where('type', TruckOdometerEvent::TYPE_DEPARTURE)
                ->orderBy('occurred_at', 'asc')
                ->limit(1),

            'return_odometer' => TruckOdometerEvent::query()
                ->selectRaw('COALESCE(NULLIF(odometer_km, 0), trips.odo_end_km)')
                ->whereColumn('trip_id', 'trips.id')
                ->where('type', TruckOdometerEvent::TYPE_RETURN)
                ->orderBy('occurred_at', 'desc')
                ->limit(1),
        ]);

        return $q;
    }

    public function render()
    {
        $allowedSort = [
            'id',
            'start_date',
            'end_date',
            'freight_total',
            'expenses_total',
            'profit',
            'departure_at',
            'return_at',
        ];

        $sortField = in_array($this->sortField, $allowedSort, true)
            ? $this->sortField
            : 'id';

        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $q = $this->query();

        // для вычисляемых полей
        if (in_array($sortField, ['freight_total', 'expenses_total', 'profit'], true)) {
            $q->orderByRaw("{$sortField} {$sortDir}");
        } else {
            $q->orderBy($sortField, $sortDir);
        }

        $rows = $q->paginate(15);

        return view('livewire.stats.trips-stats-table', compact('rows'))->layout('layouts.app', [
            'title' => 'Trips stats'
        ]);
    }
}
