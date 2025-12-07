<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Enums\TripExpenseCategory;

class DriverTripExpenses extends Component
{
    use WithFileUploads;

    public Trip $trip;

    // поля формы
    public string $category = 'fuel';
    public string $description = '';
    public float|string $amount = '';
    public string $expense_date = '';
    public $file = null;

    public function mount(Trip $trip)
    {
        if (!Auth::user()?->driver) {
            return redirect()->route('driver.login');
        }

        $this->trip = $trip;
        $this->category = TripExpenseCategory::FUEL->value;
    }

    protected function rules()
    {
        return [
            'category'     => ['required', new Enum(TripExpenseCategory::class)],
            'description'  => 'nullable|string|max:2000',
            'amount'       => 'required|numeric|min:0.01|max:99999',
            'expense_date' => 'required|date',
            'file'         => 'nullable|file|max:51200', // 50mb
        ];
    }

    public function saveExpense()
    {
        $this->validate();

        $path = $this->file
            ? $this->file->store("trip_expenses/{$this->trip->id}", 'public')
            : null;

        TripExpense::create([
            'trip_id'        => $this->trip->id,
            'category'       => $this->category,
            'description'    => $this->description,
            'amount'         => $this->amount,
            'expense_date'   => $this->expense_date,
            'file_path'      => $path,
            // 'uploader_driver_id' => Auth::user()->driver->id,
        ]);

        $this->reset(['description', 'amount', 'expense_date', 'file']);

        session()->flash('success', 'Izdevums pievienots!');
    }

    // public function delete($id)
    // {
    //     $exp = TripExpense::where('id', $id)
    //         ->where('uploader_driver_id', Auth::user()->driver->id)
    //         ->firstOrFail();

    //     if ($exp->file_path && \Storage::disk('public')->exists($exp->file_path)) {
    //         \Storage::disk('public')->delete($exp->file_path);
    //     }

    //     $exp->delete();

    //     session()->flash('success', 'Izdevums dzēsts!');
    // }

    public function render()
    {
        $expenses = TripExpense::where('trip_id', $this->trip->id)
          
            ->latest()
            ->get();

        return view('livewire.driver-app.driver-trip-expenses', [
            'expenses' => $expenses,
            'categories' => TripExpenseCategory::options(),
            'total' => $expenses->sum('amount'),
        ]);
    }
}
