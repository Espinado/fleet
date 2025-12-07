<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TripExpense;
use App\Enums\TripExpenseCategory;
use Illuminate\Support\Facades\Storage;

class TripExpensesSection extends Component
{
    use WithFileUploads;

    public $trip;

    public $category = 'fuel';
    public $description;
    public $amount;
    public $currency = 'EUR';
    public $expense_date;

    // ⭐ исправлено — НЕ $file
    public $expenseFile;

    protected $rules = [
        'category'     => 'required|string',
        'amount'       => 'required|numeric|min:0',
        'description'  => 'nullable|string|max:255',
        'expense_date' => 'nullable|date',
        'expenseFile'  => 'nullable|file|max:10240',
    ];

    public function saveExpense()
    {
        $this->validate();

        // ⭐ Загружаем файл (если есть)
        $path = $this->expenseFile
            ? $this->expenseFile->store("trip_expenses/trip_{$this->trip->id}", 'public')
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

        // ⭐ сбрасываем только нужные поля
        $this->reset(['description', 'amount', 'expense_date', 'expenseFile']);
        $this->category = 'fuel';

        session()->flash('success', '💶 Izdevumi veiksmīgi pievienoti.');
    }

    public function delete($id)
    {
        $exp = TripExpense::findOrFail($id);

        if ($exp->file_path) {
            Storage::disk('public')->delete($exp->file_path);
        }

        $exp->delete();
    }

    public function render()
    {
        $expenses = TripExpense::where('trip_id', $this->trip->id)
            ->orderBy('expense_date', 'desc')
            ->get();

        return view('livewire.trips.trip-expenses-section', [
            'expenses'   => $expenses,
            'total'      => $expenses->sum('amount'),
            'categories' => TripExpenseCategory::options(),
        ]);
    }
}
