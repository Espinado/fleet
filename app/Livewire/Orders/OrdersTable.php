<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Models\TransportOrder;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public int $perPage = 10;
    public string $sortField = 'order_date';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search'        => ['except' => ''],
        'status'        => ['except' => ''],
        'perPage'       => ['except' => 10],
        'sortField'     => ['except' => 'order_date'],
        'sortDirection' => ['except' => 'desc'],
        'page'          => ['except' => 1],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
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
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = TransportOrder::query()
            ->with(['expeditor:id,name', 'customer:id,company_name', 'trip:id']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('expeditor', fn ($e) => $e->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', '%' . $this->search . '%'));
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $allowedSort = ['number', 'order_date', 'status', 'quoted_price', 'created_at'];
        if (in_array($this->sortField, $allowedSort, true)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->orderBy('order_date', 'desc')->orderBy('id', 'desc');
        }

        $orders = $query->paginate($this->perPage);

        return view('livewire.orders.orders-table', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ])->layout('layouts.app');
    }
}
