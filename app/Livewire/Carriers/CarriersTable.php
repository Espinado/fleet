<?php

namespace App\Livewire\Carriers;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;

class CarriersTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['name', 'country', 'trips_count']) ? 'asc' : 'desc';
        }
    }

    public function render()
    {
        $query = Company::query()
            ->where('type', 'carrier')
            ->where('is_third_party', true)
            ->withCount('trips');

        if ($this->search !== '') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', '%' . $s . '%')
                    ->orWhere('reg_nr', 'like', '%' . $s . '%')
                    ->orWhere('country', 'like', '%' . $s . '%')
                    ->orWhere('contact_person', 'like', '%' . $s . '%')
                    ->orWhere('email', 'like', '%' . $s . '%')
                    ->orWhere('phone', 'like', '%' . $s . '%');
            });
        }

        // rating пока скрыт в UI
        $allowedSort = ['name', 'country', 'reg_nr', 'trips_count'];
        $sortField = in_array($this->sortField, $allowedSort) ? $this->sortField : 'name';
        $carriers = $query->orderBy($sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.carriers.carriers-table', [
            'carriers' => $carriers,
        ])->layout('layouts.app', [
            'title' => __('app.carriers.title'),
        ]);
    }
}
