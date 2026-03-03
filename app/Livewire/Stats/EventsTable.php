<?php

namespace App\Livewire\Stats;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;
use App\Models\Driver;
use App\Models\Truck;
use App\Enums\TripExpenseCategory;

class EventsTable extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public ?int $type = null;
    public ?int $driverId = null;
    public ?int $truckId = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public array $ownCompanyIds = [1, 2];

    // Table: по умолчанию по дате, последние сначала (как в /trips)
    public string $sortField = 'timestamp';
    public string $sortDirection = 'desc';
    public int $perPage = 25;

    // Dropdown lists
    public array $drivers = [];
    public array $trucks = [];

    // PWA/mobile
    public bool $filtersOpen = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => null],
        'driverId' => ['except' => null],
        'truckId' => ['except' => null],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'sortField' => ['except' => 'timestamp'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 25],
    ];

    public function mount(): void
    {
        $this->drivers = Driver::query()
            ->select(['id', 'first_name', 'last_name'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => trim(($d->first_name ?? '') . ' ' . ($d->last_name ?? '')),
            ])
            ->toArray();

        $this->trucks = Truck::query()
            ->select(['id', 'brand', 'model', 'plate'])
            ->orderBy('brand')
            ->orderBy('plate')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => trim(($t->brand ?? '') . ' ' . ($t->model ?? '') . ' ' . ($t->plate ?? '')),
            ])
            ->toArray();
    }

    public function updated($name): void
    {
        if (in_array($name, [
            'search','type','driverId','truckId','dateFrom','dateTo','perPage'
        ], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->type = null;
        $this->driverId = null;
        $this->truckId = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->perPage = 25;

        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function getTypeOptionsProperty(): array
    {
        return [
            TruckOdometerEvent::TYPE_DEPARTURE => __('app.stats.departure_garage'),
            TruckOdometerEvent::TYPE_RETURN    => __('app.stats.return_garage'),
            TruckOdometerEvent::TYPE_EXPENSE   => __('app.stats.events.badge_expense'),
            TruckOdometerEvent::TYPE_STEP      => __('app.stats.events.badge_step'),
        ];
    }

    protected function query(): \Illuminate\Database\Query\Builder
    {
        $ownCompanyIds = $this->ownCompanyIds;
        $search = trim($this->search);
        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        /**
         * 1) Event-строки из TruckOdometerEvent (кроме TYPE_EXPENSE).
         */
        $events = DB::table('truck_odometer_events as toe')
            ->leftJoin('drivers as d', 'd.id', '=', 'toe.driver_id')
            ->leftJoin('trucks as tr', 'tr.id', '=', 'toe.truck_id')
            ->leftJoin('trips as t', 't.id', '=', 'toe.trip_id')
            ->select([
                DB::raw("'event' as row_kind"),
                'toe.id as id',
                'toe.type as type',

                // Trip linkage (for departure/return odo fallback)
                't.id as trip_id',
                't.odo_start_km as trip_odo_start_km',
                't.odo_end_km as trip_odo_end_km',

                // Expense-only поля (всегда NULL для events)
                DB::raw('NULL as expense_category'),
                DB::raw('NULL as expense_date'),
                DB::raw('NULL as amount'),
                DB::raw('NULL as te_currency'),
                DB::raw('NULL as te_liters'),
                DB::raw('NULL as te_description'),

                // Odometer / timestamps / step
                'toe.odometer_km as odometer_km',
                'toe.occurred_at as occurred_at',
                'toe.step_status as step_status',
                'toe.note as note',

                // Driver / truck
                'd.first_name as d_first_name',
                'd.last_name as d_last_name',
                'tr.brand as tr_brand',
                'tr.model as tr_model',
                'tr.plate as tr_plate',
            ])
            ->whereNotNull('toe.driver_id')
            ->whereIn('d.company_id', $ownCompanyIds)
            // В этом списке не хотим TYPE_EXPENSE — расходы отдельными строками
            ->where('toe.type', '!=', TruckOdometerEvent::TYPE_EXPENSE);

        /**
         * 2) Expense-строки из TripExpense.
         */
        $expenses = DB::table('trip_expenses as te')
            ->leftJoin('trips as t', 't.id', '=', 'te.trip_id')
            ->leftJoin('drivers as d', 'd.id', '=', 't.driver_id')
            ->leftJoin('trucks as tr', 'tr.id', '=', 't.truck_id')
            ->select([
                DB::raw("'expense' as row_kind"),
                'te.id as id',
                DB::raw('NULL as type'),

                // Trip linkage
                't.id as trip_id',
                't.odo_start_km as trip_odo_start_km',
                't.odo_end_km as trip_odo_end_km',

                // Категория расхода и money-поля
                'te.category as expense_category',
                'te.expense_date as expense_date',
                'te.amount as amount',
                'te.currency as te_currency',
                'te.liters as te_liters',
                'te.description as te_description',

                // Odometer / timestamps / step
                'te.odometer_km as odometer_km',
                DB::raw('NULL as occurred_at'),
                DB::raw('NULL as step_status'),
                DB::raw('NULL as note'),

                // Driver / truck
                'd.first_name as d_first_name',
                'd.last_name as d_last_name',
                'tr.brand as tr_brand',
                'tr.model as tr_model',
                'tr.plate as tr_plate',
            ])
            ->whereNotNull('t.driver_id')
            ->whereIn('d.company_id', $ownCompanyIds)
            // Исключаем subcontractor-расходы
            ->where(function ($qq) {
                $qq->whereNull('te.category')
                    ->orWhere('te.category', '!=', TripExpenseCategory::SUBCONTRACTOR->value);
            });

        /**
         * Общие фильтры (search, driver, truck, dates, type) применяем к обоим подзапросам.
         */

        if ($search !== '') {
            $events->where(function ($qq) use ($search) {
                $qq->where('d.first_name', 'like', "%{$search}%")
                    ->orWhere('d.last_name', 'like', "%{$search}%")
                    ->orWhere('tr.brand', 'like', "%{$search}%")
                    ->orWhere('tr.model', 'like', "%{$search}%")
                    ->orWhere('tr.plate', 'like', "%{$search}%")
                    ->orWhere('toe.note', 'like', "%{$search}%");
            });

            $expenses->where(function ($qq) use ($search) {
                $qq->where('d.first_name', 'like', "%{$search}%")
                    ->orWhere('d.last_name', 'like', "%{$search}%")
                    ->orWhere('tr.brand', 'like', "%{$search}%")
                    ->orWhere('tr.model', 'like', "%{$search}%")
                    ->orWhere('tr.plate', 'like', "%{$search}%")
                    ->orWhere('te.description', 'like', "%{$search}%")
                    ->orWhere('te.category', 'like', "%{$search}%");
            });
        }

        if (!empty($this->driverId)) {
            $events->where('toe.driver_id', (int) $this->driverId);
            $expenses->where('t.driver_id', (int) $this->driverId);
        }

        if (!empty($this->truckId)) {
            $events->where('toe.truck_id', (int) $this->truckId);
            $expenses->where('t.truck_id', (int) $this->truckId);
        }

        // Фильтр по типу: для events — по toe.type, для expenses — только TYPE_EXPENSE
        if (!empty($this->type)) {
            $type = (int) $this->type;

            $events->where('toe.type', $type);

            if ($type === TruckOdometerEvent::TYPE_EXPENSE) {
                // показываем только expense-строки
            } else {
                // при других типах расходов не показываем
                $expenses->whereRaw('1=0');
            }
        }

        // Даты: для events по occurred_at, для expenses по expense_date
        if ($this->dateFrom) {
            $events->whereDate('toe.occurred_at', '>=', $this->dateFrom);
            $expenses->whereDate('te.expense_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $events->whereDate('toe.occurred_at', '<=', $this->dateTo);
            $expenses->whereDate('te.expense_date', '<=', $this->dateTo);
        }

        /**
         * Объединяем события и расходы в единый поток строк.
         */
        $base = $events->unionAll($expenses);

        $q = DB::query()->fromSub($base, 'rows');

        // Специальная сортировка по времени
        if (in_array($this->sortField, ['timestamp', 'occurred_at'], true)) {
            $q->orderByRaw('COALESCE(occurred_at, expense_date) ' . $dir);
            return $q;
        }

        $fieldMap = [
            'driver'      => 'd_first_name',
            'truck'       => 'tr_plate',
            'type'        => 'type',
            'odometer_km' => 'odometer_km',
            'amount'      => 'amount',
        ];

        $field = $fieldMap[$this->sortField] ?? 'expense_date';

        // Для driver/truck сделаем сортировку стабильнее
        if ($field === 'd_first_name') {
            $q->orderBy('d_first_name', $dir)->orderBy('d_last_name', $dir);
        } elseif ($field === 'tr_plate') {
            $q->orderBy('tr_plate', $dir)->orderBy('tr_brand', $dir);
        } else {
            $q->orderBy($field, $dir);
        }

        return $q;
    }
    public function getRowsProperty()
    {
        return $this->query()->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.stats.events-table', [
            'rows'  => $this->rows,
            'types' => $this->typeOptions,
        ])->layout('layouts.app', [
            'title' => __('app.stats.events.title'),
        ]);
    }
}
