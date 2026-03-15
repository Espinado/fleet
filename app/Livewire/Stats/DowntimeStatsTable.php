<?php

namespace App\Livewire\Stats;

use App\Models\Client;
use App\Models\TripCargo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class DowntimeStatsTable extends Component
{
    public string $sortField = 'trip_start_date';
    public string $sortDirection = 'desc';

    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    protected $queryString = [
        'sortField'     => ['except' => 'trip_start_date'],
        'sortDirection' => ['except' => 'desc'],
        'dateFrom'      => ['except' => null],
        'dateTo'        => ['except' => null],
    ];

    public function updatedDateFrom(): void {}
    public function updatedDateTo(): void {}

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
            $this->sortDirection = in_array($field, ['delay_amount', 'delay_days']) ? 'desc' : 'asc';
        }
    }

    private function baseQuery(): Builder
    {
        return TripCargo::query()
            ->where('has_delay', true)
            ->whereHas('trip', function (Builder $t) {
                if ($this->dateFrom) {
                    $t->whereDate('trips.start_date', '>=', $this->dateFrom);
                }
                if ($this->dateTo) {
                    $t->whereDate('trips.start_date', '<=', $this->dateTo);
                }
            })
            ->with(['trip', 'customer']);
    }

    public function getRowsProperty()
    {
        $rows = $this->baseQuery()
            ->get()
            ->map(function (TripCargo $c) {
                return (object) [
                    'trip_id'         => $c->trip_id,
                    'trip_start_date' => $c->trip?->start_date?->format('Y-m-d'),
                    'customer_name'   => $c->customer?->company_name ?? '—',
                    'customer_id'     => $c->customer_id,
                    'delay_days'      => (int) ($c->delay_days ?? 0),
                    'delay_amount'    => (float) ($c->delay_amount ?? 0),
                ];
            });

        $validSort = in_array($this->sortField, ['trip_start_date', 'customer_name', 'delay_days', 'delay_amount']) ? $this->sortField : 'trip_start_date';
        $dir = strtolower($this->sortDirection) === 'asc';
        return $rows->sortBy($validSort, SORT_REGULAR, !$dir)->values();
    }

    public function getSummaryProperty(): array
    {
        $q = $this->baseQuery();
        $row = $q->selectRaw('COALESCE(SUM(delay_days), 0) as total_days, COALESCE(SUM(delay_amount), 0) as total_amount')->first();
        return [
            'total_days'   => (int) ($row->total_days ?? 0),
            'total_amount' => (float) ($row->total_amount ?? 0),
        ];
    }

    public function render()
    {
        return view('livewire.stats.downtime-stats-table', [
            'rows'    => $this->rows,
            'summary' => $this->summary,
        ])->layout('layouts.app', [
            'title' => __('app.stats.downtime.title'),
        ]);
    }
}
