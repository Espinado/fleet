<?php

namespace App\Livewire\Stats;

use App\Enums\TripExpenseCategory;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;
use App\Models\VehicleMaintenance;
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
            ->with(['truck', 'driver', 'trailer', 'carrierCompany'])
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

    /**
     * Группировка рейсов по периоду (неделя или месяц) для графика.
     * При диапазоне > 60 дней — по месяцам, иначе — по неделям.
     *
     * @param \Illuminate\Support\Collection $rows
     * @return array{labels: string[], freight: float[], profit: float[]}
     */
    private function buildChartData($rows): array
    {
        if ($rows->isEmpty()) {
            return ['labels' => [], 'freight' => [], 'profit' => [], 'label_freight' => '', 'label_profit' => ''];
        }

        $first = $rows->min('start_date');
        $last = $rows->max('start_date');
        $days = $first && $last ? Carbon::parse($first)->diffInDays(Carbon::parse($last)) : 0;
        $useMonth = $days > 60;

        $grouped = $rows->groupBy(function ($t) use ($useMonth) {
            $d = Carbon::parse($t->start_date);
            return $useMonth ? $d->format('Y-m') : $d->copy()->startOfWeek()->format('Y-m-d');
        });

        $labels = [];
        $freight = [];
        $profit = [];

        foreach ($grouped->keys()->sort()->values() as $key) {
            $items = $grouped[$key];
            if ($useMonth) {
                $labels[] = Carbon::createFromFormat('Y-m', $key)->translatedFormat('M Y');
            } else {
                $start = Carbon::parse($key);
                $end = $start->copy()->endOfWeek();
                $labels[] = $start->format('d.m') . '–' . $end->format('d.m');
            }
            $freight[] = round($items->sum('freight_total'), 2);
            $profit[] = round($items->sum('profit'), 2);
        }

        return [
            'labels' => $labels,
            'freight' => $freight,
            'profit' => $profit,
            'label_freight' => __('app.stats.chart_freight'),
            'label_profit' => __('app.stats.chart_profit'),
        ];
    }

    /**
     * Расходы по категориям за период (по дате рейса), без SUBCONTRACTOR.
     * Возвращает: ['items' => [...], 'total_amount' => float]
     */
    private function getExpensesByCategory(): array
    {
        $q = TripExpense::query()
            ->whereHas('trip', function (Builder $t) {
                if ($this->dateFrom) {
                    $t->whereDate('trips.start_date', '>=', $this->dateFrom);
                }
                if ($this->dateTo) {
                    $t->whereDate('trips.start_date', '<=', $this->dateTo);
                }
            })
            ->where('category', '!=', TripExpenseCategory::SUBCONTRACTOR->value);

        $rows = $q
            ->selectRaw('category, COALESCE(SUM(amount), 0) as total_amount, COALESCE(SUM(liters), 0) as total_liters')
            ->groupBy('category')
            ->orderByRaw('SUM(amount) DESC')
            ->get();

        $totalAmount = $rows->sum('total_amount');
        $items = $rows->map(function ($r) use ($totalAmount) {
            $label = $r->category
                ? ($r->category instanceof TripExpenseCategory ? $r->category->label() : (TripExpenseCategory::tryFrom((string) $r->category)?->label() ?? (string) $r->category))
                : '—';
            $pct = $totalAmount > 0 ? round((float) $r->total_amount / $totalAmount * 100, 1) : 0;
            return [
                'category' => $r->category,
                'label'    => $label,
                'amount'   => (float) $r->total_amount,
                'liters'   => (float) $r->total_liters,
                'percent'  => $pct,
            ];
        })->values()->all();

        return [
            'items'        => $items,
            'total_amount' => round($totalAmount, 2),
        ];
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

        // Сводка по тем же фильтрам (без дублирования: те же dateFrom/dateTo/search)
        $summaryQuery = (clone $q)->get();
        $totalKm = $summaryQuery->sum(function ($t) {
            $dep = (float) ($t->departure_odometer ?? $t->odo_start_km ?? 0);
            $ret = (float) ($t->return_odometer ?? $t->odo_end_km ?? 0);
            return $ret > $dep ? $ret - $dep : 0;
        });
        $tripIds = $summaryQuery->pluck('id')->filter()->values()->all();
        $expensesRoad = 0.0;
        if ($tripIds !== []) {
            $expensesRoad = (float) TripExpense::query()
                ->whereIn('trip_id', $tripIds)
                ->where(function (Builder $qb) {
                    $qb->whereNull('category')
                        ->orWhere('category', '!=', TripExpenseCategory::SUBCONTRACTOR->value);
                })
                ->sum('amount');
        }

        $expensesMaintenanceQuery = VehicleMaintenance::query()->whereNotNull('cost');
        if ($this->dateFrom) {
            $expensesMaintenanceQuery->whereDate('performed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $expensesMaintenanceQuery->whereDate('performed_at', '<=', $this->dateTo);
        }
        $expensesMaintenance = (float) $expensesMaintenanceQuery->sum('cost');
        $totalExpenses = $expensesRoad + $expensesMaintenance;
        $costPerKm = $totalKm > 0 ? round($expensesRoad / $totalKm, 2) : null;

        $summary = (object) [
            'trips_count'         => $summaryQuery->count(),
            'total_freight'       => round($summaryQuery->sum('freight_total'), 2),
            'expenses_road'       => round($expensesRoad, 2),
            'expenses_maintenance' => round($expensesMaintenance, 2),
            'total_expenses'      => round($totalExpenses, 2),
            'total_profit'        => round($summaryQuery->sum('freight_total') - $totalExpenses, 2),
            'avg_margin_percent'  => null,
            'total_km'            => round($totalKm, 1),
            'cost_per_km'         => $costPerKm,
        ];
        $withFreight = $summaryQuery->filter(fn ($t) => (float) ($t->freight_total ?? 0) > 0);
        if ($withFreight->isNotEmpty() && $summary->total_freight > 0) {
            $summary->avg_margin_percent = round(
                ($summary->total_profit / $summary->total_freight) * 100,
                1
            );
        }

        // График по периодам: группировка по неделе или месяцу
        $chartData = $this->buildChartData($summaryQuery);

        // Расходы по категориям за тот же период (по дате рейса), без SUBCONTRACTOR
        $expensesByCategory = $this->getExpensesByCategory();

        return view('livewire.stats.trips-stats-table', compact('rows', 'summary', 'chartData', 'expensesByCategory'))->layout('layouts.app', [
            'title' => 'Trips stats'
        ]);
    }
}
