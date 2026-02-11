<?php

namespace App\Livewire\Stats;

use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TruckOdometerEvent;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class TripsStatsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public ?string $dateFrom = null; // Y-m-d
    public ?string $dateTo   = null; // Y-m-d

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
        if (!$this->dateFrom && !$this->dateTo) {
            $this->dateFrom = now()->startOfMonth()->toDateString();
            $this->dateTo   = now()->endOfMonth()->toDateString();
        }
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

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

        /**
         * ✅ Показываем "начавшиеся" в диапазоне
         * (если хочешь вообще без фильтра — просто удали эти if)
         */
        if ($this->dateFrom) {
            $q->whereDate('trips.start_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $q->whereDate('trips.start_date', '<=', $this->dateTo);
        }

        // ✅ Поиск
        if (($s = trim($this->search)) !== '') {
            $q->where(function (Builder $qq) use ($s) {
                if (is_numeric($s)) {
                    $qq->orWhere('trips.id', (int)$s);
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

        // ✅ Фрахт (если грузов нет -> 0)
        $q->addSelect([
            'freight_total' => TripCargo::query()
                ->selectRaw('COALESCE(SUM(price), 0)')
                ->whereColumn('trip_id', 'trips.id'),
        ]);

        // ✅ Odometer events (опционально)
       // ✅ более надёжные границы (MySQL)
$rangeStart = "TIMESTAMP(trips.start_date, '00:00:00')";
$rangeEnd   = "TIMESTAMP(trips.end_date, '23:59:59')";

$q->addSelect([
    'departure_at' => TruckOdometerEvent::query()
        ->select('occurred_at')
        ->whereColumn('truck_id', 'trips.truck_id')
        ->where('type', TruckOdometerEvent::TYPE_DEPARTURE)
        ->whereRaw("occurred_at >= {$rangeStart}")
        ->whereRaw("occurred_at <= {$rangeEnd}")
        ->orderBy('occurred_at', 'asc')
        ->limit(1),

    'departure_odometer' => TruckOdometerEvent::query()
        ->select('odometer_km')
        ->whereColumn('truck_id', 'trips.truck_id')
        ->where('type', TruckOdometerEvent::TYPE_DEPARTURE)
        ->whereRaw("occurred_at >= {$rangeStart}")
        ->whereRaw("occurred_at <= {$rangeEnd}")
        ->orderBy('occurred_at', 'asc')
        ->limit(1),

    'return_at' => TruckOdometerEvent::query()
        ->select('occurred_at')
        ->whereColumn('truck_id', 'trips.truck_id')
        ->where('type', TruckOdometerEvent::TYPE_RETURN)
        ->whereRaw("occurred_at >= {$rangeStart}")
        ->whereRaw("occurred_at <= {$rangeEnd}")
        ->orderBy('occurred_at', 'desc')
        ->limit(1),

    'return_odometer' => TruckOdometerEvent::query()
        ->select('odometer_km')
        ->whereColumn('truck_id', 'trips.truck_id')
        ->where('type', TruckOdometerEvent::TYPE_RETURN)
        ->whereRaw("occurred_at >= {$rangeStart}")
        ->whereRaw("occurred_at <= {$rangeEnd}")
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
            'departure_at',
            'return_at',
        ];

        $sortField = in_array($this->sortField, $allowedSort, true) ? $this->sortField : 'id';
        $sortDir   = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $rows = $this->query()
            ->orderBy($sortField, $sortDir)
            ->paginate(15);

        return view('livewire.stats.trips-stats-table', compact('rows'))
            ->title('Stats')
            ->layout('layouts.app');
    }
}
