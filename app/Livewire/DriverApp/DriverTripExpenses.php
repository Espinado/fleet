<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Illuminate\Validation\Rule;

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;

use App\Enums\TripExpenseCategory;

class DriverTripExpenses extends Component
{
    use WithFileUploads;

    public Trip $trip;

    /** Form */
    public string $category = ''; // placeholder option works
    public string $description = '';
    public string $overload_note = '';
    public string $amount = ''; // keep string for Livewire input stability (optional for driver — can be 0)
    public string $expense_date = '';
    public $file = null;

    // TEMP compatibility with cached frontend (remove later)
    public $maponOdometerKm = null;
    public $maponOdometerSource = null;
    public $maponAt = null;
    public $maponIsStale = null;
    public $maponStaleMinutes = null;

    public ?float $manualOdometerKm = null;
    public ?float $liters = null;

    public function mount(Trip $trip)
    {
        if (!Auth::user()?->driver) {
            redirect()->route('driver.login')->send();
            return;
        }

        $this->trip = $trip;
        $this->category = '';
        $this->expense_date = now()->toDateString();
    }

    protected function rules(): array
    {
        // NOTE:
        // - required_if for liters / odometer
        // - prohibited_unless blocks sending fields for other categories (keeps DB clean)
        return [
            'category'     => ['required', new EnumRule(TripExpenseCategory::class)],
            'description'  => ['nullable', 'string', 'max:2000'],
            'amount'       => ['nullable', Rule::when(filled($this->amount), ['numeric', 'min:0'])],
            'overload_note' => ['nullable', 'string', 'max:500'],
            'expense_date' => ['required', 'date'],
            'file'         => ['nullable', 'file'],

            // Odometer only for fuel/adblue
            'manualOdometerKm' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:category,' . TripExpenseCategory::FUEL->value . ',' . TripExpenseCategory::ADBLUE->value,
                'prohibited_unless:category,' . TripExpenseCategory::FUEL->value . ',' . TripExpenseCategory::ADBLUE->value,
            ],

            // Liters for fuel/adblue/washer_fluid
            'liters' => [
                'nullable',
                'numeric',
                'min:0.01',
                'required_if:category,' . TripExpenseCategory::FUEL->value . ',' . TripExpenseCategory::ADBLUE->value . ',' . TripExpenseCategory::WASHER_FLUID->value,
                'prohibited_unless:category,' . TripExpenseCategory::FUEL->value . ',' . TripExpenseCategory::ADBLUE->value . ',' . TripExpenseCategory::WASHER_FLUID->value,
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'category.required' => 'Izvēlieties kategoriju.',
            'amount.numeric' => 'Summai jābūt skaitlim.',
            'expense_date.required' => 'Izvēlieties datumu.',
            'expense_date.date' => 'Nederīgs datums.',

            'manualOdometerKm.required_if' => 'Ievadiet odometru (km).',
            'manualOdometerKm.numeric' => 'Odometram jābūt skaitlim.',
            'manualOdometerKm.prohibited_unless' => 'Odometru var ievadīt tikai Degviela / AdBlue kategorijām.',

            'liters.required_if' => 'Ievadiet litrus.',
            'liters.numeric' => 'Litriem jābūt skaitlim.',
            'liters.prohibited_unless' => 'Litrus var ievadīt tikai Degviela / AdBlue / Logu šķidrums kategorijām.',
        ];
    }

    public function updatedCategory(): void
    {
        // reset dependent fields
        $this->manualOdometerKm = null;
        $this->liters = null;

        // clear validation so UI updates immediately
        $this->resetValidation();
    }

    public function updated($property): void
    {
        // clear error for changed property only
        $this->resetErrorBag($property);
    }

    private function logCtx(array $extra = []): array
    {
        $user = Auth::user();

        return array_merge([
            'user_id' => $user?->id,
            'driver_id' => $user?->driver?->id,
            'trip_id' => $this->trip->id ?? null,
            'truck_id' => $this->trip->truck?->id ?? null,
            'category' => $this->category ?? null,
            'amount' => $this->amount ?? null,
            'expense_date' => $this->expense_date ?? null,
            'manualOdometerKm' => $this->manualOdometerKm ?? null,
            'liters' => $this->liters ?? null,
            'has_file' => (bool) $this->file,
        ], $extra);
    }

    public function saveExpense(): void
    {
        $driver = Auth::user()?->driver;
        if (!$driver) {
            Log::warning('DriverTripExpenses: no driver auth', $this->logCtx());
            redirect()->route('driver.login')->send();
            return;
        }

        // validate (with conditional requirements)
        try {
            $validated = $this->validate();
        } catch (ValidationException $e) {
            Log::warning('DriverTripExpenses: validation failed', $this->logCtx([
                'errors' => $e->errors(),
            ]));
            throw $e;
        }

        $needsOdometer = in_array($this->category, [
            TripExpenseCategory::FUEL->value,
            TripExpenseCategory::ADBLUE->value,
        ], true);

        if ($needsOdometer && !$this->trip->truck) {
            Log::error('DriverTripExpenses: odometer required but trip has no truck', $this->logCtx());
            $this->addError('manualOdometerKm', 'В рейсе не найден truck.');
            $this->dispatch('driver-toast-error');
            return;
        }

        try {
            $result = DB::transaction(function () use ($driver, $needsOdometer, $validated) {

                $path = $this->file
                    ? \App\Helpers\ImageCompress::storeUpload($this->file, "trip_expenses/{$this->trip->id}", 'public')
                    : null;

                $expenseData = [
                    'trip_id'      => $this->trip->id,
                    'category'     => $validated['category'],
                    'description'  => $validated['description'] ?? null,
                    'amount'       => isset($validated['amount']) && $validated['amount'] !== '' && $validated['amount'] !== null
                        ? (float) $validated['amount']
                        : 0,
                    'overload_note' => isset($validated['overload_note']) ? trim($validated['overload_note']) : null,
                    'currency'     => 'EUR',
                    'file_path'    => $path,
                    'expense_date' => $validated['expense_date'],
                    'created_by'   => Auth::id(),
                    'liters'       => isset($validated['liters']) ? (float) $validated['liters'] : null,
                ];

                if ($needsOdometer) {
                    $expenseData['odometer_km'] = (float) $validated['manualOdometerKm'];
                    $expenseData['odometer_source'] = 'manual';
                } else {
                    // keep clean if not needed
                    $expenseData['odometer_km'] = null;
                    $expenseData['odometer_source'] = null;
                }

                Log::info('DriverTripExpenses: creating TripExpense', $this->logCtx([
                    'expenseData' => $expenseData,
                ]));

                /** @var TripExpense $expense */
                $expense = TripExpense::create($expenseData);

                $eventId = null;

                if ($needsOdometer) {
                    $truck = $this->trip->truck;
                    $odometerKm = (float) $validated['manualOdometerKm'];

                    // occurred_at is the expense date (more correct than now())
                    $occurredAt = now()->parse($validated['expense_date'])->setTimeFrom(now());

                    // Deduplicate within 2 minutes and lock to avoid double create on fast double tap
                    $existingEvent = TruckOdometerEvent::query()
                        ->where('truck_id', $truck->id)
                        ->where('driver_id', $driver->id)
                        ->where('type', TruckOdometerEvent::TYPE_FUEL) // temporary “refuel-like”
                        ->where('odometer_km', $odometerKm)
                        ->where('occurred_at', '>=', now()->subMinutes(2))
                        ->lockForUpdate()
                        ->first();

                    if ($existingEvent) {
                        $expense->update(['truck_odometer_event_id' => $existingEvent->id]);
                        $eventId = $existingEvent->id;
                    } else {
                        $event = TruckOdometerEvent::create([
                            'truck_id'      => $truck->id,
                            'driver_id'     => $driver->id,
                            'type'          => TruckOdometerEvent::TYPE_FUEL, // later can split Fuel/AdBlue
                            'odometer_km'   => $odometerKm,
                            'source'        => TruckOdometerEvent::SOURCE_MANUAL,
                            'occurred_at'   => $occurredAt,
                            'mapon_at'      => null,
                            'is_stale'      => false,
                            'stale_minutes' => null,
                            'raw'           => null,
                            'note'          => "Expense #{$expense->id} ({$validated['category']})",
                        ]);

                        $expense->update(['truck_odometer_event_id' => $event->id]);
                        $eventId = $event->id;
                    }
                }

                return ['expense_id' => $expense->id, 'event_id' => $eventId];
            });

            Log::info('DriverTripExpenses: saved successfully', $this->logCtx($result));
        } catch (\Throwable $e) {
            Log::error('DriverTripExpenses: save failed', $this->logCtx([
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]));

            $this->addError('save', 'Neizdevās saglabāt. Mēģiniet vēlreiz vai sazinieties ar dispečeru.');
            $this->dispatch('driver-toast-error');
            report($e);
            return;
        }

        $this->reset(['category', 'description', 'overload_note', 'amount', 'expense_date', 'file', 'manualOdometerKm', 'liters']);
        $this->resetValidation();

        $this->dispatch('driver-toast-success');
    }

    public function render()
    {
        $expenses = TripExpense::where('trip_id', $this->trip->id)->latest()->get();

        return view('livewire.driver-app.driver-trip-expenses', [
            'expenses'   => $expenses,
            'categories' => TripExpenseCategory::options(),
            'total'      => $expenses->sum('amount'),
        ]);
    }
}
