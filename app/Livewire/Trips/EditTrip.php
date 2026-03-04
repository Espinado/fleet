<?php
// app/Livewire/Trips/EditTrip.php

namespace App\Livewire\Trips;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Enums\TripStatus;
use App\Helpers\CalculateTax;
use App\Models\{
    Trip,
    TripCargo,
    TripStep,
    TripExpense,
    Company,
    Driver,
    Truck,
    Trailer,
    Client
};

class EditTrip extends Component
{
    public Trip $trip;

    /** ============================================================
     *  EXPEDITOR + CARRIER LOGIC (same as CreateTrip)
     * ============================================================ */
    public ?int $expeditor_id = null;
    public array $expeditorData = [];
    public ?string $expeditor_type = null;

    public ?int $carrier_company_id = null;
    public bool $needsCarrierSelect = false;
    public $carrierCompanies = [];

    /** third-party carriers (for autocomplete) */
    public $thirdPartyCarriers = [];

    public array $banks = [];
    public ?string $bank_index = null;

    public array $payers = [];
    public array $taxRates = [0, 5, 10, 21];

    /** '' | numeric string | '__third_party__' */
    public string $carrier_company_select = '';

    /** ============================================================
     *  THIRD PARTY INPUTS
     * ============================================================ */
    public ?string $third_party_name = null;
    public ?string $third_party_country = null;
    public ?string $third_party_reg_nr = null;

    public ?string $third_party_truck_plate = null;
    public ?string $third_party_truck_brand = null;
    public ?string $third_party_truck_model = null;
    public ?int    $third_party_truck_year = null;

    public ?string $third_party_trailer_plate = null;
    public ?string $third_party_trailer_brand = null;
    public ?int    $third_party_trailer_type_id = null;
    public ?int    $third_party_trailer_year = null;
    public ?string $third_party_trailer_vin = null;

    public ?string $third_party_price = null;

    /** ============================================================
     *  GLOBAL STEPS SELECTION FOR ALL CARGOS
     * ============================================================ */
    public array $trip_loading_step_ids = [];
    public array $trip_unloading_step_ids = [];

    /** ============================================================
     *  TRANSPORT (OUR ONLY UI)
     * ============================================================ */
    public ?int $driver_id = null;
    public ?int $truck_id = null;

    public ?int $trailer_id = null;
    public ?int $selected_trailer_type_id = null;

    public ?string $cont_nr = null;
    public ?string $seal_nr = null;

    public $drivers = [];
    public $trucks = [];
    public $trailers = [];

    /** ============================================================
     *  TRIP
     * ============================================================ */
    public string $currency = 'EUR';
    public $start_date;
    public $end_date;

    /** IMPORTANT: keep as string even if model casts status to Enum */
    public string $status = 'planned';

    public bool $customs = false;
    public ?string $customs_address = null;

    /** ============================================================
     *  STEPS / CARGOS
     * ============================================================ */
    public array $steps = [];
    public array $stepCities = [];
    public array $cargos = [];

    /** ============================================================
     *  SAFE CAST HELPERS (Enum -> string)
     * ============================================================ */
    private function statusToString(mixed $value, string $default = 'planned'): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            // if someone uses non-backed enum
            return (string) ($value->name ?? $default);
        }

        if ($value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    /** ============================================================
     *  MOUNT
     * ============================================================ */
    public function mount(Trip $trip): void
    {
        $this->trip = $trip;

        if ($trip->status === TripStatus::COMPLETED) {
            $this->redirect(route('trips.show', $trip->id), navigate: true);
            return;
        }

        $this->drivers  = Driver::where('is_active', 1)->get();
        $this->trucks   = Truck::where('is_active', 1)->get();
        $this->trailers = Trailer::where('is_active', 1)->get();

        $this->payers = config('payers', []);

        $this->carrierCompanies = Company::query()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('is_third_party', false)->orWhereNull('is_third_party');
            })
            ->whereIn('type', ['carrier', 'mixed', 'forwarder'])
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $this->thirdPartyCarriers = Company::query()
            ->where('is_active', 1)
            ->whereIn('type', ['carrier', 'mixed'])
            ->orderBy('name')
            ->get(['id', 'name']);

        // === basic trip fields
        $this->expeditor_id        = $trip->expeditor_id ? (int) $trip->expeditor_id : null;
        $this->carrier_company_id  = $trip->carrier_company_id ? (int) $trip->carrier_company_id : null;

        $this->driver_id  = $trip->driver_id ? (int) $trip->driver_id : null;
        $this->truck_id   = $trip->truck_id ? (int) $trip->truck_id : null;
        $this->trailer_id = $trip->trailer_id ? (int) $trip->trailer_id : null;

        $this->cont_nr = $trip->cont_nr;
        $this->seal_nr = $trip->seal_nr;

        $this->start_date = $trip->start_date ? $trip->start_date->format('Y-m-d') : null;
        $this->end_date   = $trip->end_date ? $trip->end_date->format('Y-m-d') : null;

        // ✅ FIX: enum-safe
        $this->status   = $this->statusToString($trip->status, 'planned');
        $this->currency = 'EUR';

        $this->customs = (bool) ($trip->customs ?? false);
        $this->customs_address = $trip->customs_address;

        // hydrate expeditor (banks + needsCarrierSelect + default carrier logic)
        $this->hydrateExpeditor();

        // при редактировании рейса третьей стороны — показывать селект перевозчика и блок данных 3. стороны
        $carrierForCheck = $this->trip->carrier_company_id ? Company::find($this->trip->carrier_company_id) : null;
        if ($this->trip->carrier_company_id !== null && ($this->trip->driver_id === null || ($carrierForCheck && $carrierForCheck->is_third_party))) {
            $this->needsCarrierSelect = true;
        }

        // restore carrier select state
        $this->hydrateCarrierSelectFromTrip();

        // restore trailer meta
        $this->updatedTrailerId($this->trailer_id);

        // restore steps + global selection + cargos
        $this->hydrateStepsFromDb();
        $this->hydrateCargosFromDb();
    }

    /** ============================================================
     *  EXPEDITOR (same helpers as CreateTrip)
     * ============================================================ */
    protected function decodeBanksJson(mixed $banksJson): array
    {
        if (empty($banksJson)) return [];
        if (is_array($banksJson)) return $banksJson;

        $str = (string) $banksJson;

        $decoded = json_decode($str, true);
        if (is_array($decoded)) return $decoded;

        $decoded2 = json_decode(trim($str, "\""), true);
        return is_array($decoded2) ? $decoded2 : [];
    }

    protected function hydrateBank(): void
    {
        if ($this->bank_index === null || $this->bank_index === '') {
            $this->expeditorData['bank'] = null;
            $this->expeditorData['iban'] = null;
            $this->expeditorData['bic']  = null;
            return;
        }

        $key = (string) $this->bank_index;

        if (!array_key_exists($key, $this->banks)) {
            $this->expeditorData['bank'] = null;
            $this->expeditorData['iban'] = null;
            $this->expeditorData['bic']  = null;
            return;
        }

        $bank = $this->banks[$key];

        $this->expeditorData['bank'] = $bank['name'] ?? null;
        $this->expeditorData['iban'] = $bank['iban'] ?? null;
        $this->expeditorData['bic']  = $bank['bic']  ?? null;
    }

    protected function resetExpeditorState(): void
    {
        $this->expeditorData = [];
        $this->banks = [];
        $this->bank_index = null;

        $this->expeditor_type = null;
        $this->needsCarrierSelect = false;
    }

    protected function resetThirdPartyState(): void
    {
        $this->third_party_name = null;
        $this->third_party_country = null;
        $this->third_party_reg_nr = null;

        $this->third_party_truck_plate = null;
        $this->third_party_truck_brand = null;
        $this->third_party_truck_model = null;
        $this->third_party_truck_year = null;

        $this->third_party_trailer_plate = null;
        $this->third_party_trailer_brand = null;
        $this->third_party_trailer_type_id = null;
        $this->third_party_trailer_year = null;
        $this->third_party_trailer_vin = null;

        $this->third_party_price = null;
    }

    protected function hydrateExpeditor(): void
    {
        $id = (int) ($this->expeditor_id ?? 0);

        $this->resetExpeditorState();

        if (!$id) {
            $this->carrier_company_id = null;
            $this->carrier_company_select = '';
            $this->resetThirdPartyState();
            return;
        }

        $exp = Company::query()->find($id);
        if (!$exp) return;

        $this->expeditor_type = $exp->type ?? null;

        $this->banks = $this->decodeBanksJson($exp->banks_json);

        // при редактировании: только если это исходный экспедитор — берём snapshot банка
        $useSnapshotBank = $this->trip
            && $this->trip->expeditor_id
            && (int) $this->trip->expeditor_id === $id;

        if ($useSnapshotBank && $this->trip->expeditor_bank_id !== null) {
            $this->bank_index = (string) $this->trip->expeditor_bank_id;
        } else {
            $this->bank_index = null;
        }

        $this->expeditorData = [
            'name'      => $exp->name ?? null,
            'reg_nr'    => $exp->reg_nr ?? null,
            'country'   => $exp->country ?? null,
            'city'      => $exp->city ?? null,
            'address'   => $exp->address ?? null,
            'post_code' => $exp->post_code ?? null,
            'email'     => $exp->email ?? null,
            'phone'     => $exp->phone ?? null,
            'bank'      => $useSnapshotBank ? $this->trip->expeditor_bank : null,
            'iban'      => $useSnapshotBank ? $this->trip->expeditor_iban : null,
            'bic'       => $useSnapshotBank ? $this->trip->expeditor_bic : null,
        ];

        // если выбрали нового экспедитора или сменили bank_index позже — обновим банковские реквизиты из banks_json
        if (!$useSnapshotBank) {
            $this->hydrateBank();
        }

        $this->needsCarrierSelect = ($this->expeditor_type === 'expeditor');

        // если НЕ посредник — carrier auto = expeditor
        if (!$this->needsCarrierSelect) {
            $this->carrier_company_id = (int) $exp->id;
            $this->carrier_company_select = (string) $this->carrier_company_id;
            $this->resetThirdPartyState();
        }
    }

    protected function hydrateCarrierSelectFromTrip(): void
    {
        if (!$this->needsCarrierSelect) {
            // forwarder => auto
            if ($this->expeditor_id) {
                $this->carrier_company_id = (int) $this->expeditor_id;
                $this->carrier_company_select = (string) $this->carrier_company_id;
            }
            return;
        }

        // посредник: определяем текущий режим (третья сторона, если компания помечена или у рейса нет водителя)
        $carrier = $this->trip->carrier_company_id ? Company::find($this->trip->carrier_company_id) : null;

        $isThirdPartyTrip =
            $this->trip->carrier_company_id !== null
            && (($carrier?->is_third_party ?? false) || $this->trip->driver_id === null);

        if ($isThirdPartyTrip) {
            $this->carrier_company_select = '__third_party__';
            $this->carrier_company_id = (int) $this->trip->carrier_company_id;

            $this->hydrateThirdPartyFromEntities($carrier);
            $this->hydrateThirdPartyPriceFromExpense();

            // third party => наша техника в UI выключена
            $this->driver_id = null;
            // truck_id/trailer_id оставляем (они third party)
        } else {
            $this->carrier_company_id = $this->trip->carrier_company_id ? (int) $this->trip->carrier_company_id : null;
            $this->carrier_company_select = $this->carrier_company_id ? (string) $this->carrier_company_id : '';
            $this->resetThirdPartyState();
        }
    }

    protected function hydrateThirdPartyFromEntities(?Company $carrier): void
    {
        if (!$carrier) return;

        $this->third_party_name    = $carrier->name;
        $this->third_party_country = $carrier->country;
        $this->third_party_reg_nr  = $carrier->reg_nr;

        if ($this->trip->truck_id) {
            $t = Truck::find($this->trip->truck_id);
            if ($t) {
                $this->third_party_truck_plate = $t->plate;
                $this->third_party_truck_brand = $t->brand;
                $this->third_party_truck_model = $t->model;
                $this->third_party_truck_year  = $t->year;
            }
        }

        if ($this->trip->trailer_id) {
            $tr = Trailer::find($this->trip->trailer_id);
            if ($tr) {
                $this->third_party_trailer_plate   = $tr->plate;
                $this->third_party_trailer_brand   = $tr->brand;
                $this->third_party_trailer_type_id = $tr->type_id;
                $this->third_party_trailer_year    = $tr->year;
                $this->third_party_trailer_vin     = $tr->vin;
            }
        }
    }

    protected function hydrateThirdPartyPriceFromExpense(): void
    {
        $exp = TripExpense::query()
            ->where('trip_id', $this->trip->id)
            ->orderByDesc('id')
            ->first();

        if ($exp) {
            $this->third_party_price = $exp->amount !== null ? (string) $exp->amount : null;
        }
    }

    public function updatedExpeditorId($value): void
    {
        $this->expeditor_id = $value ? (int) $value : null;
        $this->hydrateExpeditor();

        // если стал посредником — сбросить carrier select
        if ($this->needsCarrierSelect) {
            $this->carrier_company_select = '';
            $this->carrier_company_id = null;
        }
    }

    public function updatedBankIndex($value = null): void
    {
        $this->bank_index = ($value === '' || $value === null) ? null : (string) $value;

        if (empty($this->expeditorData)) {
            $this->expeditorData = [
                'name' => null, 'reg_nr' => null, 'country' => null, 'city' => null,
                'address' => null, 'post_code' => null, 'email' => null, 'phone' => null,
                'bank' => null, 'iban' => null, 'bic' => null,
            ];
        }

        $this->hydrateBank();
    }

    public function updatedCarrierCompanySelect($value): void
    {
        $this->carrier_company_select = (string) ($value ?? '');
        $this->resetValidation();

        if (!$this->needsCarrierSelect) {
            if ($this->expeditor_id) {
                $this->carrier_company_id = (int) $this->expeditor_id;
                $this->carrier_company_select = (string) $this->carrier_company_id;
            }
            return;
        }

        if ($this->carrier_company_select === '__third_party__') {
            $this->carrier_company_id = null;

            // third party => наша техника не нужна
            $this->driver_id = null;
            $this->truck_id  = null;
            $this->trailer_id = null;
            $this->selected_trailer_type_id = null;
            $this->cont_nr = null;
            $this->seal_nr = null;

            // даты возьмём из steps
            $this->start_date = null;
            $this->end_date = null;

            return;
        }

        if ($this->carrier_company_select === '') {
            $this->carrier_company_id = null;
            $this->resetThirdPartyState();
            return;
        }

        if (ctype_digit($this->carrier_company_select)) {
            $this->carrier_company_id = (int) $this->carrier_company_select;
            $this->resetThirdPartyState();
            return;
        }

        $this->carrier_company_id = null;
        $this->carrier_company_select = '';
    }

    /** ============================================================
     *  STEPS / CARGOS hydration from DB
     * ============================================================ */
    protected function hydrateStepsFromDb(): void
    {
        $steps = TripStep::where('trip_id', $this->trip->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $this->steps = [];
        $this->stepCities = [];

        foreach ($steps as $s) {
            $uid = (string) Str::uuid();

            $this->steps[] = [
                'uid'        => $uid,
                'type'       => $s->type,
                'country_id' => $s->country_id,
                'city_id'    => $s->city_id,
                'address'    => $s->address,
                'contact_phone_1' => $s->contact_phone_1,
                'contact_phone_2' => $s->contact_phone_2,
                'date'       => $s->date ? $s->date->format('Y-m-d') : null,
                'time'       => $s->time !== null && $s->time !== '' ? (string) $s->time : null,
                'order'      => $s->order,
                'notes'      => $s->notes,
            ];

            $this->stepCities[] = [
                'cities' => getCitiesByCountryId((int) $s->country_id) ?? []
            ];
        }

        // ВАЖНО: в DB у тебя pivot хранит step_id. А в UI мы работаем по uid.
        // Поэтому для Edit проще: восстановить global selection через СООТВЕТСТВИЕ ПО ПОРЯДКУ.
        $this->hydrateGlobalStepSelectionHeuristic();
    }

    protected function hydrateGlobalStepSelectionHeuristic(): void
    {
        $cargo = TripCargo::where('trip_id', $this->trip->id)->orderBy('id')->first();
        if (!$cargo) {
            $this->trip_loading_step_ids = [];
            $this->trip_unloading_step_ids = [];
            return;
        }

        if (!method_exists($cargo, 'steps')) {
            $this->trip_loading_step_ids = [];
            $this->trip_unloading_step_ids = [];
            return;
        }

        $pivotSteps = $cargo->steps()->withPivot('role')->get();

        $loadingOrders = [];
        $unloadingOrders = [];

        foreach ($pivotSteps as $ps) {
            $role = $ps->pivot->role ?? null;
            $order = (int) ($ps->order ?? 0);
            if (!$order) continue;

            if ($role === 'loading') $loadingOrders[] = $order;
            if ($role === 'unloading') $unloadingOrders[] = $order;
        }

        $this->trip_loading_step_ids = [];
        $this->trip_unloading_step_ids = [];

        foreach ($this->steps as $s) {
            $ord = (int) ($s['order'] ?? 0);
            $uid = (string) ($s['uid'] ?? '');

            if ($uid && in_array($ord, $loadingOrders, true)) {
                $this->trip_loading_step_ids[] = $uid;
            }
            if ($uid && in_array($ord, $unloadingOrders, true)) {
                $this->trip_unloading_step_ids[] = $uid;
            }
        }

        $this->trip_loading_step_ids = array_values(array_unique($this->trip_loading_step_ids));
        $this->trip_unloading_step_ids = array_values(array_unique($this->trip_unloading_step_ids));
    }

    /** ============================================================
     *  GLOBAL STEP TOGGLES (как в CreateTrip)
     * ============================================================ */
    public function toggleTripLoadingStep(string $uid): void
    {
        $arr = $this->trip_loading_step_ids ?? [];
        $this->trip_loading_step_ids = in_array($uid, $arr, true)
            ? array_values(array_diff($arr, [$uid]))
            : array_values(array_unique(array_merge($arr, [$uid])));
    }

    public function toggleTripUnloadingStep(string $uid): void
    {
        $arr = $this->trip_unloading_step_ids ?? [];
        $this->trip_unloading_step_ids = in_array($uid, $arr, true)
            ? array_values(array_diff($arr, [$uid]))
            : array_values(array_unique(array_merge($arr, [$uid])));
    }

    /** ============================================================
     *  STEPS: add, remove, reorder (drag-drop)
     * ============================================================ */
    public function addStep(): void
    {
        $this->steps[] = [
            'uid'              => (string) Str::uuid(),
            'type'             => 'loading',
            'country_id'       => null,
            'city_id'          => null,
            'address'          => null,
            'contact_phone_1'  => null,
            'contact_phone_2'  => null,
            'date'             => null,
            'time'             => null,
            'order'            => count($this->steps) + 1,
            'notes'             => null,
        ];
        $this->stepCities[] = ['cities' => []];
    }

    public function removeStep($index): void
    {
        $removedUid = $this->steps[$index]['uid'] ?? null;
        unset($this->steps[$index], $this->stepCities[$index]);
        $this->steps = array_values($this->steps);
        $this->stepCities = array_values($this->stepCities);
        if ($removedUid) {
            $this->trip_loading_step_ids = array_values(array_filter(
                $this->trip_loading_step_ids ?? [],
                fn ($v) => (string) $v !== (string) $removedUid
            ));
            $this->trip_unloading_step_ids = array_values(array_filter(
                $this->trip_unloading_step_ids ?? [],
                fn ($v) => (string) $v !== (string) $removedUid
            ));
            foreach ($this->cargos as $ci => $cargo) {
                $this->cargos[$ci]['loading_step_ids'] = array_values(array_filter(
                    $cargo['loading_step_ids'] ?? [],
                    fn ($v) => (string) $v !== (string) $removedUid
                ));
                $this->cargos[$ci]['unloading_step_ids'] = array_values(array_filter(
                    $cargo['unloading_step_ids'] ?? [],
                    fn ($v) => (string) $v !== (string) $removedUid
                ));
            }
        }
    }

    /**
     * Перестановка шагов по новому порядку индексов (для drag-drop).
     * @param array $orderedIndices Текущий порядок индексов после перетаскивания, например [1, 0, 2, 3]
     */
    public function reorderSteps(array $orderedIndices): void
    {
        if (empty($this->steps) || count($orderedIndices) !== count($this->steps)) {
            return;
        }
        $steps = [];
        $stepCities = [];
        foreach ($orderedIndices as $i) {
            $idx = (int) $i;
            if (isset($this->steps[$idx], $this->stepCities[$idx])) {
                $steps[] = $this->steps[$idx];
                $stepCities[] = $this->stepCities[$idx];
            }
        }
        if (count($steps) === count($this->steps)) {
            $this->steps = $steps;
            $this->stepCities = $stepCities;
        }
    }

    /**
     * При смене страны в шаге — обновляем только список городов и сбрасываем город.
     * Даты и остальные поля не трогаем, чтобы не сбрасывать форму.
     */
    public function updatedSteps($value, $key): void
    {
        $parts = explode('.', $key);
        $stepIndex = (int) ($parts[0] ?? 0);
        $field = $parts[1] ?? null;

        if ($field === 'country_id' && isset($this->stepCities[$stepIndex])) {
            $this->stepCities[$stepIndex]['cities'] = getCitiesByCountryId((int) $value) ?? [];
            $this->steps[$stepIndex]['city_id'] = null;
        }
    }

    protected function hydrateCargosFromDb(): void
    {
        $cargos = TripCargo::where('trip_id', $this->trip->id)
            ->orderBy('id')
            ->with('items')
            ->get();

        $this->cargos = [];

        foreach ($cargos as $cargo) {
            $itemsArr = [];

            foreach ($cargo->items as $it) {
                $itemsArr[] = [
                    'uid'            => (string) Str::uuid(),
                    'description'    => $it->description,
                    'customs_code'   => $it->customs_code,

                    'packages'       => $it->packages,
                    'pallets'        => $it->pallets,
                    'units'          => $it->units,
                    'net_weight'     => $it->net_weight,
                    'gross_weight'   => $it->gross_weight,
                    'tonnes'         => $it->tonnes,
                    'volume'         => $it->volume,
                    'loading_meters' => $it->loading_meters,

                    'hazmat'       => $it->hazmat,
                    'temperature'  => $it->temperature,
                    'stackable'    => (bool) $it->stackable,
                    'instructions' => $it->instructions,
                    'remarks'      => $it->remarks,
                ];
            }

            $this->cargos[] = [
                'uid' => (string) Str::uuid(),

                'customer_id'  => $cargo->customer_id,
                'shipper_id'   => $cargo->shipper_id,
                'consignee_id' => $cargo->consignee_id,

                // UI: для всех грузов у тебя GLOBAL selection
                'loading_step_ids'   => $this->trip_loading_step_ids,
                'unloading_step_ids' => $this->trip_unloading_step_ids,

                'price'            => (string) $cargo->price,
                'tax_percent'      => (string) $cargo->tax_percent,
                'total_tax_amount' => (float) $cargo->total_tax_amount,
                'price_with_tax'   => (float) $cargo->price_with_tax,
                'currency'         => 'EUR',

                'payment_terms' => $cargo->payment_terms,
                'payment_days'  => $cargo->payment_days ?? 30,
                'payer_type_id' => $cargo->payer_type_id,

                'commercial_invoice_nr'     => $cargo->commercial_invoice_nr,
                'commercial_invoice_amount' => $cargo->commercial_invoice_amount,

                'items' => $itemsArr ?: [[
                    'uid'            => (string) Str::uuid(),
                    'description'    => '',
                    'customs_code'   => null,
                    'packages'       => null,
                    'pallets'        => null,
                    'units'          => null,
                    'net_weight'     => null,
                    'gross_weight'   => null,
                    'tonnes'         => null,
                    'volume'         => null,
                    'loading_meters' => null,
                    'hazmat'         => '',
                    'temperature'    => '',
                    'stackable'      => false,
                    'instructions'   => '',
                    'remarks'        => '',
                ]],
            ];
        }

        if (empty($this->cargos)) {
            $this->cargos[] = [
                'uid' => (string) Str::uuid(),
                'customer_id' => null,
                'shipper_id' => null,
                'consignee_id' => null,
                'loading_step_ids' => $this->trip_loading_step_ids,
                'unloading_step_ids' => $this->trip_unloading_step_ids,
                'price' => '',
                'tax_percent' => 21,
                'total_tax_amount' => 0,
                'price_with_tax' => 0,
                'currency' => 'EUR',
                'payment_terms' => null,
                'payment_days' => 30,
                'payer_type_id' => null,
                'commercial_invoice_nr' => null,
                'commercial_invoice_amount' => null,
                'items' => [[
                    'uid' => (string) Str::uuid(),
                    'description' => '',
                    'customs_code' => null,
                    'packages' => null,
                    'pallets' => null,
                    'units' => null,
                    'net_weight' => null,
                    'gross_weight' => null,
                    'tonnes' => null,
                    'volume' => null,
                    'loading_meters' => null,
                    'hazmat' => '',
                    'temperature' => '',
                    'stackable' => false,
                    'instructions' => '',
                    'remarks' => ''
                ]],
            ];
        }
    }

    /** ============================================================
     *  NUM helpers (same as CreateTrip)
     * ============================================================ */
    private function normNumString($v): ?string
    {
        if ($v === null) return null;
        if ($v === '') return null;

        $v = (string) $v;
        $v = str_replace(["\xc2\xa0", ' '], '', $v);
        $v = str_replace(',', '.', $v);

        return $v === '' ? null : $v;
    }

    private function toFloat($v, float $default = 0.0): float
    {
        $v = $this->normNumString($v);
        if ($v === null) return $default;
        if (!is_numeric($v)) return $default;
        return (float) $v;
    }

    private function toInt($v, int $default = 0): int
    {
        if ($v === null || $v === '') return $default;
        $v = (string) $v;
        $v = str_replace(["\xc2\xa0", ' '], '', $v);
        if (!is_numeric($v)) return $default;
        return (int) $v;
    }

    private function normalizeInputsForValidation(): void
    {
        foreach ($this->cargos as $ci => $cargo) {
            $this->cargos[$ci]['price'] = $this->normNumString($cargo['price'] ?? null);
            $this->cargos[$ci]['tax_percent'] = $this->normNumString($cargo['tax_percent'] ?? null) ?? ($cargo['tax_percent'] ?? null);
            $this->cargos[$ci]['commercial_invoice_amount'] = $this->normNumString($cargo['commercial_invoice_amount'] ?? null);

            foreach (($cargo['items'] ?? []) as $ii => $item) {
                foreach (['packages', 'pallets', 'units', 'gross_weight', 'net_weight', 'tonnes', 'volume', 'loading_meters'] as $f) {
                    $this->cargos[$ci]['items'][$ii][$f] = $this->normNumString($item[$f] ?? null);
                }
            }
        }

        $this->third_party_price = $this->normNumString($this->third_party_price);

        // ✅ Нормализация шагов: типы и форматы для валидации
        foreach ($this->steps as $si => $step) {
            $date = isset($step['date']) ? trim((string) $step['date']) : '';
            if ($date !== '') {
                if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $date, $m)) {
                    $this->steps[$si]['date'] = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
                } else {
                    $this->steps[$si]['date'] = $date;
                }
            }
            $this->steps[$si]['time'] = isset($step['time']) ? trim((string) $step['time']) : null;
            $this->steps[$si]['address'] = isset($step['address']) ? trim((string) $step['address']) : '';
            $this->steps[$si]['contact_phone_1'] = isset($step['contact_phone_1']) ? trim((string) $step['contact_phone_1']) : null;
            $this->steps[$si]['contact_phone_2'] = isset($step['contact_phone_2']) ? trim((string) $step['contact_phone_2']) : null;
            $cid = $step['country_id'] ?? null;
            $this->steps[$si]['country_id'] = ($cid !== null && $cid !== '') ? (int) $cid : null;
            $cityId = $step['city_id'] ?? null;
            $this->steps[$si]['city_id'] = ($cityId !== null && $cityId !== '') ? (int) $cityId : null;
            $ord = $step['order'] ?? null;
            $this->steps[$si]['order'] = ($ord !== null && $ord !== '') ? (int) $ord : ($si + 1);
        }

        // ✅ ensure status is string (enum-safe)
        $this->status = $this->statusToString($this->status, 'planned');
    }

    private function autofillTripDatesFromSteps(bool $force = false): void
    {
        $dates = [];

        foreach (($this->steps ?? []) as $s) {
            $d = $s['date'] ?? null;
            if (!$d) continue;
            $dates[] = (string) $d;
        }

        if (empty($dates)) return;

        sort($dates);

        if ($force || empty($this->start_date)) $this->start_date = $dates[0];
        if ($force || empty($this->end_date)) $this->end_date = $dates[count($dates) - 1];
    }

    /** ============================================================
     *  TRAILER meta (same)
     * ============================================================ */
    private function containerTypeId(): int
    {
        $types = config('trailer-types.types', []);
        $id = array_search('container', $types, true);
        if ($id === false) return 2;
        return (int) $id;
    }

    public function getIsContainerTrailerProperty(): bool
    {
        return (int) ($this->selected_trailer_type_id ?? 0) === (int) $this->containerTypeId();
    }

    public function updatedTrailerId($value): void
    {
        $this->trailer_id = $value ? (int) $value : null;

        $this->selected_trailer_type_id = $this->trailer_id
            ? (int) Trailer::whereKey($this->trailer_id)->value('type_id')
            : null;

        if (!$this->isContainerTrailer) {
            $this->cont_nr = null;
            $this->seal_nr = null;
        }
    }

    /** ============================================================
     *  THIRD PARTY ensure (edit-safe)
     * ============================================================ */
    private function ensureThirdPartyCarrierCompany(): Company
    {
        $name = trim((string) ($this->third_party_name ?? ''));
        $nameLc = mb_strtolower($name);

        // если уже был third party carrier в trip — обновим его
        $current = null;
        if ($this->trip->carrier_company_id) {
            $c = Company::find($this->trip->carrier_company_id);
            if ($c && $c->is_third_party) $current = $c;
        }

        if ($current) {
            $current->update([
                'name'    => $name ?: $current->name,
                'reg_nr'  => $this->third_party_reg_nr,
                'country' => $this->third_party_country,
                'type'    => 'carrier',
                'is_third_party' => true,
                'is_active' => 1,
            ]);
            return $current;
        }

        // иначе попробуем найти по имени (как в CreateTrip)
        $existing = Company::query()
            ->where('is_active', 1)
            ->whereRaw('LOWER(name) = ?', [$nameLc])
            ->first();

        if ($existing) {
            if (!$existing->is_third_party) {
                $existing->update(['is_third_party' => true]);
            }
            return $existing;
        }

        $slug = Str::slug($name) ?: 'third-party';
        $base = $slug;
        $i = 2;

        while (Company::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return Company::create([
            'slug'           => $slug,
            'name'           => $name,
            'type'           => 'carrier',
            'reg_nr'         => $this->third_party_reg_nr,
            'country'        => $this->third_party_country,
            'is_third_party' => true,
            'is_system'      => 0,
            'is_active'      => 1,
        ]);
    }

    private function ensureThirdPartyTruck(int $companyId): Truck
    {
        $plate = trim((string) ($this->third_party_truck_plate ?? ''));

        // если в trip уже был truck — обновим его (если он принадлежит companyId)
        if ($this->trip->truck_id) {
            $t = Truck::find($this->trip->truck_id);
            if ($t && (int) $t->company_id === (int) $companyId) {
                $brandRaw = trim((string) ($this->third_party_truck_brand ?? ''));
                $modelRaw = trim((string) ($this->third_party_truck_model ?? ''));
                $t->update([
                    'plate' => $plate ?: $t->plate,
                    'brand' => $brandRaw !== '' ? $brandRaw : $t->brand,
                    'model' => $modelRaw !== '' ? $modelRaw : $t->model,
                    'year'  => $this->third_party_truck_year ? (int) $this->third_party_truck_year : $t->year,
                    'is_active' => 1,
                ]);
                return $t;
            }
        }

        $existing = Truck::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(plate) = ?', [mb_strtolower($plate)])
            ->first();

        if ($existing) return $existing;

        $brand = trim((string) ($this->third_party_truck_brand ?? ''));
        $model = trim((string) ($this->third_party_truck_model ?? ''));

        return Truck::create([
            'company_id'    => $companyId,
            'plate'         => $plate,
            'brand'         => $brand !== '' ? $brand : null,
            'model'         => $model !== '' ? $model : null,
            'year'          => $this->third_party_truck_year ? (int) $this->third_party_truck_year : null,
            'vin'           => null,
            'can_available' => 0,
            'status'        => 1,
            'is_active'     => 1,
        ]);
    }

    private function ensureThirdPartyTrailer(int $companyId): ?Trailer
    {
        $plate = trim((string) ($this->third_party_trailer_plate ?? ''));
        if ($plate === '') return null;

        // если уже был trailer — обновим его
        if ($this->trip->trailer_id) {
            $tr = Trailer::find($this->trip->trailer_id);
            if ($tr && (int) $tr->company_id === (int) $companyId) {
                $brandRaw = trim((string) ($this->third_party_trailer_brand ?? ''));
                $tr->update([
                    'plate'   => $plate ?: $tr->plate,
                    'brand'   => $brandRaw !== '' ? $brandRaw : $tr->brand,
                    'type_id' => $this->third_party_trailer_type_id ?: $tr->type_id,
                    'year'    => $this->third_party_trailer_year ? (int) $this->third_party_trailer_year : $tr->year,
                    'vin'     => null,
                    'is_active' => 1,
                ]);
                return $tr;
            }
        }

        $existing = Trailer::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(plate) = ?', [mb_strtolower($plate)])
            ->first();

        if ($existing) return $existing;

        $brand = trim((string) ($this->third_party_trailer_brand ?? ''));

        return Trailer::create([
            'company_id' => $companyId,
            'plate'      => $plate,
            'brand'      => $brand !== '' ? $brand : null,
            'type_id'    => $this->third_party_trailer_type_id ?: null,
            'year'       => $this->third_party_trailer_year ? (int) $this->third_party_trailer_year : null,
            'vin'        => null,
            'status'     => 1,
            'is_active'  => 1,
        ]);
    }

    /** ============================================================
     *  SAVE (edit)
     * ============================================================ */
    public function save(): void
    {
        $this->normalizeInputsForValidation();

        // currency fixed
        $this->currency = 'EUR';

        // forwarder => carrier auto
        if (!$this->needsCarrierSelect && $this->expeditor_id) {
            $this->carrier_company_id = (int) $this->expeditor_id;
            $this->carrier_company_select = (string) $this->carrier_company_id;
        }

        // посредник sync carrier_company_id
        if ($this->needsCarrierSelect) {
            if ($this->carrier_company_select === '__third_party__') {
                $this->carrier_company_id = null;
            } elseif (ctype_digit((string) $this->carrier_company_select)) {
                $this->carrier_company_id = (int) $this->carrier_company_select;
            } else {
                $this->carrier_company_id = null;
            }
        }

        $isThirdPartyFlow = $this->needsCarrierSelect && $this->carrier_company_select === '__third_party__';

        if ($isThirdPartyFlow) {
            $this->driver_id = null;
            $this->truck_id = null;
            $this->trailer_id = null;

            $this->selected_trailer_type_id = null;
            $this->cont_nr = null;
            $this->seal_nr = null;

            $this->autofillTripDatesFromSteps(true);
        }

        $needsContainerFields = (!$isThirdPartyFlow && $this->isContainerTrailer);
        if ($isThirdPartyFlow) {
            $this->cont_nr = null;
            $this->seal_nr = null;
        }

        $rules = [
            'expeditor_id' => 'required|integer|exists:companies,id',
            'bank_index'   => empty($this->banks) ? 'nullable' : 'required',

            'carrier_company_select' => $this->needsCarrierSelect ? 'required' : 'nullable',

            'carrier_company_id' => ($this->needsCarrierSelect && !$isThirdPartyFlow)
                ? 'required|integer|exists:companies,id'
                : 'nullable|integer|exists:companies,id',

            'third_party_name'        => $isThirdPartyFlow ? 'required|string|max:255' : 'nullable|string|max:255',
            'third_party_country'     => 'nullable|string|max:191',
            'third_party_reg_nr'      => 'nullable|string|max:191',
            'third_party_truck_plate' => $isThirdPartyFlow ? 'required|string|max:191' : 'nullable|string|max:191',
            'third_party_price'       => $isThirdPartyFlow ? 'required|numeric|min:0' : 'nullable|numeric|min:0',

            'driver_id'  => $isThirdPartyFlow ? 'nullable' : 'required|integer|exists:drivers,id',
            'truck_id'   => $isThirdPartyFlow ? 'nullable' : 'required|integer|exists:trucks,id',
            'trailer_id' => $isThirdPartyFlow ? 'nullable' : 'nullable|integer|exists:trailers,id',

            'start_date' => $isThirdPartyFlow ? 'nullable|date' : 'required|date',
            'end_date'   => $isThirdPartyFlow ? 'nullable|date' : 'required|date',

            'cont_nr' => $needsContainerFields ? 'required|string|max:50' : 'nullable|string|max:50',
            'seal_nr' => $needsContainerFields ? 'required|string|max:50' : 'nullable|string|max:50',

            'customs'         => 'nullable|boolean',
            'customs_address' => $this->customs ? 'required|string|max:255' : 'nullable|string|max:255',

            'steps'              => 'required|array|min:1',
            'steps.*.uid'        => 'required|string',
            'steps.*.type'       => 'required|string|in:loading,unloading',
            'steps.*.country_id' => 'required|integer',
            'steps.*.city_id'    => 'required|integer',
            'steps.*.address'    => 'required|string',
            'steps.*.contact_phone_1' => 'nullable|string|max:50',
            'steps.*.contact_phone_2' => 'nullable|string|max:50',
            'steps.*.date'       => 'required|date',
            'steps.*.time'       => 'nullable',
            'steps.*.order'      => 'required|integer',

            'trip_loading_step_ids'   => 'required|array|min:1',
            'trip_unloading_step_ids' => 'required|array|min:1',

            'cargos'                      => 'required|array|min:1',
            'cargos.*.customer_id'        => 'required|integer',
            'cargos.*.shipper_id'         => 'required|integer',
            'cargos.*.consignee_id'       => 'required|integer',
            'cargos.*.price'              => 'required|numeric',
            'cargos.*.payment_days'       => 'nullable|integer|in:7,14,21,30',
            'cargos.*.tax_percent'        => 'required|numeric',
            'cargos.*.commercial_invoice_nr'     => 'nullable|string|max:64',
            'cargos.*.commercial_invoice_amount' => 'nullable|numeric|min:0',

            'cargos.*.items'                => 'required|array|min:1',
            'cargos.*.items.*.customs_code' => 'nullable|string|max:32',
            'cargos.*.items.*.description'  => 'nullable|string|max:255',
        ];

        $data = [
            'expeditor_id' => $this->expeditor_id,
            'bank_index'   => $this->bank_index,
            'carrier_company_select' => $this->carrier_company_select,
            'carrier_company_id'     => $this->carrier_company_id,

            'third_party_name'        => $this->third_party_name,
            'third_party_country'     => $this->third_party_country,
            'third_party_reg_nr'      => $this->third_party_reg_nr,
            'third_party_truck_plate' => $this->third_party_truck_plate,
            'third_party_price'       => $this->third_party_price,

            'driver_id'  => $this->driver_id,
            'truck_id'   => $this->truck_id,
            'trailer_id' => $this->trailer_id,

            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,

            'customs' => $this->customs,
            'customs_address' => $this->customs_address,

            'steps'  => $this->steps,
            'cargos' => $this->cargos,

            'cont_nr' => $this->cont_nr,
            'seal_nr' => $this->seal_nr,

            'trip_loading_step_ids' => $this->trip_loading_step_ids,
            'trip_unloading_step_ids' => $this->trip_unloading_step_ids,
        ];

        if ($isThirdPartyFlow) {
            $data['driver_id'] = null;
            $data['truck_id'] = null;
            $data['trailer_id'] = null;
        }

        $validator = Validator::make($data, $rules);

        $validator->after(function ($validator) use ($isThirdPartyFlow) {
            if ($isThirdPartyFlow && (empty($this->start_date) || empty($this->end_date))) {
                $this->autofillTripDatesFromSteps(true);
            }

            if ($isThirdPartyFlow && (empty($this->start_date) || empty($this->end_date))) {
                $validator->errors()->add('start_date', __('app.trip.edit.err_dates'));
            }

            if (!$isThirdPartyFlow && !$this->isContainerTrailer) {
                $this->cont_nr = null;
                $this->seal_nr = null;
            }

            foreach ($this->cargos as $cargoIndex => $cargo) {
                foreach (($cargo['items'] ?? []) as $itemIndex => $item) {
                    $hasDescOrCode = trim((string)($item['description'] ?? '')) !== '' || trim((string)($item['customs_code'] ?? '')) !== '';
                    $hasMeasurement =
                        ($this->toFloat($item['packages'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['pallets'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['units'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['net_weight'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['gross_weight'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['tonnes'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['volume'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['loading_meters'] ?? null, 0) > 0);

                    if (!$hasDescOrCode && !$hasMeasurement) {
                        $validator->errors()->add(
                            "cargos.$cargoIndex.items.$itemIndex.measurements",
                            __('app.trip.edit.err_item_measure', ['n' => $itemIndex + 1])
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());
            return;
        }

        // global steps sanity
        $intersection = array_values(array_intersect(
            (array) $this->trip_loading_step_ids,
            (array) $this->trip_unloading_step_ids
        ));

        if (!empty($intersection)) {
            $this->addError('trip_unloading_step_ids', __('app.trip.edit.err_same_step'));
            return;
        }

        DB::beginTransaction();

        try {
            // ===== third party: ensure entities + set ids
            $thirdPartyCompany = null;

            if ($isThirdPartyFlow) {
                $thirdPartyCompany = $this->ensureThirdPartyCarrierCompany();
                $this->carrier_company_id = (int) $thirdPartyCompany->id;

                $tpTruck = $this->ensureThirdPartyTruck($this->carrier_company_id);
                $this->truck_id = (int) $tpTruck->id;

                $tpTrailer = $this->ensureThirdPartyTrailer($this->carrier_company_id);
                $this->trailer_id = $tpTrailer ? (int) $tpTrailer->id : null;

                $this->autofillTripDatesFromSteps(true);
            }

            // ===== update trip snapshot + core fields
            $this->trip->update([
                'expeditor_id'        => $this->expeditor_id,
                'expeditor_name'      => $this->expeditorData['name'] ?? null,
                'expeditor_reg_nr'    => $this->expeditorData['reg_nr'] ?? null,
                'expeditor_country'   => $this->expeditorData['country'] ?? null,
                'expeditor_city'      => $this->expeditorData['city'] ?? null,
                'expeditor_address'   => $this->expeditorData['address'] ?? null,
                'expeditor_post_code' => $this->expeditorData['post_code'] ?? null,
                'expeditor_email'     => $this->expeditorData['email'] ?? null,
                'expeditor_phone'     => $this->expeditorData['phone'] ?? null,

                'customs'         => (bool) $this->customs,
                'customs_address' => $this->customs ? $this->customs_address : null,

                'carrier_company_id' => $this->carrier_company_id,

                'expeditor_bank_id' => $this->bank_index !== null ? (int) $this->bank_index : null,
                'expeditor_bank'    => $this->expeditorData['bank'] ?? null,
                'expeditor_iban'    => $this->expeditorData['iban'] ?? null,
                'expeditor_bic'     => $this->expeditorData['bic'] ?? null,

                'driver_id'  => $isThirdPartyFlow ? null : $this->driver_id,
                'truck_id'   => $this->truck_id,
                'trailer_id' => $this->trailer_id,

                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,

                'currency'   => 'EUR',

                // ✅ FIX: always store string, even if UI got Enum somehow
                'status'     => $this->statusToString($this->status, 'planned'),

                'cont_nr'    => $this->cont_nr,
                'seal_nr'    => $this->seal_nr,
            ]);

            // ===== third party expense: upsert / delete
            if ($isThirdPartyFlow && $thirdPartyCompany) {
                $amount = $this->toFloat($this->third_party_price, 0.0);

                $expense = TripExpense::query()
                    ->where('trip_id', $this->trip->id)
                    ->where('supplier_company_id', $thirdPartyCompany->id)
                    ->orderByDesc('id')
                    ->first();

                if ($expense) {
                    $expense->update([
                        'category'     => 'other',
                        'description'  => __('app.trip.edit.expense_third', ['name' => $thirdPartyCompany->name ?? '']),
                        'amount'       => $amount,
                        'currency'     => 'EUR',
                        'expense_date' => $this->start_date,
                    ]);
                } else {
                    TripExpense::create([
                        'trip_id'             => $this->trip->id,
                        'supplier_company_id' => $thirdPartyCompany->id,
                        'category'            => 'other',
                        'description'         => __('app.trip.edit.expense_third', ['name' => $thirdPartyCompany->name ?? '']),
                        'amount'              => $amount,
                        'currency'            => 'EUR',
                        'expense_date'        => $this->start_date,
                    ]);
                }
            } else {
                TripExpense::where('trip_id', $this->trip->id)
                    ->where(function ($q) {
                        $q->where('description', 'like', 'Оплата третьей стороне:%')
                          ->orWhere('description', 'like', 'Maksa 3. pusei:%');
                    })
                    ->delete();
            }

            // ===== STEPS & CARGOS: delete + recreate
            $existingCargos = TripCargo::where('trip_id', $this->trip->id)->with('items')->get();
            foreach ($existingCargos as $c) {
                if (method_exists($c, 'steps')) $c->steps()->detach();
                $c->items()->delete();
                $c->delete();
            }

            TripStep::where('trip_id', $this->trip->id)->delete();

            // create steps + map uid -> id
            $stepUidToId = [];

            foreach ($this->steps as $i => $s) {
                $dbStep = TripStep::create([
                    'trip_id'    => $this->trip->id,
                    'order'      => $i + 1,
                    'type'       => $s['type'],
                    'country_id' => $s['country_id'],
                    'city_id'    => $s['city_id'],
                    'address'    => $s['address'],
                    'contact_phone_1' => $s['contact_phone_1'] ?? null,
                    'contact_phone_2' => $s['contact_phone_2'] ?? null,
                    'date'       => $s['date'],
                    'time'       => $s['time'] ?? null,
                    'notes'      => $s['notes'] ?? null,
                ]);

                $uid = (string) ($s['uid'] ?? '');
                if ($uid !== '') $stepUidToId[$uid] = $dbStep->id;
            }

            // create cargos + items + pivot steps (GLOBAL selection)
            foreach ($this->cargos as $cargoData) {
                $price = $this->toFloat($cargoData['price'] ?? null, 0.0);
                $taxPercent = $this->toFloat($cargoData['tax_percent'] ?? null, 0.0);

                $tax = CalculateTax::calculate($price, $taxPercent);

                $commercialInvoiceAmountRaw = $cargoData['commercial_invoice_amount'] ?? null;
                $commercialInvoiceAmount = ($commercialInvoiceAmountRaw !== null && $commercialInvoiceAmountRaw !== '')
                    ? $this->toFloat($commercialInvoiceAmountRaw, 0.0)
                    : null;

                $cargo = TripCargo::create([
                    'trip_id'      => $this->trip->id,
                    'customer_id'  => $cargoData['customer_id'],
                    'shipper_id'   => $cargoData['shipper_id'],
                    'consignee_id' => $cargoData['consignee_id'],

                    'price'            => $price,
                    'tax_percent'      => $taxPercent,
                    'total_tax_amount' => $tax['tax_amount'],
                    'price_with_tax'   => $tax['price_with_tax'],

                    'currency'      => 'EUR',
                    'payment_terms' => $cargoData['payment_terms'] ?? null,
                    'payment_days'  => in_array((int)($cargoData['payment_days'] ?? 30), [7, 14, 21, 30], true) ? (int)$cargoData['payment_days'] : 30,
                    'payer_type_id' => $cargoData['payer_type_id'] ?? null,

                    'commercial_invoice_nr'     => $cargoData['commercial_invoice_nr'] ?? null,
                    'commercial_invoice_amount' => $commercialInvoiceAmount,
                ]);

                foreach (($cargoData['items'] ?? []) as $item) {
                    $cargo->items()->create([
                        'description'  => $item['description'] ?? '',
                        'customs_code' => $item['customs_code'] ?? null,

                        'packages'       => $this->toInt($item['packages'] ?? null, 0),
                        'pallets'        => $this->toInt($item['pallets'] ?? null, 0),
                        'units'          => $this->toInt($item['units'] ?? null, 0),

                        'net_weight'     => $this->toFloat($item['net_weight'] ?? null, 0.0),
                        'gross_weight'   => $this->toFloat($item['gross_weight'] ?? null, 0.0),
                        'tonnes'         => $this->toFloat($item['tonnes'] ?? null, 0.0),
                        'volume'         => $this->toFloat($item['volume'] ?? null, 0.0),
                        'loading_meters' => $this->toFloat($item['loading_meters'] ?? null, 0.0),

                        'hazmat'       => $item['hazmat'] ?? '',
                        'temperature'  => $item['temperature'] ?? '',
                        'stackable'    => (bool) ($item['stackable'] ?? false),
                        'instructions' => $item['instructions'] ?? '',
                        'remarks'      => $item['remarks'] ?? '',
                    ]);
                }

                // pivot steps
                if (method_exists($cargo, 'steps')) {
                    $pivot = [];

                    foreach (($this->trip_loading_step_ids ?? []) as $uid) {
                        $uid = (string) $uid;
                        if ($uid && isset($stepUidToId[$uid])) {
                            $pivot[$stepUidToId[$uid]] = ['role' => 'loading'];
                        }
                    }

                    foreach (($this->trip_unloading_step_ids ?? []) as $uid) {
                        $uid = (string) $uid;
                        if ($uid && isset($stepUidToId[$uid])) {
                            $stepId = $stepUidToId[$uid];
                            if (isset($pivot[$stepId])) continue;
                            $pivot[$stepId] = ['role' => 'unloading'];
                        }
                    }

                    if ($pivot) $cargo->steps()->attach($pivot);
                }
            }

            DB::commit();

            $this->redirectRoute('trips.show', $this->trip->id);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('EditTrip ERROR', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            $this->addError('error', __('app.trip.edit.err_save'));
        }
    }

    /** ============================================================
     *  RENDER
     * ============================================================ */
    public function render()
    {
        $expeditors = Company::query()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('is_third_party', false)->orWhereNull('is_third_party');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('livewire.trips.edit-trip', [
            'clients'            => Client::orderBy('company_name')->get(),
            'countries'          => config('countries', []),
            'expeditors'         => $expeditors,
            'carrierCompanies'   => $this->carrierCompanies,
            'thirdPartyCarriers' => $this->thirdPartyCarriers,
            'needsCarrierSelect' => $this->needsCarrierSelect,
            'payers'             => $this->payers,
            'taxRates'           => $this->taxRates,

            'isContainerTrailer' => $this->isContainerTrailer,
            'trailerTypeMeta'    => $this->trailerTypeMeta,
        ])->layout('layouts.app', [
            'title' => __('app.trip.edit.title'),
        ]);
    }

    public function getTrailerTypeMetaProperty(): ?array
    {
        if (!$this->selected_trailer_type_id) return null;

        $types  = config('trailer-types.types', []);
        $labels = config('trailer-types.labels', []);
        $icons  = config('trailer-types.icons', []);

        $key = $types[$this->selected_trailer_type_id] ?? null;
        if (!$key) return null;

        return [
            'id'    => $this->selected_trailer_type_id,
            'key'   => $key,
            'label' => $labels[$key] ?? $key,
            'icon'  => $icons[$key] ?? '🚚',
        ];
    }
}
