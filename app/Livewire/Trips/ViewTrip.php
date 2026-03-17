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
use App\Services\OpenRouteService;
use App\Services\HereRouteService;

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

    /** Расчёт маршрута по шагам рейса (OpenRouteService или HERE) */
    public ?array $routeSummary = null;
    public ?string $routeSummaryError = null;
    public bool $routeCalcConfigHint = false;
    public ?string $routeProviderKey = null;
    public ?string $routeProviderLink = null;
    public bool $routeSummaryLoading = false;

    /** Оптимальный маршрут (минимальный километраж при другой последовательности точек) */
    public ?array $routeSummaryOptimal = null;
    /** Подписи шагов в порядке оптимального маршрута (для вывода "A → B → C") */
    public array $routeSuggestedOrderLabels = [];
    public ?string $routeOptimalError = null;
    public bool $routeOptimalLoading = false;
    /** Экономия км при переходе на оптимальный порядок (может быть 0 или отрицательной) */
    public ?float $savedKm = null;

    /**
     * Форматирует длительность в минутах: "X d Y h Z min".
     */
    public function formatRouteDuration(float $minutes): string
    {
        $total = (int) round($minutes);
        $days = (int) ($total / (24 * 60));
        $rest = $total % (24 * 60);
        $hours = (int) ($rest / 60);
        $mins = $rest % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' ' . __('app.orders.route_calc.duration_d');
        }
        if ($hours > 0 || $days > 0) {
            $parts[] = $hours . ' ' . __('app.orders.route_calc.duration_h');
        }
        $parts[] = $mins . ' ' . __('app.orders.route_calc.duration_min');

        return implode(' ', $parts);
    }

    /** Рассчитать километраж и время по маршруту рейса (все шаги по порядку). */
    public function calculateRouteDistance(): void
    {
        $this->routeSummary = null;
        $this->routeSummaryError = null;
        $this->routeCalcConfigHint = false;
        $this->routeProviderKey = null;
        $this->routeProviderLink = null;
        $this->routeSummaryLoading = true;

        $serviceClass = config('route.provider') === 'here' ? HereRouteService::class : OpenRouteService::class;
        $service = app($serviceClass);
        if (!$service->isConfigured()) {
            $this->routeProviderKey = config('route.provider') === 'here' ? 'HERE_API_KEY' : 'OPENROUTESERVICE_API_KEY';
            $this->routeSummaryError = __('app.orders.route_calc.not_configured', ['key' => $this->routeProviderKey]);
            $this->routeCalcConfigHint = true;
            $this->routeProviderLink = config('route.provider') === 'here' ? 'https://developer.here.com' : 'https://openrouteservice.org/dev/#/login';
            $this->routeSummaryLoading = false;
            return;
        }

        $steps = $this->trip->steps()->orderBy('order')->orderBy('id')->get();
        if ($steps->count() < 2) {
            $this->routeSummaryError = __('app.orders.route_calc.need_two_steps');
            $this->routeSummaryLoading = false;
            return;
        }

        $result = $service->getRouteSummaryFromSteps($steps);
        $this->routeSummaryLoading = false;

        if (!empty($result['error'])) {
            $errorKey = $result['error_key'] ?? null;
            if ($errorKey === 'distance_limit') {
                $this->routeSummaryError = __('app.orders.route_calc.distance_limit');
                return;
            }
            if ($errorKey === 'point_not_routable') {
                $this->routeSummaryError = __('app.orders.route_calc.point_not_routable');
                return;
            }
            if (!empty($result['directions_failed'])) {
                $this->routeSummaryError = __('app.orders.route_calc.directions_failed');
                return;
            }
            $order = (int) ($result['failed_step_order'] ?? 0);
            if ($order < 1) {
                $this->routeSummaryError = __('app.orders.route_calc.failed');
                return;
            }
            $address = trim((string) ($result['failed_address'] ?? ''));
            $address = $address !== '' ? $address : __('app.orders.route_calc.empty_address');
            $this->routeSummaryError = __('app.orders.route_calc.failed_step', ['order' => $order, 'address' => $address]);
            return;
        }

        $this->routeSummary = $result;
    }

    /** Найти более короткий маршрут (эвристика TSP: nearest neighbour) и сравнить с текущим порядком. */
    public function calculateOptimalRoute(): void
    {
        $this->routeSummaryOptimal = null;
        $this->routeSuggestedOrderLabels = [];
        $this->routeOptimalError = null;
        $this->savedKm = null;
        $this->routeOptimalLoading = true;

        $serviceClass = config('route.provider') === 'here' ? HereRouteService::class : OpenRouteService::class;
        $service = app($serviceClass);
        if (!$service->isConfigured()) {
            $this->routeOptimalError = __('app.orders.route_calc.not_configured', [
                'key' => config('route.provider') === 'here' ? 'HERE_API_KEY' : 'OPENROUTESERVICE_API_KEY',
            ]);
            $this->routeOptimalLoading = false;
            return;
        }

        $steps = $this->trip->steps()->orderBy('order')->orderBy('id')->get();
        if ($steps->count() < 2) {
            $this->routeOptimalError = __('app.orders.route_calc.need_two_steps');
            $this->routeOptimalLoading = false;
            return;
        }

        // Текущий километраж: если ещё не считали — один раз посчитать
        if ($this->routeSummary === null || !empty($this->routeSummary['error'])) {
            $currentResult = $service->getRouteSummaryFromSteps($steps);
            if (!empty($currentResult['error'])) {
                $this->routeOptimalError = $this->formatRouteError($currentResult);
                $this->routeOptimalLoading = false;
                return;
            }
            $this->routeSummary = $currentResult;
        }

        $currentKm = (float) ($this->routeSummary['distance_km'] ?? 0);

        $optimalResult = $service->getOptimalRouteFromSteps($steps);
        $this->routeOptimalLoading = false;

        if (!empty($optimalResult['error'])) {
            $this->routeOptimalError = $this->formatRouteError($optimalResult);
            return;
        }

        $this->routeSummaryOptimal = [
            'distance_km' => $optimalResult['distance_km'],
            'duration_minutes' => $optimalResult['duration_minutes'],
        ];
        $optimalKm = (float) $optimalResult['distance_km'];
        $this->savedKm = round($currentKm - $optimalKm, 1);

        $suggestedSteps = $optimalResult['suggested_steps'] ?? [];
        $this->routeSuggestedOrderLabels = array_map(function ($step) {
            return method_exists($step, 'addressLine') ? $step->addressLine() : (string) $step;
        }, $suggestedSteps);
    }

    private function formatRouteError(array $result): string
    {
        $key = $result['error_key'] ?? null;
        if ($key === 'distance_limit') {
            return __('app.orders.route_calc.distance_limit');
        }
        if ($key === 'point_not_routable') {
            return __('app.orders.route_calc.point_not_routable');
        }
        if (!empty($result['directions_failed'])) {
            return __('app.orders.route_calc.directions_failed');
        }
        $order = (int) ($result['failed_step_order'] ?? 0);
        if ($order < 1) {
            return __('app.orders.route_calc.failed');
        }
        $address = trim((string) ($result['failed_address'] ?? ''));
        $address = $address !== '' ? $address : __('app.orders.route_calc.empty_address');
        return __('app.orders.route_calc.failed_step', ['order' => $order, 'address' => $address]);
    }

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
                    $this->trip->appendOrder($order);
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
                $this->trip->appendOrder($order);
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
