<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Maps Geocoding + Directions (https://developers.google.com/maps).
 * Same public interface as OpenRouteService / HereRouteService for drop-in replacement.
 * Requires: Geocoding API and Directions API enabled in Google Cloud, API key with both.
 */
class GoogleMapsRouteService
{
    protected string $apiKey;
    protected int $timeout;

    /** Google Directions allows max 25 waypoints (origin + 23 via + destination). */
    private const MAX_WAYPOINTS = 25;

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key', '');
        $this->timeout = (int) config('services.google.maps_timeout', 15);
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
     * Google returns geometry.location.lat, .lng.
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

        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        $response = Http::timeout($this->timeout)
            ->get($url, [
                'address' => $address,
                'key' => $this->apiKey,
            ]);

        if (!$response->successful()) {
            Log::warning('Google Maps geocode failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'address' => $address,
            ]);
            return null;
        }

        $data = $response->json();
        if (($data['status'] ?? '') !== 'OK') {
            return null;
        }

        $results = $data['results'] ?? [];
        $first = $results[0] ?? null;
        if (!$first) {
            return null;
        }

        $location = $first['geometry']['location'] ?? null;
        if (!$location || !isset($location['lat'], $location['lng'])) {
            return null;
        }

        return [(float) $location['lng'], (float) $location['lat']];
    }

    /**
     * Get route summary (distance in km, duration in minutes).
     * Coordinates: array of [lon, lat]. Google: origin=lat,lng, destination=lat,lng, waypoints=lat,lng|...
     */
    public function getRouteSummary(array $coordinates): ?array
    {
        if ($this->apiKey === '' || count($coordinates) < 2) {
            return null;
        }

        $coords = array_values($coordinates);
        if (count($coords) > self::MAX_WAYPOINTS) {
            $coords = $this->reduceWaypointsToLimit($coords, self::MAX_WAYPOINTS);
            Log::info('GoogleMapsRouteService: reduced waypoints to ' . count($coords) . ' (limit ' . self::MAX_WAYPOINTS . ')');
        }

        $origin = $coords[0][1] . ',' . $coords[0][0];
        $destination = $coords[count($coords) - 1][1] . ',' . $coords[count($coords) - 1][0];

        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'key' => $this->apiKey,
        ];

        if (count($coords) > 2) {
            $waypoints = [];
            for ($i = 1; $i < count($coords) - 1; $i++) {
                $waypoints[] = $coords[$i][1] . ',' . $coords[$i][0];
            }
            $params['waypoints'] = implode('|', $waypoints);
        }

        $url = 'https://maps.googleapis.com/maps/api/directions/json?' . http_build_query($params);
        $response = Http::timeout($this->timeout)->get($url);

        if (!$response->successful()) {
            Log::warning('Google Maps directions failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        if (($data['status'] ?? '') !== 'OK') {
            Log::warning('Google Maps directions status', ['status' => $data['status'] ?? '', 'body' => $data]);
            return null;
        }

        $routes = $data['routes'] ?? [];
        $route = $routes[0] ?? null;
        if (!$route) {
            return null;
        }

        $totalMeters = 0;
        $totalSeconds = 0.0;
        foreach ($route['legs'] ?? [] as $leg) {
            $totalMeters += (float) ($leg['distance']['value'] ?? 0);
            $totalSeconds += (float) ($leg['duration']['value'] ?? 0);
        }

        return [
            'distance_km' => round($totalMeters / 1000, 2),
            'duration_minutes' => round($totalSeconds / 60, 1),
        ];
    }

    private function reduceWaypointsToLimit(array $coords, int $limit): array
    {
        $n = count($coords);
        if ($n <= $limit) {
            return $coords;
        }
        $out = [$coords[0]];
        $step = ($n - 2) / ($limit - 2);
        for ($i = 1; $i < $limit - 1; $i++) {
            $idx = (int) round(1 + ($i - 1) * $step);
            $out[] = $coords[$idx];
        }
        $out[] = $coords[$n - 1];
        return $out;
    }

    /**
     * Build coordinates from steps (geocode each). Same contract as OpenRouteService.
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
                Log::info('GoogleMaps: empty address for step', ['order' => $order]);
                return ['error' => true, 'failed_step_order' => $order, 'failed_address' => ''];
            }

            $coord = $this->geocode($addressString);
            if ($coord === null) {
                $fallback = self::stepToFallbackAddressString($step);
                if ($fallback !== '') {
                    $coord = $this->geocode($fallback);
                    if ($coord !== null) {
                        Log::info('GoogleMaps: used fallback (city/country) for step', [
                            'original' => $addressString,
                            'fallback' => $fallback,
                        ]);
                    }
                }
            }
            if ($coord === null) {
                Log::info('GoogleMaps: geocode failed for step', ['order' => $order, 'address' => $addressString]);
                return ['error' => true, 'failed_step_order' => $order, 'failed_address' => $addressString];
            }
            $coordinates[] = $coord;
        }

        return ['coordinates' => $coordinates, 'steps' => $stepsArray];
    }

    /**
     * Get route summary from steps. Same contract as OpenRouteService::getRouteSummaryFromSteps.
     */
    public function getRouteSummaryFromSteps(Collection $steps): array
    {
        $result = $this->getCoordinatesAndStepsFromSteps($steps);
        if (!empty($result['error'])) {
            return $result;
        }

        $coordinates = $result['coordinates'];
        $summary = $this->getRouteSummary($coordinates);
        if ($summary !== null) {
            return $summary;
        }

        Log::warning('GoogleMapsRouteService: directions returned no route', ['coordinates_count' => count($coordinates)]);
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
        if ($summary === null) {
            return [
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
