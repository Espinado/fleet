<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum as EnumRule;

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;

use App\Enums\TripExpenseCategory;
use App\Services\Services\Odometer\MaponOdometerFetcher;

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

    // Mapon snapshot (только для топлива)
    public ?float $maponOdometerKm = null;
    public ?string $maponOdometerSource = null; // can|mileage|null
    public ?string $maponAt = null;
    public ?bool $maponIsStale = null;
    public ?int $maponStaleMinutes = null;

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
        ];
    }

    public function updatedCategory(): void
    {
        $this->resetMaponSnapshot();
        $this->resetErrorBag('mapon');
    }

    private function resetMaponSnapshot(): void
    {
        $this->maponOdometerKm = null;
        $this->maponOdometerSource = null;
        $this->maponAt = null;
        $this->maponIsStale = null;
        $this->maponStaleMinutes = null;
    }

    public function fetchOdometerFromMapon(): void
    {
        $this->resetErrorBag('mapon');
        $this->resetMaponSnapshot();

        $truck = $this->trip->truck;

        if (!$truck) {
            $this->addError('mapon', 'В рейсе не найден truck.');
            return;
        }

        if (!$truck->mapon_unit_id) {
            $this->addError('mapon', 'У трака не задан mapon_unit_id.');
            return;
        }

        /** @var MaponOdometerFetcher $fetcher */
        $fetcher = app(MaponOdometerFetcher::class);

        $odo = $fetcher->fetchOdometer((int) $truck->mapon_unit_id, (int) $truck->company);

        if (!$odo || !array_key_exists('km', $odo)) {
            $this->addError('mapon', 'Не удалось получить данные из Mapon.');
            return;
        }

        if ($odo['km'] === null) {
            $this->addError('mapon', 'Mapon не вернул одометр (CAN и mileage пустые).');
            return;
        }

        $this->maponOdometerKm = (float) $odo['km'];
        $this->maponOdometerSource = $odo['source'] ?? null; // can|mileage
        $this->maponAt = $odo['mapon_at'] ?? null;

        $this->maponIsStale = (bool) ($odo['is_stale'] ?? false);
        $this->maponStaleMinutes = $odo['stale_minutes'] ?? null;
    }

    public function saveExpense()
    {
        $this->validate();

        $driver = Auth::user()?->driver;
        if (!$driver) {
            return redirect()->route('driver.login');
        }

        $isFuel = $this->category === TripExpenseCategory::FUEL->value;

        // ✅ Для топлива обязательно получаем одометр из Mapon
        if ($isFuel) {
            if ($this->maponOdometerKm === null) {
                $this->fetchOdometerFromMapon();
            }

            if ($this->getErrorBag()->has('mapon') || $this->maponOdometerKm === null) {
                return; // покажем ошибку, не сохраняем
            }
        }

        $path = $this->file
            ? $this->file->store("trip_expenses/{$this->trip->id}", 'public')
            : null;

        $expense = TripExpense::create([
            'trip_id'      => $this->trip->id,
            'category'     => $this->category,
            'description'  => $this->description,
            'amount'       => $this->amount,
            'currency'     => 'EUR',
            'file_path'    => $path,
            'expense_date' => $this->expense_date,
            'created_by'   => Auth::id(),
        ]);

        // ✅ Вариант A: одометр НЕ в TripExpense, а только в TruckOdometerEvent
        if ($isFuel) {
            $truck = $this->trip->truck;

            $sourceInt = match ($this->maponOdometerSource) {
                'can' => TruckOdometerEvent::SOURCE_CAN,
                'mileage' => TruckOdometerEvent::SOURCE_MILEAGE,
                default => TruckOdometerEvent::SOURCE_FALLBACK_LOCAL,
            };

            // антидубль на случай двойного клика
            $duplicate = TruckOdometerEvent::query()
                ->where('truck_id', $truck->id)
                ->where('driver_id', $driver->id)
                ->where('type', TruckOdometerEvent::TYPE_FUEL)
                ->where('odometer_km', (float) $this->maponOdometerKm)
                ->where('occurred_at', '>=', now()->subMinutes(2))
                ->exists();

            if (!$duplicate) {
                TruckOdometerEvent::create([
                    'truck_id'      => $truck->id,
                    'driver_id'     => $driver->id,
                    'type'          => TruckOdometerEvent::TYPE_FUEL,
                    'odometer_km'   => (float) $this->maponOdometerKm,
                    'source'        => $sourceInt,

                    // момент действия + фактическое время в Mapon
                    'occurred_at'   => now(),
                    'mapon_at'      => $this->maponAt,
                    'is_stale'      => (bool) $this->maponIsStale,
                    'stale_minutes' => $this->maponStaleMinutes,
                    'raw'           => null,
                    'note'          => "Fuel expense #{$expense->id}",
                ]);
            }
        }

        $this->reset(['description', 'amount', 'expense_date', 'file']);
        $this->resetMaponSnapshot();

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
