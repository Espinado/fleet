<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Driver;

class DriversTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'last_name'; // сортировка по умолчанию
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'last_name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function sortBy($field): void
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
        $drivers = Driver::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('pers_code', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('license_number', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.drivers-table', [
            'items' => $drivers,
        ])->layout('layouts.app');
    }
}
