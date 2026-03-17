<?php

namespace App\Livewire\Stats;

use App\Enums\TripExpenseCategory;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripExpense;
use App\Models\Truck;
use App\Models\Trailer;
use App\Models\TruckOdometerEvent;
use App\Models\VehicleMaintenance;
use App\Services\ExpiringDocumentsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class OwnerDashboard extends Component
{
    /** Период: null = всё время, иначе dateFrom/dateTo */
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    /** Топ клиенты: 'revenue' | 'trips' */
    public string $topClientsMode = 'revenue';

    /** Топ тягачи: 'revenue' | 'trips' */
    public string $topTrucksMode = 'revenue';

    /** Истекающие документы (блок на дашборде) */
    public string $expiringSearch = '';
    public string $expiringSort = 'expires_at';
    public string $expiringDir = 'asc';
    public int $expiringPage = 1;
    public int $expiringPerPage = 5;

    /** Предстоящее ТО (блок на дашборде) */
    public string $upcomingSearch = '';
    public string $upcomingSort = 'expires_at';
    public string $upcomingDir = 'asc';
    public int $upcomingPage = 1;
    public int $upcomingPerPage = 5;

    protected $queryString = [
        'dateFrom' => ['except' => null],
        'dateTo'   => ['except' => null],
        'expiringSearch' => ['except' => ''],
        'expiringPage' => ['except' => 1],
        'upcomingSearch' => ['except' => ''],
        'upcomingPage' => ['except' => 1],
    ];

    public function setPeriodAll(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
    }

    public function setPeriodMonth(): void
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo = Carbon::now()->endOfMonth()->toDateString();
    }

    public function setPeriodYear(): void
    {
        $this->dateFrom = Carbon::now()->startOfYear()->toDateString();
        $this->dateTo = Carbon::now()->endOfYear()->toDateString();
    }

    public function clearDates(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
    }

    private function tripDateQuery(Builder $q): void
    {
        if ($this->dateFrom) {
            $q->whereDate('trips.start_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $q->whereDate('trips.start_date', '<=', $this->dateTo);
        }
    }

    public function getKpiProperty(): object
    {
        $tripIds = Trip::query();
        $this->tripDateQuery($tripIds);
        $tripIds = $tripIds->pluck('id');

        $revenue = TripCargo::whereIn('trip_id', $tripIds)->sum('price');
        $expenses = TripExpense::whereIn('trip_id', $tripIds)
            ->where('category', '!=', TripExpenseCategory::SUBCONTRACTOR->value)
            ->sum('amount');

        $maintenanceCost = 0.0;
        $maintenanceQuery = VehicleMaintenance::query()->whereNotNull('cost');
        if ($this->dateFrom) {
            $maintenanceQuery->whereDate('performed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $maintenanceQuery->whereDate('performed_at', '<=', $this->dateTo);
        }
        $maintenanceCost = (float) $maintenanceQuery->sum('cost');
        $expenses += $maintenanceCost;

        return (object) [
            'revenue'  => round((float) $revenue, 2),
            'expenses' => round((float) $expenses, 2),
            'profit'   => round((float) $revenue - (float) $expenses, 2),
            'trips_count' => $tripIds->count(),
            'maintenance_costs' => round($maintenanceCost, 2),
        ];
    }

    public function getReceivablesProperty(): object
    {
        $user = auth()->user();
        $summary = (object) [
            'total_receivables' => 0.0,
            'invoices_with_balance_count' => 0,
            'overdue_count' => 0,
            'overdue_amount' => 0.0,
        ];

        $q = Invoice::query()
            ->select('invoices.id', 'invoices.total', 'invoices.due_date')
            ->selectSub(function ($sub) {
                $sub->from('invoice_payments')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('invoice_id', 'invoices.id');
            }, 'paid_total');

        if (!$user || (!$user->isAdmin() && $user->company_id === null)) {
            return $summary;
        }
        if (!$user->isAdmin()) {
            $q->whereHas('trip', fn ($sub) => $sub->where('carrier_company_id', (int) $user->company_id));
        }

        foreach ($q->get() as $inv) {
            $balance = (float) $inv->total - (float) $inv->paid_total;
            if ($balance <= 0) {
                continue;
            }
            $summary->total_receivables += $balance;
            $summary->invoices_with_balance_count++;
            if ($inv->due_date && Carbon::parse($inv->due_date)->isPast()) {
                $summary->overdue_count++;
                $summary->overdue_amount += $balance;
            }
        }
        $summary->total_receivables = round($summary->total_receivables, 2);
        $summary->overdue_amount = round($summary->overdue_amount, 2);
        return $summary;
    }

    public function getTopClientsByRevenueProperty(): \Illuminate\Support\Collection
    {
        $q = TripCargo::query()
            ->select('trip_cargos.customer_id')
            ->selectRaw('COALESCE(SUM(trip_cargos.price), 0) as total_revenue')
            ->selectRaw('COUNT(DISTINCT trip_cargos.trip_id) as trips_count')
            ->whereHas('trip', fn (Builder $t) => $this->tripDateQuery($t))
            ->whereNotNull('trip_cargos.customer_id')
            ->groupBy('trip_cargos.customer_id')
            ->orderByDesc('total_revenue')
            ->limit(10);

        $rows = $q->get();
        $clientIds = $rows->pluck('customer_id')->filter()->unique()->values()->all();
        $clients = Client::whereIn('id', $clientIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($clients) {
            $row->client_name = $clients->get($row->customer_id)?->company_name ?? '—';
            return $row;
        });
    }

    public function getTopClientsByTripsProperty(): \Illuminate\Support\Collection
    {
        $q = TripCargo::query()
            ->select('trip_cargos.customer_id')
            ->selectRaw('COUNT(DISTINCT trip_cargos.trip_id) as trips_count')
            ->selectRaw('COALESCE(SUM(trip_cargos.price), 0) as total_revenue')
            ->whereHas('trip', fn (Builder $t) => $this->tripDateQuery($t))
            ->whereNotNull('trip_cargos.customer_id')
            ->groupBy('trip_cargos.customer_id')
            ->orderByDesc('trips_count')
            ->limit(10);

        $rows = $q->get();
        $clientIds = $rows->pluck('customer_id')->filter()->unique()->values()->all();
        $clients = Client::whereIn('id', $clientIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($clients) {
            $row->client_name = $clients->get($row->customer_id)?->company_name ?? '—';
            return $row;
        });
    }

    public function getTopTrucksByRevenueProperty(): \Illuminate\Support\Collection
    {
        $rows = Trip::query()
            ->select('trips.truck_id')
            ->selectRaw('COALESCE(SUM(trip_cargos.price), 0) as total_revenue')
            ->selectRaw('COUNT(DISTINCT trips.id) as trips_count')
            ->join('trip_cargos', 'trip_cargos.trip_id', '=', 'trips.id')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('trips.start_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('trips.start_date', '<=', $this->dateTo))
            ->whereNotNull('trips.truck_id')
            ->groupBy('trips.truck_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $truckIds = $rows->pluck('truck_id')->filter()->unique()->values()->all();
        $trucks = Truck::whereIn('id', $truckIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($trucks) {
            $t = $trucks->get($row->truck_id);
            $row->truck_name = $t ? trim(($t->brand ?? '') . ' ' . ($t->model ?? '') . ' ' . ($t->plate ?? '')) : '—';
            return $row;
        });
    }

    public function getTopTrucksByTripsProperty(): \Illuminate\Support\Collection
    {
        $rows = Trip::query()
            ->select('trips.truck_id')
            ->selectRaw('COUNT(DISTINCT trips.id) as trips_count')
            ->selectRaw('COALESCE(SUM(trip_cargos.price), 0) as total_revenue')
            ->leftJoin('trip_cargos', 'trip_cargos.trip_id', '=', 'trips.id')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('trips.start_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('trips.start_date', '<=', $this->dateTo))
            ->whereNotNull('trips.truck_id')
            ->groupBy('trips.truck_id')
            ->orderByDesc('trips_count')
            ->limit(10)
            ->get();

        $truckIds = $rows->pluck('truck_id')->filter()->unique()->values()->all();
        $trucks = Truck::whereIn('id', $truckIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($trucks) {
            $t = $trucks->get($row->truck_id);
            $row->truck_name = $t ? trim(($t->brand ?? '') . ' ' . ($t->model ?? '') . ' ' . ($t->plate ?? '')) : '—';
            return $row;
        });
    }

    public function getDowntimeProperty(): object
    {
        $q = TripCargo::query()
            ->where('has_delay', true)
            ->whereHas('trip', fn (Builder $t) => $this->tripDateQuery($t));

        $totalAmount = (clone $q)->sum('delay_amount');
        $count = (clone $q)->count();

        return (object) [
            'total_amount' => round((float) $totalAmount, 2),
            'cargos_count' => (int) $count,
        ];
    }

    public function getExpiringCountProperty(): int
    {
        $today = Carbon::today();
        $deadline = $today->copy()->addDays(30);

        $driverCount = \App\Models\Driver::where(function ($q) use ($today, $deadline) {
            $q->whereBetween('license_end', [$today, $deadline])
                ->orWhereBetween('code95_end', [$today, $deadline])
                ->orWhereBetween('permit_expired', [$today, $deadline])
                ->orWhereBetween('medical_expired', [$today, $deadline])
                ->orWhereBetween('declaration_expired', [$today, $deadline]);
        })->count();

        $truckCount = \App\Models\Truck::where(function ($q) use ($today, $deadline) {
            $q->whereBetween('inspection_expired', [$today, $deadline])
                ->orWhereBetween('insurance_expired', [$today, $deadline])
                ->orWhereBetween('tech_passport_expired', [$today, $deadline]);
        })->count();

        $trailerCount = \App\Models\Trailer::where(function ($q) use ($today, $deadline) {
            $q->whereBetween('inspection_expired', [$today, $deadline])
                ->orWhereBetween('insurance_expired', [$today, $deadline])
                ->orWhereBetween('tech_passport_expired', [$today, $deadline])
                ->orWhereBetween('tir_expired', [$today, $deadline]);
        })->count();

        return $driverCount + $truckCount + $trailerCount;
    }

    public function getExpiringDocumentsPaginatorProperty(): LengthAwarePaginator
    {
        $list = ExpiringDocumentsService::list(30);
        if ($this->expiringSearch !== '') {
            $term = mb_strtolower(trim($this->expiringSearch));
            $list = $list->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item->entity_name), $term)
                    || str_contains(mb_strtolower(__('app.' . $item->doc_label_key)), $term);
            })->values();
        }
        $list = $this->expiringDir === 'desc'
            ? $list->sortByDesc($this->expiringSort)->values()
            : $list->sortBy($this->expiringSort)->values();
        $total = $list->count();
        $chunk = $list->forPage($this->expiringPage, $this->expiringPerPage)->values();
        return new LengthAwarePaginator($chunk, $total, $this->expiringPerPage, $this->expiringPage, ['path' => request()->url(), 'pageName' => 'expiringPage']);
    }

    public function getUpcomingMaintenanceListProperty(): Collection
    {
        $today = Carbon::today();
        $dateLimit = $today->copy()->addDays(30);
        $kmThreshold = 2000;
        $list = collect();
        $truckOdometers = TruckOdometerEvent::query()
            ->select('truck_id')
            ->selectRaw('MAX(odometer_km) as last_km')
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->groupBy('truck_id')
            ->pluck('last_km', 'truck_id');
        $trucks = Truck::query()
            ->whereHas('company', fn ($c) => $c->where(fn ($q) => $q->where('is_third_party', false)->orWhereNull('is_third_party')))
            ->where(fn ($q) => $q->whereNotNull('next_service_km')->orWhereNotNull('next_service_date'))
            ->get();
        foreach ($trucks as $truck) {
            $currentKm = (int) ($truckOdometers->get($truck->id) ?? Trip::where('truck_id', $truck->id)->max('odo_end_km') ?? 0);
            $dueByKm = $truck->next_service_km !== null && $currentKm > 0 && $currentKm >= (int) $truck->next_service_km - $kmThreshold;
            $dueByDate = $truck->next_service_date !== null && Carbon::parse($truck->next_service_date)->lte($dateLimit);
            if ($dueByKm || $dueByDate) {
                $list->push((object) [
                    'type' => 'truck',
                    'id' => $truck->id,
                    'name' => trim(($truck->brand ?? '') . ' ' . ($truck->model ?? '') . ' ' . ($truck->plate ?? '')),
                    'due_by_km' => $dueByKm,
                    'due_by_date' => $dueByDate,
                    'next_service_km' => $truck->next_service_km,
                    'next_service_date' => $truck->next_service_date,
                    'sort_date' => $truck->next_service_date,
                ]);
            }
        }
        $trailers = Trailer::query()
            ->whereHas('company', fn ($c) => $c->where(fn ($q) => $q->where('is_third_party', false)->orWhereNull('is_third_party')))
            ->whereNotNull('next_service_date')
            ->whereDate('next_service_date', '<=', $dateLimit)
            ->get();
        foreach ($trailers as $trailer) {
            $list->push((object) [
                'type' => 'trailer',
                'id' => $trailer->id,
                'name' => trim(($trailer->brand ?? '') . ' ' . ($trailer->plate ?? '')),
                'due_by_km' => false,
                'due_by_date' => true,
                'next_service_km' => $trailer->next_service_km,
                'next_service_date' => $trailer->next_service_date,
                'sort_date' => $trailer->next_service_date,
            ]);
        }
        return $list->sortBy(fn ($i) => $i->sort_date ?? '9999-12-31')->values();
    }

    public function getUpcomingMaintenancePaginatorProperty(): LengthAwarePaginator
    {
        $list = $this->upcomingMaintenanceList;
        if ($this->upcomingSearch !== '') {
            $term = mb_strtolower(trim($this->upcomingSearch));
            $list = $list->filter(fn ($item) => str_contains(mb_strtolower($item->name), $term))->values();
        }
        $list = $this->upcomingDir === 'desc'
            ? $list->sortByDesc($this->upcomingSort === 'expires_at' ? 'sort_date' : 'name')->values()
            : $list->sortBy($this->upcomingSort === 'expires_at' ? 'sort_date' : 'name')->values();
        $total = $list->count();
        $chunk = $list->forPage($this->upcomingPage, $this->upcomingPerPage)->values();
        return new LengthAwarePaginator($chunk, $total, $this->upcomingPerPage, $this->upcomingPage, ['path' => request()->url(), 'pageName' => 'upcomingPage']);
    }

    public function updatingExpiringSort(): void { $this->expiringPage = 1; }
    public function updatingExpiringDir(): void { $this->expiringPage = 1; }
    public function updatingUpcomingSort(): void { $this->upcomingPage = 1; }
    public function updatingUpcomingDir(): void { $this->upcomingPage = 1; }
    public function setExpiringPage(int $page): void { $this->expiringPage = max(1, $page); }
    public function setUpcomingPage(int $page): void { $this->upcomingPage = max(1, $page); }

    public function render()
    {
        return view('livewire.stats.owner-dashboard', [
            'kpi' => $this->kpi,
            'receivables' => $this->receivables,
            'topClients' => $this->topClientsMode === 'revenue' ? $this->topClientsByRevenue : $this->topClientsByTrips,
            'topTrucks' => $this->topTrucksMode === 'revenue' ? $this->topTrucksByRevenue : $this->topTrucksByTrips,
            'downtime' => $this->downtime,
            'expiringCount' => $this->expiringCount,
            'expiringDocuments' => $this->expiringDocumentsPaginator,
            'upcomingMaintenance' => $this->upcomingMaintenancePaginator,
        ])->layout('layouts.app', [
            'title' => __('app.owner_dashboard.title'),
        ]);
    }
}
