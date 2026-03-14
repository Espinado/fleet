<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TripExpense;
use App\Enums\TripExpenseCategory;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;

class TripExpensesSection extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $trip;

    public $category = 'fuel';
    public $description;
    public $amount;
    public $currency = 'EUR';
    public $expense_date;
    public $search = '';
    public $sortField = 'expense_date';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $expenseFile;

    protected $rules = [
        'category'     => 'required|string',
        'amount'       => 'required|numeric|min:0',
        'description'  => 'nullable|string|max:255',
        'expense_date' => 'nullable|date',
        'expenseFile'  => 'nullable|file|mimes:jpg,jpeg,png,gif,webp',
    ];

    public function saveExpense()
    {
        $this->validate();

        $path = $this->expenseFile
            ? \App\Helpers\ImageCompress::storeUpload($this->expenseFile, "trip_expenses/trip_{$this->trip->id}", 'public')
            : null;

        TripExpense::create([
            'trip_id'     => $this->trip->id,
            'category'    => $this->category,
            'description' => $this->description,
            'amount'      => $this->amount,
            'currency'    => $this->currency,
            'expense_date'=> $this->expense_date,
            'file_path'   => $path,
            'created_by'  => auth()->id(),
        ]);

        $this->reset(['description', 'amount', 'expense_date', 'expenseFile']);
        $this->category = 'fuel';

        session()->flash('success', '💶 Izdevumi veiksmīgi pievienoti.');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function getFilteredExpensesProperty()
    {
        $search = $this->search;

        $q = $this->trip->expenses();

        // Не показываем оплату фрахта третьей стороне в списке расходов:
        // это TripExpense с supplier_company_id = carrier_company_id,
        // когда carrier помечен как third party.
        $carrier = $this->trip->carrierCompany;
        if ($carrier && $carrier->is_third_party) {
            $carrierId = (int) $carrier->id;
            $q->where(function ($qq) use ($carrierId) {
                $qq->whereNull('supplier_company_id')
                   ->orWhere('supplier_company_id', '!=', $carrierId);
            });
        }

        return $q
            ->when($search, function ($q) use ($search) {

                $labelCases = collect(\App\Enums\TripExpenseCategory::cases())
                    ->map(function ($case) {
                        return "WHEN category = '{$case->value}' THEN '" . addslashes($case->label()) . "'";
                    })->implode(' ');

                $q->where(function ($sub) use ($search, $labelCases) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhereRaw(
                            "(CASE {$labelCases} END) LIKE ?",
                            ["%{$search}%"]
                        );
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /** 🔥 Сумма только видимых (отфильтрованных) данных */
    public function getFilteredTotalProperty()
    {
        return $this->filteredExpenses->sum('amount');
    }

    /** 🔥 Полная сумма всех расходов по рейсу */
    public function getTotalAllProperty()
    {
        $q = $this->trip->expenses();

        $carrier = $this->trip->carrierCompany;
        if ($carrier && $carrier->is_third_party) {
            $carrierId = (int) $carrier->id;
            $q->where(function ($qq) use ($carrierId) {
                $qq->whereNull('supplier_company_id')
                   ->orWhere('supplier_company_id', '!=', $carrierId);
            });
        }

        return $q->sum('amount');
    }

    /** 🔥 Проверка: применён ли поиск, фильтр, сортировка или лимит */
    public function getIsFilteredProperty()
    {
        return $this->search !== '' ||
               $this->sortField !== 'expense_date' ||
               $this->sortDirection !== 'desc' ||
               $this->perPage != 10;
    }

    public function delete($id)
    {
        $exp = TripExpense::findOrFail($id);
        abort_if((int) $exp->trip_id !== (int) $this->trip->id, 403);

        if ($exp->file_path) {
            Storage::disk('public')->delete($exp->file_path);
        }

        $exp->delete();
    }

    public function render()
    {
        return view('livewire.trips.trip-expenses-section', [
            'categories' => TripExpenseCategory::options(),
        ]);
    }
}
