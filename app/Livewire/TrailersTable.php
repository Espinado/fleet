<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Trailer;
use App\Models\Company;

class TrailersTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'brand';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'brand'],     // ✅ было last_name
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
        $trailers = Trailer::query()
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

        // ✅ 1 запрос на компании для текущей страницы
        $companyIds = $trailers->getCollection()
            ->map(fn ($tr) => (int) ($tr->company_id ?? $tr->company ?? 0))
            ->filter()
            ->unique()
            ->values();

        $companiesById = Company::query()
            ->whereIn('id', $companyIds)
            ->pluck('name', 'id'); // [id => name]

        $trailers->getCollection()->transform(function ($trailer) use ($companiesById) {
            $companyId = (int) ($trailer->company_id ?? $trailer->company ?? 0);

            $trailer->company_name = $companyId
                ? ($companiesById[$companyId] ?? '—')
                : '—';

            return $trailer;
        });

        return view('livewire.trailers-table', [
            'items' => $trailers,
        ])->layout('layouts.app', [
            'title' => 'Trailers'
        ]);
    }
}
