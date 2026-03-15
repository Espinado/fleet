<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Models\Company;
use App\Models\OrderCargo;
use App\Models\OrderStep;
use App\Models\TransportOrder;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateOrder extends Component
{
    public string $order_date = '';
    public ?int $expeditor_id = null;
    public string $currency = 'EUR';
    public string $status = 'draft';
    public ?string $notes = null;

    /** TIR / таможня (как в создании рейса) */
    public bool $customs = false;
    public ?string $customs_address = null;

    /** @var array<int, array{uid: string, type: string, country_id: ?int, city_id: ?int, address: ?string, date: ?string, time: ?string, contact_phone: ?string, order: int, notes: ?string}> */
    public array $steps = [];
    /** @var array<int, array{cities: array}> */
    public array $stepCities = [];

    /** @var array<int, array{uid: string, customer_id: ?int, requested_date_from: ?string, requested_date_to: ?string, shipper_id: ?int, consignee_id: ?int, description: ?string, weight_kg: ?string, net_weight: ?string, gross_weight: ?string, tonnes: ?string, volume_m3: ?string, loading_meters: ?string, pallets: ?string, packages: ?string, units: ?string, customs_code: ?string, hazmat: ?string, temperature: ?string, stackable: bool, instructions: ?string, remarks: ?string, quoted_price: ?string}> */
    public array $cargos = [];

    public function mount(): void
    {
        $this->order_date = now()->format('Y-m-d');
        // Steps and cargos start empty — add via "Add step" / "Add cargo" (груз и маршрут могут быть уточнены позже)
    }

    protected function rules(): array
    {
        return [
            'order_date'          => 'required|date',
            'expeditor_id'        => 'required|exists:companies,id',
            'currency'            => 'required|string|max:10',
            'status'              => 'required|in:draft,quoted,confirmed,converted,cancelled',
            'notes'               => 'nullable|string|max:5000',
            'customs'             => 'nullable|boolean',
            'customs_address'     => $this->customs ? 'required|string|max:255' : 'nullable|string|max:255',
        ];
    }

    public function addStep(): void
    {
        $this->steps[] = [
            'uid'           => (string) Str::uuid(),
            'type'          => 'loading',
            'country_id'    => null,
            'city_id'       => null,
            'address'       => null,
            'date'          => null,
            'time'          => null,
            'contact_phone' => null,
            'order'         => count($this->steps) + 1,
            'notes'         => null,
        ];
        $this->stepCities[] = ['cities' => []];
    }

    public function removeStep(int $index): void
    {
        if (isset($this->steps[$index])) {
            array_splice($this->steps, $index, 1);
            array_splice($this->stepCities, $index, 1);
            foreach ($this->steps as $i => $s) {
                $this->steps[$i]['order'] = $i + 1;
            }
        }
    }

    public function updatedSteps($value, $key): void
    {
        $parts = explode('.', $key);
        $stepIndex = (int) ($parts[0] ?? 0);
        $field = $parts[1] ?? null;
        if ($field === 'country_id' && isset($this->stepCities[$stepIndex])) {
            $this->stepCities[$stepIndex]['cities'] = function_exists('getCitiesByCountryId')
                ? (getCitiesByCountryId((int) $value) ?? [])
                : [];
            $this->steps[$stepIndex]['city_id'] = null;
        }
    }

    public function setStepCountry(int $stepIndex, $countryId): void
    {
        if (!isset($this->steps[$stepIndex])) {
            return;
        }
        $id = $countryId !== null && $countryId !== '' ? (int) $countryId : null;
        $this->steps[$stepIndex]['country_id'] = $id;
        $this->stepCities[$stepIndex]['cities'] = $id !== null && function_exists('getCitiesByCountryId')
            ? (getCitiesByCountryId($id) ?? [])
            : [];
        $this->steps[$stepIndex]['city_id'] = null;
    }

    public function addCargo(): void
    {
        $this->cargos[] = [
            'uid'                 => (string) Str::uuid(),
            'customer_id'         => null,
            'requested_date_from' => null,
            'requested_date_to'   => null,
            'shipper_id'          => null,
            'consignee_id'        => null,
            'description'         => null,
            'weight_kg'           => null,
            'net_weight'          => null,
            'gross_weight'        => null,
            'tonnes'              => null,
            'volume_m3'           => null,
            'loading_meters'      => null,
            'pallets'             => null,
            'packages'             => null,
            'units'               => null,
            'customs_code'         => null,
            'hazmat'              => null,
            'temperature'         => null,
            'stackable'           => false,
            'instructions'        => null,
            'remarks'             => null,
            'quoted_price'        => null,
        ];
    }

    public function removeCargo(int $index): void
    {
        if (isset($this->cargos[$index])) {
            array_splice($this->cargos, $index, 1);
        }
    }

    public function save(): \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
    {
        $validated = $this->validate();

        $quotedTotal = null;
        $dateFrom = null;
        $dateTo = null;
        $firstCustomerId = null;
        foreach ($this->cargos as $c) {
            $p = isset($c['quoted_price']) && $c['quoted_price'] !== '' && $c['quoted_price'] !== null ? (float) $c['quoted_price'] : null;
            if ($p !== null) {
                $quotedTotal = ($quotedTotal ?? 0) + $p;
            }
            if (!empty($c['requested_date_from'])) {
                $dateFrom = $dateFrom === null ? $c['requested_date_from'] : min($dateFrom, $c['requested_date_from']);
            }
            if (!empty($c['requested_date_to'])) {
                $dateTo = $dateTo === null ? $c['requested_date_to'] : max($dateTo, $c['requested_date_to']);
            }
            if (($firstCustomerId === null) && !empty($c['customer_id'])) {
                $firstCustomerId = (int) $c['customer_id'];
            }
        }

        $order = TransportOrder::create([
            'number'              => TransportOrder::generateNumber(),
            'order_date'          => $validated['order_date'],
            'expeditor_id'        => $validated['expeditor_id'],
            'customer_id'         => $firstCustomerId,
            'requested_date_from' => $dateFrom,
            'requested_date_to'   => $dateTo,
            'quoted_price'        => $quotedTotal,
            'currency'            => $validated['currency'],
            'status'              => $validated['status'],
            'notes'               => $validated['notes'] ?? null,
            'customs'             => (bool) ($validated['customs'] ?? false),
            'customs_address'     => $this->customs ? ($validated['customs_address'] ?? null) : null,
        ]);

        foreach ($this->steps as $i => $s) {
            OrderStep::create([
                'transport_order_id' => $order->id,
                'type'               => $s['type'] ?? 'loading',
                'country_id'         => $s['country_id'] ?? null,
                'city_id'            => $s['city_id'] ?? null,
                'address'            => $s['address'] ?? null,
                'date'               => !empty($s['date']) ? $s['date'] : null,
                'time'               => $s['time'] ?? null,
                'contact_phone'      => $s['contact_phone'] ?? null,
                'order'              => $i + 1,
                'notes'              => $s['notes'] ?? null,
            ]);
        }

        foreach ($this->cargos as $c) {
            $price = isset($c['quoted_price']) && $c['quoted_price'] !== '' && $c['quoted_price'] !== null
                ? (float) $c['quoted_price']
                : null;
            OrderCargo::create([
                'transport_order_id'   => $order->id,
                'customer_id'          => $c['customer_id'] ?? null,
                'requested_date_from'  => !empty($c['requested_date_from']) ? $c['requested_date_from'] : null,
                'requested_date_to'    => !empty($c['requested_date_to']) ? $c['requested_date_to'] : null,
                'shipper_id'           => $c['shipper_id'] ?? null,
                'consignee_id'         => $c['consignee_id'] ?? null,
                'description'         => $c['description'] ?? null,
                'weight_kg'            => isset($c['weight_kg']) && $c['weight_kg'] !== '' ? (float) $c['weight_kg'] : null,
                'net_weight'           => isset($c['net_weight']) && $c['net_weight'] !== '' ? (float) $c['net_weight'] : null,
                'gross_weight'        => isset($c['gross_weight']) && $c['gross_weight'] !== '' ? (float) $c['gross_weight'] : null,
                'tonnes'               => isset($c['tonnes']) && $c['tonnes'] !== '' ? (float) $c['tonnes'] : null,
                'volume_m3'            => isset($c['volume_m3']) && $c['volume_m3'] !== '' ? (float) $c['volume_m3'] : null,
                'loading_meters'       => isset($c['loading_meters']) && $c['loading_meters'] !== '' ? (float) $c['loading_meters'] : null,
                'pallets'             => isset($c['pallets']) && $c['pallets'] !== '' ? (int) $c['pallets'] : null,
                'packages'            => isset($c['packages']) && $c['packages'] !== '' ? (int) $c['packages'] : null,
                'units'               => isset($c['units']) && $c['units'] !== '' ? (int) $c['units'] : null,
                'customs_code'        => $c['customs_code'] ?? null,
                'hazmat'              => $c['hazmat'] ?? null,
                'temperature'         => $c['temperature'] ?? null,
                'stackable'           => (bool) ($c['stackable'] ?? false),
                'instructions'        => $c['instructions'] ?? null,
                'remarks'             => $c['remarks'] ?? null,
                'quoted_price'         => $price,
            ]);
        }

        session()->flash('success', __('app.orders.create.title') . ' — OK');
        return redirect()->route('orders.show', $order);
    }

    public function render()
    {
        $companies = Company::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $clients = \App\Models\Client::query()->orderBy('company_name')->get(['id', 'company_name']);
        $countries = config('countries', []);

        return view('livewire.orders.create-order', [
            'companies' => $companies,
            'clients'   => $clients,
            'countries' => $countries,
            'statuses'  => OrderStatus::cases(),
        ])->layout('layouts.app', [
            'title' => __('app.orders.create.title'),
        ]);
    }
}
