<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum as EnumRule;

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;

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

    // ✅ ручной одометр (используем только для fuel)
    public ?float $manualOdometerKm = null;

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
            'category'     => ['required', new EnumRule(TripExpenseCategory::class)],
            'description'  => 'nullable|string|max:2000',
            'amount'       => 'required|numeric|min:0.01|max:99999',
            'expense_date' => 'required|date',
            'file'         => 'nullable|file|max:51200', // 50mb

            // manual odometer (обязательность проверяем логикой ниже)
            'manualOdometerKm' => ['nullable', 'numeric', 'min:0', 'max:3000000'],
        ];
    }

    public function updatedCategory(): void
    {
        $this->manualOdometerKm = null;
        $this->resetErrorBag('manualOdometerKm');
    }

    public function saveExpense(): void
    {
        $this->validate();

        $driver = Auth::user()?->driver;
        if (!$driver) {
            redirect()->route('driver.login')->send();
            return;
        }

        $isFuel = $this->category === TripExpenseCategory::FUEL->value;

        // ✅ Fuel требует odometer + truck
        if ($isFuel) {
            if ($this->manualOdometerKm === null || $this->manualOdometerKm <= 0) {
                $this->addError('manualOdometerKm', 'Ievadiet odometru (km).');
                return;
            }

            if (!$this->trip->truck) {
                $this->addError('manualOdometerKm', 'В рейсе не найден truck.');
                return;
            }
        }

        DB::transaction(function () use ($driver, $isFuel) {

            // файл
            $path = $this->file
                ? $this->file->store("trip_expenses/{$this->trip->id}", 'public')
                : null;

            // =========================================================
            // 1) Создаём TripExpense (финансовая запись)
            // =========================================================
            $expenseData = [
                'trip_id'      => $this->trip->id,
                'category'     => $this->category,
                'description'  => $this->description,
                'amount'       => $this->amount,
                'currency'     => 'EUR',
                'file_path'    => $path,
                'expense_date' => $this->expense_date,
                'created_by'   => Auth::id(),
            ];

            if ($isFuel) {
                $expenseData['odometer_km'] = (float) $this->manualOdometerKm;
                $expenseData['odometer_source'] = 'manual';
            }

            $expense = TripExpense::create($expenseData);

            // =========================================================
            // 2) Для fuel создаём TruckOdometerEvent и связываем 1:1
            // =========================================================
            if ($isFuel) {
                $truck = $this->trip->truck;
                $odometerKm = (float) $this->manualOdometerKm;

                // антидубль (на случай двойного клика) — берём существующий event если есть
                $existingEventId = TruckOdometerEvent::query()
                    ->where('truck_id', $truck->id)
                    ->where('driver_id', $driver->id)
                    ->where('type', TruckOdometerEvent::TYPE_FUEL)
                    ->where('odometer_km', $odometerKm)
                    ->where('occurred_at', '>=', now()->subMinutes(2))
                    ->value('id');

                if ($existingEventId) {
                    $expense->update([
                        'truck_odometer_event_id' => $existingEventId,
                    ]);
                    return;
                }

                $event = TruckOdometerEvent::create([
                    'truck_id'      => $truck->id,
                    'driver_id'     => $driver->id,
                    'type'          => TruckOdometerEvent::TYPE_FUEL,
                    'odometer_km'   => $odometerKm,

                    // ✅ ручной ввод — это SOURCE_MANUAL
                    'source'        => TruckOdometerEvent::SOURCE_MANUAL,

                    'occurred_at'   => now(),
                    'mapon_at'      => null,
                    'is_stale'      => false,
                    'stale_minutes' => null,
                    'raw'           => null,
                    'note'          => "Fuel expense #{$expense->id}",
                ]);

                $expense->update([
                    'truck_odometer_event_id' => $event->id,
                ]);
            }
        });

        $this->reset(['description', 'amount', 'expense_date', 'file', 'manualOdometerKm']);
        $this->resetErrorBag();

        session()->flash('success', 'Izdevums pievienots!');
    }

    public function render()
    {
        $expenses = TripExpense::where('trip_id', $this->trip->id)
            ->latest()
            ->get();

        return view('livewire.driver-app.driver-trip-expenses', [
            'expenses'   => $expenses,
            'categories' => TripExpenseCategory::options(),
            'total'      => $expenses->sum('amount'),
        ]);
    }
}
