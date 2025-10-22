<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Trip;

class TripsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public int $perPage = 10;
    public string $sortField = 'start_date';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'start_date'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }
    public function updatingPerPage(){ $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $trips = Trip::query()
            ->with(['client','driver','truck','trailer'])
            ->when($this->search, fn($q) =>
                $q->where('expeditor_name', 'like', "%{$this->search}%")
                  ->orWhere('cargo', 'like', "%{$this->search}%")
                  ->orWhere('route_from', 'like', "%{$this->search}%")
                  ->orWhere('route_to', 'like', "%{$this->search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company_name','like',"%{$this->search}%"))
            )
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.trips-table', compact('trips'))
            ->layout('layouts.app')
            ->title('Trips');
    }
}
