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
    public int    $perPage = 10;
    public string $sortField = 'start';
    public string $sortDirection = 'asc';

    protected $queryString = [
        'search'        => ['except' => ''],
        'status'        => ['except' => ''],
        'perPage'       => ['except' => 10],
        'sortField'     => ['except' => 'start'],
        'sortDirection' => ['except' => 'asc'],
        'page'          => ['except' => 1],
    ];

    public function updatingSearch()  { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    private function applySorting($query)
    {
        return match ($this->sortField) {
            'start' => $query->orderByRaw("
                (SELECT MIN(date) FROM trip_steps s WHERE s.trip_id = trips.id AND s.type='loading') {$this->sortDirection}
            "),

            'stop' => $query->orderByRaw("
                (SELECT MAX(date) FROM trip_steps s WHERE s.trip_id = trips.id AND s.type='unloading') {$this->sortDirection}
            "),

            'expeditor' => $query->orderBy('expeditor_name', $this->sortDirection),

            'driver' => $query->orderByRaw("
                (SELECT last_name FROM drivers d WHERE d.id = trips.driver_id LIMIT 1) {$this->sortDirection}
            "),

            'route' => $query->orderBy('id', $this->sortDirection), // упрощённо

            'status' => $query->orderBy('status', $this->sortDirection),

            default => $query->orderBy('id', 'desc'),
        };
    }

    public function render()
    {
        $query = Trip::query()
            ->with([
                'steps.client',
                'driver',
                'truck',
                'trailer',
                'cargos',
            ])
            ->when($this->search, function ($q) {
                $q->where('expeditor_name', 'like', "%{$this->search}%")
                  ->orWhereHas('steps.client', fn($c) =>
                      $c->where('company_name', 'like', "%{$this->search}%")
                  );
            })
            ->when($this->status, fn($q) =>
                $q->where('status', $this->status)
            );

        $trips = $this->applySorting($query)
            ->paginate($this->perPage);

        return view('livewire.trips-table', compact('trips'))
            ->layout('layouts.app', [
        'title' => 'Trips'
    ]);
    }
}
