<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouteService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected string $profile;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('openrouteservice.base_url', 'https://api.openrouteservice.org'), '/');
        $this->apiKey = config('openrouteservice.api_key', '');
        $this->timeout = config('openrouteservice.timeout', 15);
        $this->profile = config('openrouteservice.profile', 'driving-car');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Build a single address string from a step-like object (country_id, city_id, address).
     */
    public static function stepToAddressString(object $step): string
    {
        $country = $step->country_id ? (getCountryById((int) $step->country_id) ?? '') : '';
        $city = $step->city_id ? (getCityById((int) $step->city_id, $step->country_id ? (int) $step->country_id : null) ?? '') : '';
        $address = trim($step->address ?? '');

        return collect([$address, $city, $country])->filter()->implode(', ');
    }

    /**
     * Geocode one address string to [lon, lat] or null.
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

        $url = $this->baseUrl . '/geocode/search';
        $response = Http::timeout($this->timeout)
            ->get($url, [
                'api_key' => $this->apiKey,
                'text' => $address,
                'size' => 1,
            ]);

        if (!$response->successful()) {
            Log::warning('OpenRouteService geocode failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'address' => $address,
            ]);
            return null;
        }

        $data = $response->json();
        $features = $data['features'] ?? [];
        $first = $features[0] ?? null;
        if (!$first) {
            return null;
        }

        $coords = $first['geometry']['coordinates'] ?? null;
        if (!is_array($coords) || count($coords) < 2) {
            return null;
        }

        return [(float) $coords[0], (float) $coords[1]];
    }

    /** ORS Directions API limit: max 50 waypoints. */
    private const MAX_WAYPOINTS = 50;

    /**
     * Get route summary (distance in km, duration in minutes) between coordinates.
     * Coordinates: array of [lon, lat] pairs.
     * If waypoints > 50, uses first + last + evenly distributed intermediate points.
     */
    public function getRouteSummary(array $coordinates): ?array
    {
        if ($this->apiKey === '' || count($coordinates) < 2) {
            return null;
        }

        $coords = array_values($coordinates);
        if (count($coords) > self::MAX_WAYPOINTS) {
            $coords = $this->reduceWaypointsToLimit($coords, self::MAX_WAYPOINTS);
            Log::info('OpenRouteService: reduced waypoints to ' . count($coords) . ' (limit ' . self::MAX_WAYPOINTS . ')');
        }

        $result = $this->requestDirections($coords, $this->profile);
        if ($result !== null) {
            if (is_array($result) && !empty($result['error'])) {
                return $result;
            }
            return $result;
        }

        if ($this->profile === 'driving-hgv') {
            Log::info('OpenRouteService: driving-hgv failed, trying driving-car');
            $result = $this->requestDirections($coords, 'driving-car');
            if ($result !== null) {
                if (is_array($result) && !empty($result['error'])) {
                    return $result;
                }
                return $result;
            }
        }

        return null;
    }

    /**
     * Reduce waypoints to limit: keep first, last, and distribute the rest evenly.
     */
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

    private function requestDirections(array $coords, string $profile): ?array
    {
        $url = $this->baseUrl . '/v2/directions/' . $profile;
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'coordinates' => $coords,
            ]);

        if (!$response->successful()) {
            $body = $response->body();
            Log::warning('OpenRouteService directions failed', [
                'profile' => $profile,
                'status' => $response->status(),
                'body' => $body,
                'waypoints' => count($coords),
            ]);
            $data = json_decode($body, true);
            $msg = $data['error']['message'] ?? '';
            $code = (int) ($data['error']['code'] ?? 0);
            if ($response->status() === 400 && ($code === 2004 || str_contains($msg, '6000000') || str_contains($msg, '6000'))) {
                return ['error' => true, 'error_key' => 'distance_limit'];
            }
            if (($response->status() === 404 || $code === 2010) && str_contains($msg, 'routable point')) {
                return ['error' => true, 'error_key' => 'point_not_routable'];
            }
            return null;
        }

        $data = $response->json();
        $routes = $data['routes'] ?? [];
        $route = $routes[0] ?? null;
        if (!$route) {
            Log::warning('OpenRouteService directions: empty routes', [
                'profile' => $profile,
                'body' => $response->body(),
                'waypoints' => count($coords),
            ]);
            return null;
        }

        $summary = $route['summary'] ?? [];
        $distanceMeters = $summary['distance'] ?? 0;
        $durationSeconds = $summary['duration'] ?? 0;

        return [
            'distance_km' => round((float) $distanceMeters / 1000, 2),
            'duration_minutes' => round((float) $durationSeconds / 60, 1),
        ];
    }

    /**
     * Build fallback address: "City, Country" or just "Country" (any point in city/country).
     * Used when full address fails to geocode.
     */
    public static function stepToFallbackAddressString(object $step): string
    {
        $countryId = isset($step->country_id) ? (int) $step->country_id : null;
        $cityId = isset($step->city_id) ? (int) $step->city_id : null;

        $country = $countryId ? (\getCountryById($countryId) ?? '') : '';
        $city = ($cityId && $countryId) ? \getCityById($cityId, $countryId) : '';

        $parts = array_filter([
            $city !== '' && $city !== '—' ? $city : null,
            $country,
        ]);

        return implode(', ', $parts);
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
                Log::info('OpenRouteService: empty address for step', ['order' => $order]);
                return ['error' => true, 'failed_step_order' => $order, 'failed_address' => ''];
            }

            $coord = $this->geocode($addressString);
            if ($coord === null) {
                $fallback = self::stepToFallbackAddressString($step);
                if ($fallback !== '') {
                    $coord = $this->geocode($fallback);
                    if ($coord !== null) {
                        Log::info('OpenRouteService: used fallback (city/country) for step', [
                            'original' => $addressString,
                            'fallback' => $fallback,
                        ]);
                    }
                }
            }
            if ($coord === null) {
                Log::info('OpenRouteService: geocode failed for step (full and fallback)', [
                    'order' => $order,
                    'address' => $addressString,
                ]);
                return ['error' => true, 'failed_step_order' => $order, 'failed_address' => $addressString];
            }
            $coordinates[] = $coord;
        }

        return ['coordinates' => $coordinates, 'steps' => $stepsArray];
    }

    /**
     * Get route summary from a collection of steps (OrderStep or TripStep).
     * Returns: success => ['distance_km' => float, 'duration_minutes' => float]
     *          or error => ['error' => true, 'failed_step_order' => int, 'failed_address' => string].
     */
    public function getRouteSummaryFromSteps(Collection $steps): array
    {
        $result = $this->getCoordinatesAndStepsFromSteps($steps);
        if (!empty($result['error'])) {
            return $result;
        }

        $coordinates = $result['coordinates'];
        $summary = $this->getRouteSummary($coordinates);
        if ($summary !== null && empty($summary['error'])) {
            return $summary;
        }
        if (is_array($summary) && !empty($summary['error']) && !empty($summary['error_key'])) {
            return $summary;
        }
        Log::warning('OpenRouteService: directions API returned no route', ['coordinates_count' => count($coordinates)]);
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
