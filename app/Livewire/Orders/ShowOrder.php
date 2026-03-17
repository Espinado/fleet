<?php

namespace App\Livewire\Orders;

use App\Enums\TripStatus;
use App\Models\Trip;
use App\Models\TransportOrder;
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

    public function render()
    {
        return view('livewire.orders.show-order')
            ->layout('layouts.app', [
                'title' => $this->transportOrder->number . ' — ' . __('app.orders.show.title'),
            ]);
    }
}
