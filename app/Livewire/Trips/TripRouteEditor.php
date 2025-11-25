<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\Attributes\On;
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

        // автосортировка только один раз, если все order = null
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
                $country = config("countries.{$s->country_id}.name") ?? '—';

                $city = '—';
                if ($s->country_id && $s->city_id) {
                    $cities = getCitiesByCountryId($s->country_id);
                    $city = $cities[$s->city_id]['name'] ?? '—';
                }

                return [
                    'id'      => $s->id,
                    'type'    => $s->type,
                    'country' => $country,
                    'city'    => $city,
                    'address' => $s->address ?? '—',
                    'date'    => optional($s->date)->format('d.m.Y'),
                    'time'    => $s->time ? date('H:i', strtotime($s->time)) : null,
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
            \Log::info("Updating step", ['id' => $id, 'new_order' => $i+1]);

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
