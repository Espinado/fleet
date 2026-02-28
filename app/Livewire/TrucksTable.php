<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Truck;
use App\Models\Company;

class TrucksTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'brand';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'brand'],
        'sortDirection' => ['except' => 'asc'],
        'perPage'       => ['except' => 10],
        'page'          => ['except' => 1],
    ];

    public function updatingSearch()  { $this->resetPage(); }
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
        $trucks = Truck::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('brand', 'like', "%{$this->search}%")
                      ->orWhere('model', 'like', "%{$this->search}%")
                      ->orWhere('plate', 'like', "%{$this->search}%")
                      ->orWhere('year', 'like', "%{$this->search}%")
                      ->orWhere('vin', 'like', "%{$this->search}%")
                      ->orWhere('inspection_issued', 'like', "%{$this->search}%")
                      ->orWhere('inspection_expired', 'like', "%{$this->search}%")
                      ->orWhere('insurance_number', 'like', "%{$this->search}%")
                      ->orWhere('insurance_issued', 'like', "%{$this->search}%")
                      ->orWhere('insurance_expired', 'like', "%{$this->search}%")
                      ->orWhere('insurance_company', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // ✅ 1 запрос: имена компаний для текущей страницы
        $companyIds = $trucks->getCollection()
            ->pluck('company_id')
            ->filter()
            ->unique()
            ->values();

        $companiesById = Company::query()
            ->whereIn('id', $companyIds)
            ->pluck('name', 'id'); // [id => name]

        $trucks->getCollection()->transform(function ($truck) use ($companiesById) {
            // ✅ совместимость: если где-то ещё осталось поле "company"
            $companyId = (int) ($truck->company_id ?? $truck->company ?? 0);

            $truck->company_name = $companyId
                ? ($companiesById[$companyId] ?? '—')
                : '—';

            return $truck;
        });

        return view('livewire.trucks-table', [
            'items' => $trucks,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ])->layout('layouts.app', [
            'title' => 'Trucks'
        ]);
    }
}
