<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Trip;
use App\Models\Client;

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

            'route' => $query->orderBy('id', $this->sortDirection),

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
                'carrierCompany:id,name,is_third_party',
            ])
            ->when($this->search, function ($q) {
                $q->where('expeditor_name', 'like', "%{$this->search}%")
                  ->orWhereHas('steps.client', fn($c) =>
                      $c->where('company_name', 'like', "%{$this->search}%")
                  );
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status));

        $trips = $this->applySorting($query)->paginate($this->perPage);

        /**
         * ✅ Build clients list from cargos for the current page (stable for 3rd party too)
         * - Collect all customer/shipper/consignee ids across trips on page
         * - 1 query to clients table
         * - Attach computed array $trip->clients_list to each trip
         */
        $clientIds = collect();

        foreach ($trips->getCollection() as $trip) {
            foreach (($trip->cargos ?? []) as $cargo) {
                $clientIds->push($cargo->customer_id);
                $clientIds->push($cargo->shipper_id);
                $clientIds->push($cargo->consignee_id);
            }
        }

        $clientIds = $clientIds->filter()->unique()->values();

        $clientsById = $clientIds->isNotEmpty()
            ? Client::query()->whereIn('id', $clientIds)->pluck('company_name', 'id')
            : collect();

        $trips->getCollection()->transform(function ($trip) use ($clientsById) {
            $names = collect();

            foreach (($trip->cargos ?? []) as $cargo) {
                foreach (['customer_id', 'shipper_id', 'consignee_id'] as $field) {
                    $id = (int)($cargo->{$field} ?? 0);
                    if ($id && isset($clientsById[$id])) {
                        $names->push($clientsById[$id]);
                    }
                }
            }

            $trip->clients_list = $names->filter()->unique()->values()->all();

            return $trip;
        });

        return view('livewire.trips-table', compact('trips'))
            ->layout('layouts.app', [
                'title' => 'Trips'
            ]);
    }
}
