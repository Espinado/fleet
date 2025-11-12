<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Truck;

class TrucksTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'brand'; // сортировка по умолчанию
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'brand'],
        'sortDirection' => ['except' => 'asc'],
        'perPage'       => ['except' => 10],
        'page'          => ['except' => 1],
    ];

    // 🔁 Автосброс страницы при изменении фильтров
    public function updatingSearch()  { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    // 🔽 Логика сортировки
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

        // 🔧 Подставляем название компании из конфига
        $trucks->getCollection()->transform(function ($truck) {
            $truck->company_name = config('companies')[$truck->company]['name'] ?? '-';
            return $truck;
        });

        return view('livewire.trucks-table', [
            'items' => $trucks,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ])->layout('layouts.app');
    }
}
