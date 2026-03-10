<?php

namespace App\Livewire\Map;

use Livewire\Component;
use App\Services\Services\MaponService;
use Illuminate\Support\Facades\Cache;

class FleetMap extends Component
{
    /** @var array<int, array{unit_id: int, number: string, lat: float, lng: float, state_name: string, speed: float|null, tooltip: string}> */
    public array $unitsForMap = [];

    public string $mapTitle = '';

    public function mount(): void
    {
        $this->mapTitle = (string) __('app.map.title');
        $this->loadUnits();
    }

    public function refreshUnits(): void
    {
        Cache::forget('mapon:all_units');
        $this->loadUnits();
    }

    protected function loadUnits(): void
    {
        $raw = Cache::remember('mapon:all_units', now()->addMinutes(2), function () {
            /** @var MaponService $svc */
            $svc = app(MaponService::class);
            return $svc->getAllUnits();
        });

        $this->unitsForMap = [];
        $standingLabel = (string) __('app.truck.show.mapon_standing');
        $movingLabel = (string) __('app.truck.show.mapon_moving');
        $kmhLabel = (string) __('app.truck.show.mapon_kmh');

        foreach ($raw as $unit) {
            if (!is_array($unit)) {
                continue;
            }
            $lat = isset($unit['lat']) ? (float) $unit['lat'] : null;
            $lng = isset($unit['lng']) ? (float) $unit['lng'] : null;
            if ($lat === null || $lng === null) {
                continue;
            }
            $number = (string) ($unit['number'] ?? $unit['label'] ?? (string) ($unit['unit_id'] ?? '—'));
            $stateName = (string) ($unit['state']['name'] ?? $unit['movement_state']['name'] ?? 'standing');
            $speed = isset($unit['speed']) && $unit['speed'] !== null
                ? (float) $unit['speed']
                : null;

            if ($stateName === 'moving' && $speed !== null) {
                $tooltip = $number . ' — ' . $movingLabel . ', ' . (int) round($speed) . ' ' . $kmhLabel;
            } elseif ($stateName === 'moving') {
                $tooltip = $number . ' — ' . $movingLabel;
            } else {
                $tooltip = $number . ' — ' . $standingLabel;
            }

            $this->unitsForMap[] = [
                'unit_id'    => (int) ($unit['unit_id'] ?? 0),
                'number'     => $number,
                'lat'        => $lat,
                'lng'       => $lng,
                'state_name' => $stateName,
                'speed'      => $speed,
                'tooltip'    => $tooltip,
            ];
        }
    }

    public function render()
    {
        return view('livewire.map.fleet-map')
            ->layout('layouts.app', [
                'title' => $this->mapTitle,
            ]);
    }
}
