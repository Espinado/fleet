<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoicesTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all'; // all|paid|partial|unpaid
    public int $perPage = 20;

    public string $sortBy = 'issued_at';
    public string $sortDir = 'desc';

    protected $queryString = [
        'search'  => ['except' => ''],
        'status'  => ['except' => 'all'],
        'perPage' => ['except' => 20],
        'sortBy'  => ['except' => 'issued_at'],
        'sortDir' => ['except' => 'desc'],
    ];

    public function updating($name, $value): void
    {
        // Сбрасываем страницу при смене фильтров
        if (in_array($name, ['search', 'status', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function sort(string $field): void
    {
        $allowedSorts = ['issued_at', 'invoice_no', 'total', 'paid_total', 'last_paid_at'];
        if (!in_array($field, $allowedSorts, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortBy = $field;
        $this->sortDir = 'asc';
    }

    public function render()
    {
        $q = Invoice::query()
            ->with(['payer'])
            ->select('invoices.*')
            ->selectSub(function ($sub) {
                $sub->from('invoice_payments')
                    ->selectRaw('COALESCE(SUM(amount),0)')
                    ->whereColumn('invoice_id', 'invoices.id');
            }, 'paid_total')
            ->selectSub(function ($sub) {
                $sub->from('invoice_payments')
                    ->selectRaw('MAX(paid_at)')
                    ->whereColumn('invoice_id', 'invoices.id');
            }, 'last_paid_at');

        // Search: invoice_no / payer company_name / payer reg_nr
        if (filled($this->search)) {
            $s = '%' . trim($this->search) . '%';

            $q->where(function ($w) use ($s) {
                $w->where('invoices.invoice_no', 'like', $s)
                    ->orWhereHas('payer', function ($p) use ($s) {
                        $p->where('company_name', 'like', $s)
                          ->orWhere('reg_nr', 'like', $s);
                    });
            });
        }

        // Status filter via HAVING on computed paid_total
        if ($this->status === 'paid') {
            $q->havingRaw('paid_total >= total');
        } elseif ($this->status === 'partial') {
            $q->havingRaw('paid_total > 0 AND paid_total < total');
        } elseif ($this->status === 'unpaid') {
            $q->havingRaw('paid_total = 0');
        }

        $sortBy  = in_array($this->sortBy, ['issued_at', 'invoice_no', 'total', 'paid_total', 'last_paid_at'], true)
            ? $this->sortBy
            : 'issued_at';

        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $rows = $q->orderBy($sortBy, $sortDir)->paginate($this->perPage);

        return view('livewire.invoices.invoices-table', [
            'rows' => $rows,
        ])->layout('layouts.app', [
        'title' => 'Invoices'
    ]);
    }
}
