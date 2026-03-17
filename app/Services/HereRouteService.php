<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HERE Routing & Geocoding (https://developer.here.com).
 * Same public interface as OpenRouteService for drop-in replacement.
 * No 6000 km limit on public HERE Routing API.
 */
class HereRouteService
{
    protected string $apiKey;
    protected string $geocodeUrl;
    protected string $routingUrl;
    protected int $timeout;
    protected string $transportMode;

    public function __construct()
    {
        $this->apiKey = config('here.api_key', '');
        $this->geocodeUrl = rtrim(config('here.geocode_url', 'https://geocode.search.hereapi.com/v1/geocode'), '?');
        $this->routingUrl = rtrim(config('here.routing_url', 'https://router.hereapi.com/v8/routes'), '?');
        $this->timeout = config('here.timeout', 30);
        $this->transportMode = config('here.transport_mode', 'truck');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public static function stepToAddressString(object $step): string
    {
        return OpenRouteService::stepToAddressString($step);
    }

    public static function stepToFallbackAddressString(object $step): string
    {
        return OpenRouteService::stepToFallbackAddressString($step);
    }

    /**
     * Geocode address to [lon, lat] (same format as OpenRouteService).
     * HERE returns position.lat, position.lng.
     */
    public function geocode(string $address): ?array
    {
        if ($this->apiKey === '') {
            return null;
        }

        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $response = Http::timeout($this->timeout)
            ->get($this->geocodeUrl, [
                'apiKey' => $this->apiKey,
                'q' => $address,
                'limit' => 1,
            ]);

        if (!$response->successful()) {
            Log::warning('HERE geocode failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'address' => $address,
            ]);
            return null;
        }

        $data = $response->json();
        $items = $data['items'] ?? [];
        $first = $items[0] ?? null;
        if (!$first) {
            return null;
        }

        $pos = $first['position'] ?? null;
        if (!$pos || !isset($pos['lat'], $pos['lng'])) {
            return null;
        }

        return [(float) $pos['lng'], (float) $pos['lat']];
    }

    /**
     * Get route summary between coordinates.
     * Coordinates: array of [lon, lat].
     * HERE expects origin=lat,lng & destination=lat,lng & via=lat,lng (repeated).
     */
    public function getRouteSummary(array $coordinates): ?array
    {
        if ($this->apiKey === '' || count($coordinates) < 2) {
            return null;
        }

        $coords = array_values($coordinates);
        $origin = $coords[0][1] . ',' . $coords[0][0];
        $destination = $coords[count($coords) - 1][1] . ',' . $coords[count($coords) - 1][0];

        $query = http_build_query([
            'origin' => $origin,
            'destination' => $destination,
            'transportMode' => $this->transportMode,
            'apikey' => $this->apiKey,
            'return' => 'summary',
        ]);

        if (count($coords) > 2) {
            for ($i = 1; $i < count($coords) - 1; $i++) {
                $via = $coords[$i][1] . ',' . $coords[$i][0];
                $query .= '&via=' . rawurlencode($via);
            }
        }

        $url = $this->routingUrl . (str_contains($this->routingUrl, '?') ? '&' : '?') . $query;
        $response = Http::timeout($this->timeout)->get($url);

        if (!$response->successful()) {
            Log::warning('HERE routing failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'waypoints' => count($coords),
            ]);
            return null;
        }

        $data = $response->json();
        $routes = $data['routes'] ?? [];
        $route = $routes[0] ?? null;
        if (!$route) {
            return null;
        }

        $totalMeters = 0;
        $totalSeconds = 0.0;
        foreach ($route['sections'] ?? [] as $section) {
            $summary = $section['summary'] ?? [];
            $totalMeters += (float) ($summary['length'] ?? 0);
            $totalSeconds += (float) ($summary['duration'] ?? 0);
        }

        return [
            'distance_km' => round($totalMeters / 1000, 2),
            'duration_minutes' => round($totalSeconds / 60, 1),
        ];
    }

    /**
     * Build coordinates array from steps (geocode each). Returns ['coordinates' => [...], 'steps' => [...]] or error.
     */
    protected function getCoordinatesAndStepsFromSteps(Collection $steps): array
    {
        if ($steps->isEmpty() || $steps->count() < 2) {
            return ['error' => true, 'failed_step_order' => 0, 'failed_address' => ''];
        }

        $coordinates = [];
        $stepsArray = [];
        $order = 0;
        $sortedSteps = $steps->sortBy(fn ($s) => (int) ($s->order ?? 0));
        foreach ($sortedSteps->values() as $step) {
            $order++;
            $stepsArray[] = $step;

            $addressString = method_exists($step, 'addressLine')
                ? $step->addressLine()
                : self::stepToAddressString($step);

            if ($addressString === '') {
                Log::info('HERE: empty address for step', ['order' => $order]);
                return ['error' => true, 'failed_step_order' => $order, 'failed_address' => ''];
            }

            $coord = $this->geocode($addressString);
            if ($coord === null) {
                $fallback = self::stepToFallbackAddressString($step);
                if ($fallback !== '') {
                    $coord = $this->geocode($fallback);
                    if ($coord !== null) {
                        Log::info('HERE: used fallback (city/country) for step', [
                            'original' => $addressString,
                            'fallback' => $fallback,
                        ]);
                    }
                }
            }
            if ($coord === null) {
                Log::info('HERE: geocode failed for step', ['order' => $order, 'address' => $addressString]);
                return ['error' => true, 'failed_step_order' => $order, 'failed_address' => $addressString];
            }
            $coordinates[] = $coord;
        }

        return ['coordinates' => $coordinates, 'steps' => $stepsArray];
    }

    /**
     * Same contract as OpenRouteService::getRouteSummaryFromSteps.
     */
    public function getRouteSummaryFromSteps(Collection $steps): array
    {
        $result = $this->getCoordinatesAndStepsFromSteps($steps);
        if (!empty($result['error'])) {
            return $result;
        }

        $summary = $this->getRouteSummary($result['coordinates']);
        if ($summary !== null && empty($summary['error'])) {
            return $summary;
        }

        Log::warning('HERE: directions returned no route', ['coordinates_count' => count($result['coordinates'])]);
        return [
            'error' => true,
            'failed_step_order' => 0,
            'failed_address' => '',
            'directions_failed' => true,
        ];
    }

    /**
     * Suggest optimal order (nearest-neighbor TSP) and return summary for that order.
     * Returns: ['distance_km', 'duration_minutes', 'suggested_order_indices', 'suggested_steps'] or error.
     */
    public function getOptimalRouteFromSteps(Collection $steps): array
    {
        $result = $this->getCoordinatesAndStepsFromSteps($steps);
        if (!empty($result['error'])) {
            return $result;
        }

        $coordinates = $result['coordinates'];
        $stepsArray = $result['steps'];
        $indices = RouteOptimizer::suggestOrder($coordinates, true);
        $reorderedCoords = RouteOptimizer::reorderByIndices($coordinates, $indices);
        $reorderedSteps = RouteOptimizer::reorderByIndices($stepsArray, $indices);

        $summary = $this->getRouteSummary($reorderedCoords);
        if ($summary === null || !empty($summary['error'])) {
            return $summary ?? [
                'error' => true,
                'failed_step_order' => 0,
                'failed_address' => '',
                'directions_failed' => true,
            ];
        }

        return array_merge($summary, [
            'suggested_order_indices' => $indices,
            'suggested_steps' => $reorderedSteps,
        ]);
    }
}
