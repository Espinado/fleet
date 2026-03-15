<?php

namespace App\Livewire\Stats;

use App\Models\Client;
use App\Models\TripCargo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ClientsStatsTable extends Component
{
    public string $search = '';
    public string $sortField = 'freight_total';
    public string $sortDirection = 'desc';

    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'freight_total'],
        'sortDirection' => ['except' => 'desc'],
        'dateFrom'      => ['except' => null],
        'dateTo'        => ['except' => null],
    ];

    public function updatedSearch(): void {}
    public function updatedDateFrom(): void {}
    public function updatedDateTo(): void {}

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function clearDates(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
    }

    public function quickRange(int $days): void
    {
        $this->dateFrom = Carbon::now()->subDays($days)->toDateString();
        $this->dateTo   = Carbon::now()->toDateString();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['freight_total', 'trips_count', 'cargos_count']) ? 'desc' : 'asc';
        }
    }

    private function baseTripQuery(): Builder
    {
        return TripCargo::query()->whereHas('trip', function (Builder $t) {
            if ($this->dateFrom) {
                $t->whereDate('trips.start_date', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $t->whereDate('trips.start_date', '<=', $this->dateTo);
            }
        });
    }

    private function applySearch(Builder $q): void
    {
        $search = trim($this->search);
        if ($search !== '') {
            $q->whereHas('customer', function (Builder $c) use ($search) {
                $c->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('reg_nr', 'like', '%' . $search . '%');
            });
        }
    }

    public function getRowsProperty()
    {
        $q = $this->baseTripQuery();
        $this->applySearch($q);

        $rows = $q
            ->select([
                'trip_cargos.customer_id',
                DB::raw('COUNT(trip_cargos.id) as cargos_count'),
                DB::raw('COUNT(DISTINCT trip_cargos.trip_id) as trips_count'),
                DB::raw('COALESCE(SUM(trip_cargos.price_with_tax), 0) as freight_total'),
            ])
            ->groupBy('trip_cargos.customer_id')
            ->get();

        $clientIds = $rows->pluck('customer_id')->filter()->unique()->values()->all();
        $clients = Client::whereIn('id', $clientIds)->get()->keyBy('id');

        $rows = $rows->map(function ($row) use ($clients) {
            $row->client_name = $clients->get($row->customer_id)?->company_name ?? '—';
            $row->client_id = $row->customer_id;
            return $row;
        });

        $validSort = in_array($this->sortField, ['client_name', 'cargos_count', 'trips_count', 'freight_total']) ? $this->sortField : 'freight_total';
        $dir = strtolower($this->sortDirection) === 'asc';

        return $rows->sortBy($validSort, SORT_REGULAR, !$dir)->values();
    }

    public function getSummaryProperty(): array
    {
        $q = $this->baseTripQuery();
        $this->applySearch($q);

        $row = $q
            ->select([
                DB::raw('COUNT(DISTINCT trip_cargos.trip_id) as total_trips'),
                DB::raw('COALESCE(SUM(trip_cargos.price_with_tax), 0) as total_freight'),
            ])
            ->first();

        return [
            'total_trips'   => (int) ($row->total_trips ?? 0),
            'total_freight' => (float) ($row->total_freight ?? 0),
        ];
    }

    public function render()
    {
        return view('livewire.stats.clients-stats-table', [
            'rows'    => $this->rows,
            'summary' => $this->summary,
        ])->layout('layouts.app', [
            'title' => __('app.stats.clients.title'),
        ]);
    }
}
