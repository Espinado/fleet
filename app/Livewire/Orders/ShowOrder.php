<?php

namespace App\Livewire\Orders;

use App\Enums\TripStatus;
use App\Models\Trip;
use App\Models\TransportOrder;
use App\Services\OpenRouteService;
use App\Services\HereRouteService;
use App\Services\GoogleMapsRouteService;
use Livewire\Component;

class ShowOrder extends Component
{
    public TransportOrder $transportOrder;

    /** Открыта ли модалка «Добавить в существующий рейс». */
    public bool $showAddToTripModal = false;

    /** Список рейсов, в которые можно добавить заказ (не завершённые). */
    public $availableTrips = [];

    /** ID рейса, для которого идёт добавление заказа (спиннер только на этой кнопке). */
    public ?int $addingTripId = null;

    /** Результат расчёта километража по маршруту: ['distance_km' => float, 'duration_minutes' => float] или null. */
    public ?array $routeSummary = null;

    /** Сообщение об ошибке расчёта маршрута. */
    public ?string $routeSummaryError = null;

    /** Показать подсказку «как настроить API key» (когда сервис не настроен). */
    public bool $routeCalcConfigHint = false;
    public ?string $routeProviderKey = null;
    public ?string $routeProviderLink = null;

    /** Идёт ли запрос расчёта маршрута. */
    public bool $routeSummaryLoading = false;

    /**
     * Форматирует длительность в минутах в строку: "X d Y h Z min" (дни, часы, минуты).
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

    public function mount(TransportOrder $transportOrder): void
    {
        $this->transportOrder = $transportOrder->load([
            'expeditor:id,name,reg_nr',
            'customer:id,company_name',
            'steps',
            'cargos.customer:id,company_name',
            'cargos.shipper:id,company_name',
            'cargos.consignee:id,company_name',
            'trip:id',
        ]);
    }

    /** Показать модалку и загрузить список незавершённых рейсов. */
    public function openAddToTripModal(): void
    {
        $this->availableTrips = Trip::query()
            ->whereNotIn('status', [TripStatus::COMPLETED, TripStatus::CANCELLED])
            ->with(['carrierCompany:id,name', 'driver:id,first_name,last_name'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();
        $this->showAddToTripModal = true;
    }

    public function closeAddToTripModal(): void
    {
        $this->showAddToTripModal = false;
        $this->availableTrips = [];
    }

    /** Добавить текущий заказ в выбранный рейс. Один заказ — только один рейс (проверка trip_id). */
    public function addOrderToTrip(int $tripId): void
    {
        $this->addingTripId = $tripId;

        $trip = Trip::find($tripId);
        if (!$trip || in_array($trip->status, [TripStatus::COMPLETED, TripStatus::CANCELLED], true)) {
            $this->addingTripId = null;
            session()->flash('error', __('app.orders.add_to_trip.not_finished'));
            return;
        }
        if ($this->transportOrder->trip_id) {
            $this->addingTripId = null;
            session()->flash('error', __('app.orders.add_to_trip.already_has_trip'));
            return;
        }
        $status = $this->transportOrder->status instanceof \App\Enums\OrderStatus
            ? $this->transportOrder->status->value
            : (string) $this->transportOrder->status;
        if (!in_array($status, ['draft', 'quoted', 'confirmed'], true)) {
            $this->addingTripId = null;
            session()->flash('error', __('app.orders.add_to_trip.invalid_status'));
            return;
        }

        $this->transportOrder->loadMissing(['steps' => fn ($q) => $q->orderBy('order')], 'cargos');
        $trip->appendOrder($this->transportOrder);

        $this->addingTripId = null;
        $this->closeAddToTripModal();
        $this->transportOrder->refresh();
        $this->transportOrder->load('trip:id');

        session()->flash('success', __('app.orders.add_to_trip.success'));
        $this->redirect(route('trips.show', $trip), navigate: true);
    }

    /** Рассчитать километраж и время по маршруту заказа (Google Maps / HERE / OpenRouteService по ROUTE_PROVIDER). */
    public function calculateRouteDistance(): void
    {
        $this->routeSummary = null;
        $this->routeSummaryError = null;
        $this->routeCalcConfigHint = false;
        $this->routeProviderKey = null;
        $this->routeProviderLink = null;
        $this->routeSummaryLoading = true;

        $provider = config('route.provider');
        $serviceClass = match ($provider) {
            'google' => GoogleMapsRouteService::class,
            'here' => HereRouteService::class,
            default => OpenRouteService::class,
        };
        $service = app($serviceClass);
        if (!$service->isConfigured()) {
            $this->routeProviderKey = match ($provider) {
                'google' => 'GOOGLE_MAPS_API_KEY',
                'here' => 'HERE_API_KEY',
                default => 'OPENROUTESERVICE_API_KEY',
            };
            $this->routeSummaryError = __('app.orders.route_calc.not_configured', ['key' => $this->routeProviderKey]);
            $this->routeCalcConfigHint = true;
            $this->routeProviderLink = match ($provider) {
                'google' => 'https://console.cloud.google.com/apis/credentials',
                'here' => 'https://developer.here.com',
                default => 'https://openrouteservice.org/dev/#/login',
            };
            $this->routeSummaryLoading = false;
            return;
        }

        $this->transportOrder->loadMissing(['steps' => fn ($q) => $q->orderBy('order')]);
        $steps = $this->transportOrder->steps;
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

    public function render()
    {
        return view('livewire.orders.show-order')
            ->layout('layouts.app', [
                'title' => $this->transportOrder->number . ' — ' . __('app.orders.show.title'),
            ]);
    }
}
