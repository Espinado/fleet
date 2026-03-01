<?php

namespace App\Livewire\Stats;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

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

    // Table
    public string $sortField = 'occurred_at';
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
        'sortField' => ['except' => 'occurred_at'],
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
            TruckOdometerEvent::TYPE_DEPARTURE => 'Garage departure',
            TruckOdometerEvent::TYPE_RETURN    => 'Garage return',
            TruckOdometerEvent::TYPE_EXPENSE   => 'Driver expenses',
            TruckOdometerEvent::TYPE_STEP      => 'Step status',
        ];
    }

    protected function query(): Builder
{
    $ownCompanyIds = [1, 2];

    $q = TruckOdometerEvent::query()
        ->leftJoin('drivers as d', 'd.id', '=', 'truck_odometer_events.driver_id')
        ->leftJoin('trucks as tr', 'tr.id', '=', 'truck_odometer_events.truck_id')
        ->leftJoin('trip_expenses as te', 'te.id', '=', 'truck_odometer_events.trip_expense_id')
        ->select([
            'truck_odometer_events.*',
            'd.first_name as d_first_name',
            'd.last_name as d_last_name',
            'tr.brand as tr_brand',
            'tr.model as tr_model',
            'tr.plate as tr_plate',
            'te.liters as te_liters',
            'te.currency as te_currency',
            'te.description as te_description',
            'te.category as te_category',
            'te.odometer_km as te_odometer_km',
        ]);

    /**
     * ✅ Только свои водители (company_id 1 и 2)
     * + исключаем события без driver_id
     */
    $q->whereNotNull('truck_odometer_events.driver_id')
      ->whereIn('d.company_id', $ownCompanyIds);

    // ✅ Исключаем subcontractor (только для expense)
    $q->where(function (Builder $qq) {
        $qq->where('truck_odometer_events.type', '!=', TruckOdometerEvent::TYPE_EXPENSE)
           ->orWhereNull('truck_odometer_events.expense_category')
           ->orWhere('truck_odometer_events.expense_category', '!=', TripExpenseCategory::SUBCONTRACTOR->value);
    });

    // Search
    $search = trim($this->search);
    if ($search !== '') {
        $q->where(function (Builder $qq) use ($search) {
            $qq->where('d.first_name', 'like', "%{$search}%")
               ->orWhere('d.last_name', 'like', "%{$search}%")
               ->orWhere('tr.brand', 'like', "%{$search}%")
               ->orWhere('tr.model', 'like', "%{$search}%")
               ->orWhere('tr.plate', 'like', "%{$search}%")
               ->orWhere('truck_odometer_events.expense_category', 'like', "%{$search}%")
               ->orWhere('te.description', 'like', "%{$search}%")
               ->orWhere('truck_odometer_events.note', 'like', "%{$search}%");
        });
    }

    if (!empty($this->type)) {
        $q->where('truck_odometer_events.type', (int) $this->type);
    }

    if (!empty($this->driverId)) {
        $q->where('truck_odometer_events.driver_id', (int) $this->driverId);
    }

    if (!empty($this->truckId)) {
        $q->where('truck_odometer_events.truck_id', (int) $this->truckId);
    }

    if ($this->dateFrom) {
        $q->whereDate('truck_odometer_events.occurred_at', '>=', $this->dateFrom);
    }

    if ($this->dateTo) {
        $q->whereDate('truck_odometer_events.occurred_at', '<=', $this->dateTo);
    }

    $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

    $fieldMap = [
        'timestamp'   => 'truck_odometer_events.occurred_at',
        'occurred_at' => 'truck_odometer_events.occurred_at',
        'driver'      => 'd_first_name',
        'truck'       => 'tr_plate',
        'type'        => 'truck_odometer_events.type',
        'odometer_km' => 'truck_odometer_events.odometer_km',
        'amount'      => 'truck_odometer_events.expense_amount',
    ];

    $field = $fieldMap[$this->sortField] ?? 'truck_odometer_events.occurred_at';

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
            'title' => 'Driver events',
        ]);
    }
}
