<?php

namespace App\Livewire\Stats;

use App\Enums\TripExpenseCategory;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripExpense;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    protected $queryString = [
        'dateFrom' => ['except' => null],
        'dateTo'   => ['except' => null],
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

        return (object) [
            'revenue'  => round((float) $revenue, 2),
            'expenses' => round((float) $expenses, 2),
            'profit'   => round((float) $revenue - (float) $expenses, 2),
            'trips_count' => $tripIds->count(),
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

    public function render()
    {
        return view('livewire.stats.owner-dashboard', [
            'kpi' => $this->kpi,
            'receivables' => $this->receivables,
            'topClients' => $this->topClientsMode === 'revenue' ? $this->topClientsByRevenue : $this->topClientsByTrips,
            'topTrucks' => $this->topTrucksMode === 'revenue' ? $this->topTrucksByRevenue : $this->topTrucksByTrips,
            'downtime' => $this->downtime,
            'expiringCount' => $this->expiringCount,
        ])->layout('layouts.app', [
            'title' => __('app.owner_dashboard.title'),
        ]);
    }
}
