<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Enums\OrderStatus;
use App\Models\Trip;
use App\Models\TransportOrder;
use App\Models\TripStep;
use App\Models\TripStepDocument;
use App\Models\TripCargo;
use App\Models\TripCargoItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\CmrController;
use App\Helpers\CalculateTax;

class ViewTrip extends Component
{
    use WithFileUploads;

    public Trip $trip;

    // step docs
    public array $stepDocType = [];
    public array $stepDocComment = [];
    public array $stepDocFile = [];

    // ✅ ручные номера документов по cargo_id
    public array $cmrNr = [];   // cargo_id => string
    public array $orderNr = []; // cargo_id => string
    public array $invNr = [];   // cargo_id => string

    // ✅ Dikstāve (delay) per cargo
    public array $delayChecked = []; // cargo_id => bool
    public array $delayDays = [];    // cargo_id => int|string
    public array $delayAmount = [];  // cargo_id => float|string (without VAT)

    /** Выбор заказов для «Добавить в рейс» */
    public array $add_orders_selection = [];

    public function mount($trip)
    {
        $this->trip = $trip instanceof Trip ? $trip : Trip::findOrFail($trip);

        $this->reloadTrip();
    }

    private function reloadTrip(): void
    {
        $this->trip->refresh();
        // Сброс связей маршрута и грузов, чтобы при добавлении/удалении заказов блок «Reisa maršruts» показывал актуальные точки
        $this->trip->unsetRelation('steps');
        $this->trip->unsetRelation('cargos');
        $this->trip->unsetRelation('transportOrders');
        $this->trip->load([
            'driver',
            'truck',
            'trailer',
            'transportOrders.expeditor',
            'transportOrders.customer',
            'transportOrders.steps',
            'transportOrders.cargos.shipper',
            'transportOrders.cargos.consignee',

            'cargos.shipper',
            'cargos.consignee',
            'cargos.customer',
            'cargos.items',
            'cargos.steps.documents',

            'steps.cargos',
        ]);

        $this->syncMissingCargosFromOrders();
        $this->hydrateCargoInputsFromTrip();
    }

    /** Включить публичную ссылку отслеживания для груза (клиент видит только свой груз; после разгрузки ссылка недействительна). */
    public function enableCargoTracking(int $cargoId): void
    {
        $cargo = $this->trip->cargos->firstWhere('id', $cargoId);
        if ($cargo) {
            $cargo->enableTracking();
            $this->reloadTrip();
        }
    }

    /** Отключить публичную ссылку отслеживания для груза. */
    public function disableCargoTracking(int $cargoId): void
    {
        $cargo = $this->trip->cargos->firstWhere('id', $cargoId);
        if ($cargo) {
            $cargo->disableTracking();
            $this->reloadTrip();
        }
    }

    /** Fill cmrNr, orderNr, invNr, delayChecked, delayDays, delayAmount for all current cargos (fixes Entangle after sync adds new cargos). */
    protected function hydrateCargoInputsFromTrip(): void
    {
        foreach ($this->trip->cargos as $cargo) {
            $cid = (int) $cargo->id;
            $this->cmrNr[$cid]        = (string)($cargo->cmr_nr ?? '');
            $this->orderNr[$cid]      = (string)($cargo->order_nr ?? '');
            $this->invNr[$cid]        = (string)($cargo->inv_nr ?? '');
            $this->delayChecked[$cid] = (bool) ($cargo->has_delay ?? false);
            $this->delayDays[$cid]    = $cargo->delay_days !== null ? (string) $cargo->delay_days : '';
            $this->delayAmount[$cid]  = $cargo->delay_amount !== null ? (string) $cargo->delay_amount : '';
        }
    }

    /**
     * For trips that have linked orders but missing TripCargos (e.g. orders were added before we created cargos),
     * backfill transport_order_id on existing cargos then create TripSteps and TripCargos for orders that have none.
     */
    protected function syncMissingCargosFromOrders(): void
    {
        if (!Schema::hasColumn('trip_cargos', 'transport_order_id')) {
            return;
        }

        $orders = $this->trip->transportOrders;
        if ($orders === null || $orders->isEmpty()) {
            return;
        }

        try {
            $ordersSorted = $orders->sortBy('id')->values();
            $cargosWithoutOrder = TripCargo::where('trip_id', $this->trip->id)
                ->whereNull('transport_order_id')
                ->orderBy('id')
                ->get();
            foreach ($cargosWithoutOrder as $i => $cargo) {
                if (isset($ordersSorted[$i])) {
                    $cargo->update(['transport_order_id' => $ordersSorted[$i]->id]);
                }
            }

            $synced = false;
            foreach ($orders as $order) {
                $hasCargo = TripCargo::where('trip_id', $this->trip->id)
                    ->where('transport_order_id', $order->id)
                    ->exists();
                if (!$hasCargo) {
                    $order->load(['steps' => fn ($q) => $q->orderBy('order')], 'cargos');
                    $this->appendOrderStepsAndCargosToTrip($order);
                    $synced = true;
                }
            }

            if ($synced) {
                $this->trip->refresh();
                $this->trip->unsetRelation('steps');
                $this->trip->unsetRelation('cargos');
                $this->trip->unsetRelation('transportOrders');
                $this->trip->load([
                    'driver', 'truck', 'trailer',
                    'transportOrders.expeditor', 'transportOrders.customer', 'transportOrders.steps',
                    'cargos.shipper', 'cargos.consignee', 'cargos.customer', 'cargos.items', 'cargos.steps.documents',
                    'steps.cargos',
                ]);
                $this->hydrateCargoInputsFromTrip();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Добавить выбранные заказы в рейс (confirmed, без trip_id). Статус заказов → CONVERTED.
     * Для каждого заказа создаются шаги (TripStep) и грузы (TripCargo + TripCargoItem) из данных заказа.
     */
    public function addOrdersToTrip(): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $this->add_orders_selection))));
        if (empty($ids)) {
            return;
        }

        $orders = TransportOrder::with(['steps' => fn ($q) => $q->orderBy('order')], 'cargos')
            ->whereIn('id', $ids)
            ->whereIn('status', ['draft', 'quoted', 'confirmed'])
            ->whereNull('trip_id')
            ->get();

        if ($orders->isEmpty()) {
            $this->add_orders_selection = [];
            return;
        }

        try {
            DB::beginTransaction();

            foreach ($orders as $order) {
                $this->appendOrderStepsAndCargosToTrip($order);
            }

            $processedIds = $orders->pluck('id')->toArray();
            if (!empty($processedIds)) {
                TransportOrder::whereIn('id', $processedIds)->update([
                    'trip_id' => $this->trip->id,
                    'status'  => OrderStatus::CONVERTED->value,
                ]);
            }

            DB::commit();
            $this->add_orders_selection = [];
            $this->reloadTrip();
            session()->flash('success', __('app.trip.show.add_orders_done'));
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', __('app.trip.show.add_orders_error'));
        }
    }

    /**
     * Удалить заказ из рейса без подтверждения: отвязать заказ, затем удалить его грузы и шаги.
     */
    public function removeOrderFromTrip(int $orderId): void
    {
        $orderId = (int) $orderId;
        $order = TransportOrder::where('id', $orderId)->where('trip_id', $this->trip->id)->first();
        if (!$order) {
            return;
        }

        try {
            DB::beginTransaction();

            // Сначала отвязываем заказ — тогда он сразу пропадёт из списка
            $order->update([
                'trip_id' => null,
                'status'  => OrderStatus::CONFIRMED->value,
            ]);

            // Затем удаляем грузы и шаги заказа (если есть колонка transport_order_id)
            if (Schema::hasColumn('trip_cargos', 'transport_order_id')) {
                $cargos = TripCargo::where('trip_id', $this->trip->id)
                    ->where('transport_order_id', $orderId)
                    ->get();

                $stepIdsToCheck = [];
                foreach ($cargos as $cargo) {
                    $stepIdsToCheck = array_merge($stepIdsToCheck, $cargo->steps()->get()->pluck('id')->toArray());
                    $cargo->invoice?->delete();
                    $cargo->items()->delete();
                    $cargo->steps()->detach();
                    $cargo->delete();
                }
                $stepIdsToCheck = array_values(array_unique(array_filter($stepIdsToCheck)));

                foreach ($stepIdsToCheck as $stepId) {
                    $step = TripStep::where('trip_id', $this->trip->id)->find($stepId);
                    if ($step && $step->cargos()->count() === 0) {
                        foreach ($step->stepDocuments as $doc) {
                            try {
                                if (!empty($doc->file_path)) {
                                    \Storage::disk('public')->delete($doc->file_path);
                                }
                            } catch (\Throwable $storageEx) {
                                report($storageEx);
                            }
                            $doc->delete();
                        }
                        $step->delete();
                    }
                }
            }

            DB::commit();
            $this->reloadTrip();
            session()->flash('success', __('app.trip.show.remove_order_done'));
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $msg = __('app.trip.show.remove_order_error');
            if (config('app.debug')) {
                $msg .= ' ' . $e->getMessage();
            }
            session()->flash('error', $msg);
        }
    }

    /**
     * Create TripSteps and TripCargos from one TransportOrder and attach to current trip.
     */
    protected function appendOrderStepsAndCargosToTrip(TransportOrder $order): void
    {
        $trip = $this->trip;
        $maxOrder = (int) $trip->steps()->max('order');

        $newStepIdsByType = ['loading' => null, 'unloading' => null];

        foreach ($order->steps as $os) {
            $maxOrder++;
            $dbStep = TripStep::create([
                'trip_id'          => $trip->id,
                'order'            => $maxOrder,
                'type'             => $os->type ?? 'loading',
                'country_id'       => $os->country_id,
                'city_id'          => $os->city_id,
                'address'          => $os->address,
                'contact_phone_1'   => $os->contact_phone,
                'contact_phone_2'  => null,
                'date'             => $os->date,
                'time'             => $os->time,
                'notes'            => $os->notes,
            ]);
            $type = $os->type ?? 'loading';
            if (($type === 'loading' && $newStepIdsByType['loading'] === null) || ($type === 'unloading' && $newStepIdsByType['unloading'] === null)) {
                $newStepIdsByType[$type] = $dbStep->id;
            }
        }

        $loadingStepId = $newStepIdsByType['loading'] ?? $trip->steps()->where('type', 'loading')->orderBy('order')->value('id');
        $unloadingStepId = $newStepIdsByType['unloading'] ?? $trip->steps()->where('type', 'unloading')->orderBy('order')->value('id');

        $customerId = $order->customer_id;
        if ($customerId === null && $order->cargos->isNotEmpty()) {
            $customerId = $order->cargos->first()->customer_id;
        }

        foreach ($order->cargos as $oc) {
            $price = (float) ($oc->quoted_price ?? 0);
            $taxPercent = 21.0;
            $tax = CalculateTax::calculate($price, $taxPercent);

            $cargo = TripCargo::create([
                'trip_id'             => $trip->id,
                'transport_order_id'  => $order->id,
                'customer_id'         => $oc->customer_id,
                'shipper_id'          => $oc->shipper_id ?? $oc->customer_id,
                'consignee_id'        => $oc->consignee_id ?? $oc->customer_id,
                'price'               => $price,
                'tax_percent'         => $taxPercent,
                'total_tax_amount'    => $tax['tax_amount'],
                'price_with_tax'      => $tax['price_with_tax'],
                'currency'            => 'EUR',
                'payment_terms'       => null,
                'payment_days'        => 30,
                'payer_type_id'       => null,
                'commercial_invoice_nr' => null,
                'commercial_invoice_amount' => null,
            ]);

            $cargo->items()->create([
                'description'     => $oc->description ?? '',
                'customs_code'    => $oc->customs_code,
                'packages'        => (int) ($oc->packages ?? 0),
                'pallets'         => (int) ($oc->pallets ?? 0),
                'units'           => (int) ($oc->units ?? 0),
                'net_weight'      => (float) ($oc->net_weight ?? $oc->weight_kg ?? 0),
                'gross_weight'    => (float) ($oc->gross_weight ?? $oc->weight_kg ?? 0),
                'tonnes'          => (float) ($oc->tonnes ?? 0),
                'volume'          => (float) ($oc->volume_m3 ?? 0),
                'loading_meters'  => (float) ($oc->loading_meters ?? 0),
                'hazmat'          => $oc->hazmat ?? '',
                'temperature'     => $oc->temperature ?? '',
                'stackable'       => (bool) ($oc->stackable ?? false),
                'instructions'    => $oc->instructions ?? '',
                'remarks'         => $oc->remarks ?? '',
            ]);

            $pivot = [];
            if ($loadingStepId) {
                $pivot[$loadingStepId] = ['role' => 'loading'];
            }
            if ($unloadingStepId && $unloadingStepId !== $loadingStepId) {
                $pivot[$unloadingStepId] = ['role' => 'unloading'];
            }
            if ($pivot) {
                $cargo->steps()->attach($pivot);
            }
        }

        if ($order->cargos->isEmpty()) {
            $cargo = TripCargo::create([
                'trip_id'             => $trip->id,
                'transport_order_id'  => $order->id,
                'customer_id'         => $customerId,
                'shipper_id'          => $customerId,
                'consignee_id'        => $customerId,
                'price'               => 0,
                'tax_percent'         => 21.0,
                'total_tax_amount'    => 0,
                'price_with_tax'      => 0,
                'currency'            => 'EUR',
                'payment_terms'       => null,
                'payment_days'        => 30,
                'payer_type_id'       => null,
                'commercial_invoice_nr' => null,
                'commercial_invoice_amount' => null,
            ]);
            $cargo->items()->create([
                'description' => '', 'customs_code' => null, 'packages' => 0, 'pallets' => 0, 'units' => 0,
                'net_weight' => 0, 'gross_weight' => 0, 'tonnes' => 0, 'volume' => 0, 'loading_meters' => 0,
                'hazmat' => '', 'temperature' => '', 'stackable' => false, 'instructions' => '', 'remarks' => '',
            ]);
            $pivot = [];
            if ($loadingStepId) {
                $pivot[$loadingStepId] = ['role' => 'loading'];
            }
            if ($unloadingStepId && $unloadingStepId !== $loadingStepId) {
                $pivot[$unloadingStepId] = ['role' => 'unloading'];
            }
            if ($pivot) {
                $cargo->steps()->attach($pivot);
            }
        }
    }

    public function uploadStepDocument(int $stepId)
    {
        $this->validate([
            "stepDocFile.$stepId"    => 'required|file|mimes:jpg,jpeg,png,gif,webp',
            "stepDocType.$stepId"    => 'nullable|string|max:255',
            "stepDocComment.$stepId" => 'nullable|string|max:1000',
        ]);

        $file = $this->stepDocFile[$stepId];
        $path = \App\Helpers\ImageCompress::storeUpload($file, "trip_steps/$stepId", 'public');

        TripStepDocument::create([
            'trip_step_id'       => $stepId,
            'trip_id'            => $this->trip->id,
            'cargo_id'           => null,
            'uploader_user_id'   => auth()->id(),
            'uploader_driver_id' => null,
            'type'               => $this->stepDocType[$stepId] ?? null,
            'file_path'          => $path,
            'original_name'      => $file->getClientOriginalName(),
            'comment'            => $this->stepDocComment[$stepId] ?? null,
        ]);

        unset($this->stepDocFile[$stepId], $this->stepDocComment[$stepId]);

        $this->trip->load(['cargos.steps.documents']);
        $this->dispatch('stepDocumentUploaded');
    }

    public function deleteStepDocument(int $docId)
    {
        $doc = TripStepDocument::findOrFail($docId);
        abort_if((int) $doc->trip_id !== (int) $this->trip->id, 403);

        \Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        $this->trip->load(['cargos.steps.documents']);
        $this->dispatch('stepDocumentDeleted');
    }

    private function validateDocNr(string $field, int $cargoId): string
    {
        $this->validate([
            "{$field}.{$cargoId}" => 'required|string|max:191',
        ], [
            "{$field}.{$cargoId}.required" => __('app.trip.show.doc_nr_required'),
        ]);

        $val = trim((string) data_get($this->{$field}, $cargoId, ''));

        if ($val === '') {
            $this->addError("{$field}.{$cargoId}", __('app.trip.show.doc_nr_required'));
        }

        return $val;
    }

    public function generateCmr(int $cargoId): void
    {
        $nr = $this->validateDocNr('cmrNr', $cargoId);

        $cargo = TripCargo::findOrFail($cargoId);
        $cargo->update([
            'cmr_nr' => $nr, // ✅ только cmr_nr
        ]);

        $url = app(CmrController::class)->generateAndSave($cargo);

        $this->reloadTrip();
        $this->dispatch('cmrGenerated', url: $url);
    }

    public function generateOrder(int $cargoId): void
    {
        $nr = $this->validateDocNr('orderNr', $cargoId);

        $cargo = TripCargo::findOrFail($cargoId);
        $cargo->update([
            'order_nr' => $nr,
        ]);

        $url = app(CmrController::class)->generateTransportOrder($cargo);

        $this->reloadTrip();
        $this->dispatch('orderGenerated', url: $url);
    }

    public function generateInvoice(int $cargoId): void
    {
        $nr = $this->validateDocNr('invNr', $cargoId);

        $cargo = TripCargo::findOrFail($cargoId);
        $cargo->update([
            'inv_nr' => $nr, // ✅ только inv_nr
        ]);

        $url = app(CmrController::class)->generateInvoice($cargo);

        $this->reloadTrip();
        $this->dispatch('invoiceGenerated', url: $url);
    }

    /**
     * Save delay (Dikstāve) for a cargo. Saves only when checkbox is checked and days/amount are filled.
     * When checkbox is unchecked: updates DB only if there was saved delay data to clear.
     */
    public function saveDelay(int $cargoId): void
    {
        $checked = (bool) ($this->delayChecked[$cargoId] ?? false);

        $cargo = TripCargo::findOrFail($cargoId);
        if ((int) $cargo->trip_id !== (int) $this->trip->id) {
            abort(403);
        }

        if ($checked) {
            $this->validate([
                "delayDays.{$cargoId}"   => 'required|integer|min:1|max:365',
                "delayAmount.{$cargoId}" => 'required|numeric|min:0',
            ], [
                "delayDays.{$cargoId}.required"   => __('app.trip.show.delay_days_required'),
                "delayAmount.{$cargoId}.required" => __('app.trip.show.delay_amount_required'),
            ]);
            $days = (int) $this->delayDays[$cargoId];
            $amount = (float) str_replace(',', '.', (string) $this->delayAmount[$cargoId]);
        } else {
            // Сняли галочку: сохраняем в БД только если был сохранённый простой (есть что очищать)
            $hadDelay = (bool) ($cargo->has_delay ?? false)
                || $cargo->delay_days !== null
                || $cargo->delay_amount !== null;
            if (!$hadDelay) {
                return; // Ничего не было сохранено — не пишем в БД и не показываем тост
            }
            $days = null;
            $amount = null;
        }

        $cargo->update([
            'has_delay'    => $checked,
            'delay_days'   => $days,
            'delay_amount' => $amount,
            // Инвалидация инвойса: после изменения простоя PDF устарел — нужна перегенерация
            'inv_file'       => null,
            'inv_created_at' => null,
        ]);
        Invoice::where('trip_cargo_id', $cargo->id)->update(['pdf_file' => null]);

        $this->reloadTrip();
        $this->dispatch($checked ? 'delaySaved' : 'delayRemoved');
    }

    /**
     * Удалить Dikstāve по грузу: очистить данные и инвалидировать инвойс.
     */
    public function removeDelay(int $cargoId): void
    {
        try {
            $cargo = TripCargo::findOrFail($cargoId);
            if ((int) $cargo->trip_id !== (int) $this->trip->id) {
                abort(403);
            }

            $cargo->update([
                'has_delay'       => false,
                'delay_days'      => null,
                'delay_amount'    => null,
                'inv_file'        => null,
                'inv_created_at'  => null,
            ]);
            Invoice::where('trip_cargo_id', $cargo->id)->update(['pdf_file' => null]);

            $this->delayChecked[$cargoId] = false;
            $this->delayDays[$cargoId]    = '';
            $this->delayAmount[$cargoId]  = '';
            $this->reloadTrip();
            $this->dispatch('delayRemoved');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('delayRemoveError');
        }
    }

    public function render()
    {
        $availableOrdersForTrip = TransportOrder::with(['expeditor', 'customer', 'steps', 'cargos.shipper', 'cargos.consignee'])
            ->whereIn('status', ['draft', 'quoted', 'confirmed'])
            ->whereNull('trip_id')
            ->orderBy('order_date')
            ->orderBy('id')
            ->get();

        return view('livewire.trips.view-trip', [
            'trip'                    => $this->trip,
            'availableOrdersForTrip'  => $availableOrdersForTrip,
        ])
            ->layout('layouts.app', [
            'title' => __('app.trip.show.title'),
        ]);
    }
}
