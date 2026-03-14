<?php

namespace App\Livewire\Stats;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;
use App\Models\Driver;
use App\Models\Truck;
use App\Enums\TripExpenseCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class EventsTable extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    /** @var int|null Приходит из query/select как string — нормализуем в mount/updatedType */
    public $type = null;
    public ?int $driverId = null;
    public ?int $truckId = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    /** @var array<int> ID компаний, по которым показывать события (из текущего пользователя) */
    public array $ownCompanyIds = [];

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

    public function updatedType($value): void
    {
        $this->type = $value !== null && $value !== '' ? (int) $value : null;
    }

    public function mount(): void
    {
        $user = Auth::user();
        $this->ownCompanyIds = $user ? $user->allowedMapCompanyIds() : [];
        if ($this->type !== null && $this->type !== '') {
            $this->type = (int) $this->type;
        } elseif ($this->type === '') {
            $this->type = null;
        }

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
            ->leftJoin('trip_steps as ts', 'ts.id', '=', 'toe.trip_step_id')
            ->select([
                DB::raw("'event' as row_kind"),
                'toe.id as id',
                'toe.type as type',

                't.id as trip_id',
                't.odo_start_km as trip_odo_start_km',
                't.odo_end_km as trip_odo_end_km',

                'toe.trip_step_id as trip_step_id',
                'ts.address as step_address',
                'ts.type as step_type',

                DB::raw('NULL as expense_category'),
                DB::raw('NULL as expense_date'),
                DB::raw('NULL as amount'),
                DB::raw('NULL as te_currency'),
                DB::raw('NULL as te_liters'),
                DB::raw('NULL as te_description'),

                'toe.odometer_km as odometer_km',
                'toe.occurred_at as occurred_at',
                'toe.step_status as step_status',
                'toe.note as note',

                'd.first_name as d_first_name',
                'd.last_name as d_last_name',
                'tr.brand as tr_brand',
                'tr.model as tr_model',
                'tr.plate as tr_plate',
            ])
            ->whereNotNull('toe.driver_id')
            ->whereIn('tr.company_id', $ownCompanyIds)
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

                't.id as trip_id',
                't.odo_start_km as trip_odo_start_km',
                't.odo_end_km as trip_odo_end_km',

                DB::raw('NULL as trip_step_id'),
                DB::raw('NULL as step_address'),
                DB::raw('NULL as step_type'),

                'te.category as expense_category',
                'te.expense_date as expense_date',
                'te.amount as amount',
                'te.currency as te_currency',
                'te.liters as te_liters',
                'te.description as te_description',

                'te.odometer_km as odometer_km',
                DB::raw('NULL as occurred_at'),
                DB::raw('NULL as step_status'),
                DB::raw('NULL as note'),

                'd.first_name as d_first_name',
                'd.last_name as d_last_name',
                'tr.brand as tr_brand',
                'tr.model as tr_model',
                'tr.plate as tr_plate',
            ])
            ->whereNotNull('t.driver_id')
            ->whereIn('tr.company_id', $ownCompanyIds)
            ->where(function ($qq) {
                $qq->whereNull('te.category')
                    ->orWhere('te.category', '!=', TripExpenseCategory::SUBCONTRACTOR->value);
            });

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

        if (!empty($this->type)) {
            $type = (int) $this->type;
            $events->where('toe.type', $type);
            if ($type !== TruckOdometerEvent::TYPE_EXPENSE) {
                $expenses->whereRaw('1=0');
            }
        }

        if ($this->dateFrom) {
            $events->whereDate('toe.occurred_at', '>=', $this->dateFrom);
            $expenses->whereDate('te.expense_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $events->whereDate('toe.occurred_at', '<=', $this->dateTo);
            $expenses->whereDate('te.expense_date', '<=', $this->dateTo);
        }

        $base = $events->unionAll($expenses);
        $q = DB::query()->fromSub($base, 'rows');

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
        $paginator = $this->query()->paginate($this->perPage);
        $expenseIds = $paginator->getCollection()
            ->filter(fn ($row) => ($row->row_kind ?? '') === 'expense')
            ->pluck('id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $expensesById = $expenseIds !== []
            ? TripExpense::whereIn('id', $expenseIds)->get()->keyBy('id')
            : collect();

        $paginator->getCollection()->transform(function ($row) use ($expensesById) {
            if (($row->row_kind ?? '') !== 'expense') {
                return $row;
            }
            $expense = $expensesById->get($row->id);
            if ($expense) {
                $row->amount = $expense->amount;
                $row->te_currency = $expense->currency ?? 'EUR';
                $row->expense_date = $expense->expense_date?->format('Y-m-d');
                $row->odometer_km = $expense->odometer_km;
                $row->te_liters = $expense->liters;
                $row->expense_category = $expense->category?->value;
            }
            if ($expense?->category && $expense->category !== TripExpenseCategory::SUBCONTRACTOR) {
                $row->expense_type_label = $expense->category->label();
                $row->expense_is_fuel_like = in_array($expense->category, [
                    TripExpenseCategory::FUEL,
                    TripExpenseCategory::ADBLUE,
                ], true);
            } else {
                $row->expense_type_label = __('app.stats.events.badge_expense');
                $row->expense_is_fuel_like = false;
            }
            return $row;
        });
        return $paginator;
    }

    public function getSummaryProperty(): array
    {
        $ownCompanyIds = $this->ownCompanyIds;
        if ($ownCompanyIds === []) {
            return [
                'liters_items'  => [],
                'by_category'   => [],
                'total_amount'  => 0.0,
                'total_liters'  => 0.0,
                'period_label'  => $this->periodLabel(),
            ];
        }

        // Сводка по расходам: показываем всегда за весь период (по фильтрам), кроме случая когда выбран тип «только не расходы»
        $typeInt = $this->type !== null && $this->type !== '' ? (int) $this->type : null;
        if ($typeInt !== null && $typeInt !== TruckOdometerEvent::TYPE_EXPENSE) {
            return [
                'liters_items'  => [],
                'by_category'   => [],
                'total_amount'  => 0.0,
                'total_liters'  => 0.0,
                'period_label'  => $this->periodLabel(),
            ];
        }

        $q = DB::table('trip_expenses as te')
            ->leftJoin('trips as t', 't.id', '=', 'te.trip_id')
            ->leftJoin('trucks as tr', 'tr.id', '=', 't.truck_id')
            ->whereNotNull('t.driver_id')
            ->whereIn('tr.company_id', $ownCompanyIds)
            ->where(function ($qq) {
                $qq->whereNull('te.category')
                    ->orWhere('te.category', '!=', TripExpenseCategory::SUBCONTRACTOR->value);
            });

        if ($this->dateFrom) {
            $q->whereDate('te.expense_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $q->whereDate('te.expense_date', '<=', $this->dateTo);
        }
        if (!empty($this->driverId)) {
            $q->where('t.driver_id', (int) $this->driverId);
        }
        if (!empty($this->truckId)) {
            $q->where('t.truck_id', (int) $this->truckId);
        }

        $litersCategories = [
            TripExpenseCategory::FUEL->value,
            TripExpenseCategory::ADBLUE->value,
            TripExpenseCategory::WASHER_FLUID->value,
        ];

        $rows = $q->select('te.category')
            ->selectRaw('COALESCE(SUM(te.amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(te.liters), 0) as total_liters')
            ->groupBy('te.category')
            ->get();

        $litersItems = [];
        $byCategory = [];
        $totalAmount = 0.0;
        $totalLiters = 0.0;

        foreach ($rows as $r) {
            $cat = $r->category;
            $label = $cat ? (function () use ($cat) {
                try {
                    return TripExpenseCategory::from($cat)->label();
                } catch (\Throwable $e) {
                    return $cat;
                }
            })() : __('app.stats.events.badge_expense');
            $amt = (float) $r->total_amount;
            $lit = (float) $r->total_liters;
            $totalAmount += $amt;
            $totalLiters += $lit;

            if ($cat && in_array($cat, $litersCategories, true)) {
                $litersItems[$cat] = ['label' => $label, 'liters' => $lit];
            } else {
                $byCategory[] = [
                    'label'  => $label,
                    'amount' => $amt,
                    'liters' => $lit,
                ];
            }
        }

        $litersOrdered = [];
        foreach ($litersCategories as $key) {
            $litersOrdered[] = $litersItems[$key] ?? [
                'label' => TripExpenseCategory::from($key)->label(),
                'liters' => 0.0,
            ];
        }

        return [
            'liters_items'  => $litersOrdered,
            'by_category'   => $byCategory,
            'total_amount'  => $totalAmount,
            'total_liters'  => $totalLiters,
            'period_label'  => $this->periodLabel(),
        ];
    }

    protected function periodLabel(): string
    {
        if ($this->dateFrom && $this->dateTo) {
            return $this->dateFrom . ' — ' . $this->dateTo;
        }
        if ($this->dateFrom) {
            return __('app.stats.events.summary_from') . ' ' . $this->dateFrom;
        }
        if ($this->dateTo) {
            return __('app.stats.events.summary_to') . ' ' . $this->dateTo;
        }
        return __('app.stats.events.summary_period_all');
    }

    public function exportPdf()
    {
        $query = $this->query();
        $allRows = $query->get();

        $expenseIds = $allRows->filter(fn ($row) => ($row->row_kind ?? '') === 'expense')->pluck('id')->filter()->unique()->values()->all();
        $expensesById = $expenseIds !== []
            ? TripExpense::whereIn('id', $expenseIds)->get()->keyBy('id')
            : collect();

        $rows = $allRows->map(function ($row) use ($expensesById) {
            $obj = (object) (array) $row;
            if (($row->row_kind ?? '') === 'expense') {
                $expense = $expensesById->get($row->id);
                if ($expense) {
                    $obj->amount = $expense->amount;
                    $obj->te_currency = $expense->currency ?? 'EUR';
                    $obj->expense_date = $expense->expense_date?->format('Y-m-d');
                    $obj->odometer_km = $expense->odometer_km;
                    $obj->te_liters = $expense->liters;
                    $obj->expense_category = $expense->category?->value;
                    if ($expense->category && $expense->category !== TripExpenseCategory::SUBCONTRACTOR) {
                        $obj->expense_type_label = $expense->category->label();
                    } else {
                        $obj->expense_type_label = __('app.stats.events.badge_expense');
                    }
                }
            }
            return $obj;
        });

        $summary = $this->summary;

        $html = view('pdf.stats-events', [
            'rows'    => $rows,
            'summary' => $summary,
            'title'   => __('app.stats.events.title'),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');

        $filename = 'notikumi-' . ($this->dateFrom ?: '') . ($this->dateTo ? '-' . $this->dateTo : '') . '.pdf';
        $filename = preg_replace('/^--/', '', $filename) ?: 'notikumi.pdf';

        return Response::streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        return view('livewire.stats.events-table', [
            'rows'    => $this->rows,
            'types'   => $this->typeOptions,
            'summary' => $this->summary,
        ])->layout('layouts.app', [
            'title' => __('app.stats.events.title'),
        ]);
    }
}
