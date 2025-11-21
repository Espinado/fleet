<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Trip;
use App\Models\TripStep;

class TripRouteEditor extends Component
{
    public int $tripId;
    public Trip $trip;
    public array $steps = [];
    public bool $readonly = false;

    public function mount(int $tripId)
    {
        $this->tripId = $tripId;
        $this->trip = Trip::findOrFail($tripId);

        // 🔒 Водитель не должен менять порядок
        $this->readonly = auth()->user()?->driver ? true : false;

        // 🧠 Авто-сортировка только если порядок ещё НИ РАЗУ не задавался вручную
        if (!$this->readonly && !$this->isManuallySorted()) {
            $this->autoSortSteps();
        }

        $this->loadSteps();
    }

    /**
     * Проверка: есть ли хотя бы один пустой order?
     * Если нет — значит порядок установлен вручную.
     */
private function isManuallySorted(): bool
{
    $steps = TripStep::where('trip_id', $this->tripId)->get();

    return $steps->contains(fn($s) => $s->order > 0);
}

    /**
     * Автоматическая первичная сортировка (до первой ручной)
     */
    private function autoSortSteps()
    {
        $steps = TripStep::where('trip_id', $this->tripId)->get();
        if ($steps->isEmpty()) return;

        $sorted = $steps->sort(function ($a, $b) {

            // 1) сортируем по дате
            $dateCmp = strtotime($a->date) <=> strtotime($b->date);
            if ($dateCmp !== 0) return $dateCmp;

            // 2) loading → unloading
            if ($a->type !== $b->type) {
                return $a->type === 'loading' ? -1 : 1;
            }

            // 3) сортировка по cargo
            return $a->trip_cargo_id <=> $b->trip_cargo_id;

        })->values();

        foreach ($sorted as $i => $step) {
            $step->update(['order' => $i + 1]);
        }
    }

    /**
     * Загружаем шаги по order
     */
    private function loadSteps()
    {
        $this->steps = TripStep::where('trip_id', $this->tripId)
            ->orderBy('order')
            ->get()
            ->map(function ($s) {

                $countryName = $s->country_id
                    ? config("countries.{$s->country_id}.name") ?? '—'
                    : '—';

                $cityName = '—';
                if ($s->country_id && $s->city_id) {
                    $cities = getCitiesByCountryId($s->country_id);
                    $cityName = $cities[$s->city_id]['name'] ?? '—';
                }

                return [
                    'id'      => $s->id,
                    'type'    => $s->type,
                    'country' => $countryName,
                    'city'    => $cityName,
                    'address' => $s->address,
                    'date'    => optional($s->date)->format('d.m.Y'),
                ];
            })
            ->toArray();
    }

    /**
     * Drag&Drop reorder (только админ)
     */
    #[On('stepOrderChanged')]
    public function updateOrder($data = [])
    {
        // водитель не может менять порядок
        if ($this->readonly) return;
logger()->info('ORDER IDS FROM UI', $data['ids'] ?? []);
        $orderedIds = $data['ids'] ?? [];

        foreach ($orderedIds as $index => $id) {
            TripStep::where('id', $id)->update([
                'order' => $index + 1,
            ]);
        }

        session()->flash('success', 'Маршрут обновлён!');

        $this->loadSteps();
    }

    public function render()
    {
        return view('livewire.trips.trip-route-editor');
    }
}
