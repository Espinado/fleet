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
    public $file;

    protected $rules = [
        'category' => 'required|string',
        'amount' => 'required|numeric|min:0',
        'description' => 'nullable|string|max:255',
        'expense_date' => 'nullable|date',
        'file' => 'nullable|file|max:10240',
    ];

    public function saveExpense()
    {
        $this->validate();

        $path = $this->file
            ? $this->file->store("trip_expenses/trip_{$this->trip->id}", 'public')
            : null;

        TripExpense::create([
            'trip_id' => $this->trip->id,
            'category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'expense_date' => $this->expense_date,
            'file_path' => $path,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['category', 'description', 'amount', 'expense_date', 'file']);
        session()->flash('success', '💶 Izdevumi veiksmīgi pievienoti.');
    }

    public function delete($id)
    {
        $exp = TripExpense::findOrFail($id);
        Storage::disk('public')->delete($exp->file_path);
        $exp->delete();
    }

    public function render()
    {
        $expenses = TripExpense::where('trip_id', $this->trip->id)
            ->orderBy('expense_date', 'desc')
            ->get();

        $total = $expenses->sum('amount');
        $categories = TripExpenseCategory::options();

        return view('livewire.trips.trip-expenses-section', compact('expenses', 'total', 'categories'));
    }
}
