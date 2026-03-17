<?php

namespace App\Livewire\Maintenance;

use App\Models\VehicleMaintenance;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class MaintenanceRecordsJournal extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public string $sortField = 'performed_at';
    public string $sortDir = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'sortField' => ['except' => 'performed_at'],
        'sortDir' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }
    public function updatingSortField(): void { $this->resetPage(); }
    public function updatingSortDir(): void { $this->resetPage(); }

    private function baseQuery(): Builder
    {
        $user = auth()->user();
        $query = VehicleMaintenance::query()
            ->with(['truck:id,brand,model,plate', 'trailer:id,brand,plate']);

        if ($user && !$user->isAdmin() && $user->company_id !== null) {
            $query->where('company_id', $user->company_id);
        }

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('truck', fn ($t) => $t->where('plate', 'like', $term)
                    ->orWhere('brand', 'like', $term)
                    ->orWhere('model', 'like', $term))
                    ->orWhereHas('trailer', fn ($t) => $t->where('plate', 'like', $term)
                        ->orWhere('brand', 'like', $term));
            });
        }

        if ($this->dateFrom !== null && $this->dateFrom !== '') {
            $query->whereDate('performed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo !== null && $this->dateTo !== '') {
            $query->whereDate('performed_at', '<=', $this->dateTo);
        }

        if ($this->sortField === 'vehicle_name') {
            $query->leftJoin('trucks', function ($j) {
                $j->on('vehicle_maintenance.truck_id', '=', 'trucks.id');
            })
                ->leftJoin('trailers', function ($j) {
                    $j->on('vehicle_maintenance.trailer_id', '=', 'trailers.id');
                })
                ->select('vehicle_maintenance.*')
                ->orderByRaw('COALESCE(CONCAT(trucks.brand, trucks.model, trucks.plate), CONCAT(trailers.brand, trailers.plate)) ' . ($this->sortDir === 'desc' ? 'DESC' : 'ASC'));
        } else {
            $query->orderBy('performed_at', $this->sortDir);
        }

        return $query;
    }

    public function render()
    {
        $query = $this->baseQuery();
        $totalCost = (clone $query)->sum('vehicle_maintenance.cost');
        $records = (clone $query)->paginate(20);

        return view('livewire.maintenance.maintenance-records-journal', [
            'records' => $records,
            'totalCost' => round((float) $totalCost, 2),
        ])->layout('layouts.app', ['title' => __('app.maintenance_record.journal_title')]);
    }
}
