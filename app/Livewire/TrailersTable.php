<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Trailer;

class TrailersTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'brand'; // сортировка по умолчанию
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
        $trailers =Trailer::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('brand', 'like', "%{$this->search}%")
                      ->orWhere('plate', 'like', "%{$this->search}%")
                      ->orWhere('year', 'like', "%{$this->search}%")
                      ->orWhere('inspection_issued', 'like', "%{$this->search}%")
                      ->orWhere('inspection_expired', 'like', "%{$this->search}%")
                       ->orWhere('insurance_number', 'like', "%{$this->search}%")
                         ->orWhere('insurance_issued', 'like', "%{$this->search}%")
                       ->orWhere('insurance_expired', 'like', "%{$this->search}%")
                        ->orWhere('insurance_company', 'like', "%{$this->search}%")
                         ->orWhere('tir_issued', 'like', "%{$this->search}%")
                        ->orWhere('tir_expired', 'like', "%{$this->search}%")
                         ->orWhere('tech_passport_nr', 'like', "%{$this->search}%")
                        ->orWhere('tech_passport_issued', 'like', "%{$this->search}%")
                         ->orWhere('tech_passport_expired', 'like', "%{$this->search}%")
                      ->orWhere('vin', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
              $trailers->getCollection()->transform(function ($trailer) {
            $trailer->company_name = config('companies')[$trailer->company]['name'] ?? '-';
            return $trailer;
        });


        return view('livewire.trailers-table', [
            'items' => $trailers,
        ])->layout('layouts.app');;
    }
}
