<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\InvoicePayment;
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

    // Inline payment form state
    public ?int $paymentInvoiceId = null;
    public ?string $payment_date = null;
    public ?string $payment_amount = null;

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

    public function startAddPayment(int $invoiceId): void
    {
        $this->paymentInvoiceId = $invoiceId;

        $invoice = Invoice::find($invoiceId);

        $this->payment_date = now()->toDateString();
        // по умолчанию — текущий баланс, но можно изменить вручную
        $this->payment_amount = $invoice ? (string) $invoice->balance : null;

        $this->resetErrorBag(['payment_date', 'payment_amount']);
    }

    public function cancelAddPayment(): void
    {
        $this->paymentInvoiceId = null;
        $this->payment_date = null;
        $this->payment_amount = null;

        $this->resetErrorBag(['payment_date', 'payment_amount']);
    }

    public function savePayment(): void
    {
        if (!$this->paymentInvoiceId) {
            return;
        }

        $data = $this->validate([
            'payment_date'   => ['required', 'date'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
        ], [], [
            'payment_date'   => 'payment date',
            'payment_amount' => 'payment amount',
        ]);

        $invoice = Invoice::with('trip')->findOrFail($this->paymentInvoiceId);
        $user = auth()->user();
        if ($user && !$user->isAdmin() && $user->company_id !== null) {
            abort_if(
                !$invoice->trip || (int) $invoice->trip->carrier_company_id !== (int) $user->company_id,
                403
            );
        }

        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'paid_at'    => $data['payment_date'],
            'amount'     => (float) $data['payment_amount'],
            'currency'   => $invoice->currency ?? 'EUR',
        ]);

        $this->cancelAddPayment();

        session()->flash('success', __('app.inv.toast_payment_added'));
    }

    public function render()
    {
        $user = auth()->user();
        $q = Invoice::query()
            ->with(['payer', 'payments'])
            ->select('invoices.*');

        if ($user && $user->isAdmin()) {
            // Админ видит все инвойсы
        } elseif ($user && $user->company_id !== null) {
            $q->whereHas('trip', fn ($sub) => $sub->where('carrier_company_id', (int) $user->company_id));
        } else {
            $q->whereRaw('1 = 0');
        }
        $q
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
            'title' => __('app.inv.title'),
        ]);
    }
}
