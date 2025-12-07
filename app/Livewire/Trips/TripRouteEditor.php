<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\Trip;
use App\Models\TripStep;
use App\Helpers\TripStepSorter;

class TripRouteEditor extends Component
{
    public int $tripId;

    public array $steps = [];
    public bool $readonly = false;

    public function mount(int $tripId)
    {
        $this->tripId = $tripId;

        $trip = Trip::with('steps')->findOrFail($tripId);

        // водитель не может сортировать
        $this->readonly = auth()->user()?->role === 'driver';

        // автосортировка — только если не readonly
        if (!$this->readonly && !$this->hasManualOrder()) {
            $this->autoSort($trip);
        }

        $this->loadSteps();
    }

    private function hasManualOrder(): bool
    {
        return TripStep::where('trip_id', $this->tripId)
            ->whereNotNull('order')
            ->where('order', '>', 0)
            ->exists();
    }

    private function autoSort(Trip $trip): void
    {
        $sorted = TripStepSorter::sort($trip->steps);

        foreach ($sorted as $i => $step) {
            $step->update(['order' => $i + 1]);
        }
    }

    private function loadSteps(): void
    {
        $this->steps = TripStep::where('trip_id', $this->tripId)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function (TripStep $s) {

                return [
                    'id'      => $s->id,
                    'type'    => $s->type,
                    'country' => config("countries.{$s->country_id}.name") ?? '—',
                    'city'    => $s->city_id ? (getCitiesByCountryId($s->country_id)[$s->city_id]['name'] ?? '—') : '—',
                    'address' => $s->address ?? '—',
                    'date'    => optional($s->date)->format('d.m.Y'),
                    'time'    => $s->time ? date('H:i', strtotime($s->time)) : null,

                    // ВАЖНО — статус шага
                    'status'  => $s->status?->value,

                    // Заблокирован ли шаг
                    'locked'  => in_array($s->status?->value, [4, 5]), // PROCESSING / COMPLETED
                ];
            })
            ->toArray();
    }

    public function updateOrder($data = null)
    {
        if ($this->readonly) return;

        if (!$data || !isset($data['orderedIds'])) {
            return;
        }

        $ids = $data['orderedIds'];

        foreach (array_values($ids) as $i => $id) {

            TripStep::where('id', $id)
                ->where('trip_id', $this->tripId)
                ->update(['order' => $i + 1]);
        }

        $this->loadSteps();
        $this->dispatch('order-updated');
    }

    public function render()
    {
        return view('livewire.trips.trip-route-editor');
    }
}
