<?php

namespace App\Services;

/**
 * Эвристика TSP: предлагает порядок точек для минимизации маршрута.
 * First и last точка фиксированы (старт/финиш), средние перебираются по "nearest neighbour".
 * Расстояния — по прямой (приближение), итоговый км считает API маршрутизации.
 */
class RouteOptimizer
{
    /**
     * Расстояние между двумя точками [lon, lat] в км (приближённо, haversine).
     */
    public static function distanceKm(array $a, array $b): float
    {
        $lat1 = (float) $a[1];
        $lon1 = (float) $a[0];
        $lat2 = (float) $b[1];
        $lon2 = (float) $b[0];

        $r = 6371; // Earth radius km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $x = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $r * 2 * asin(sqrt($x));
    }

    /**
     * Предлагает порядок обхода точек (индексы 0..n-1).
     * fixFirstAndLast: true = первый и последний индекс не меняются (старт/финиш).
     */
    public static function suggestOrder(array $coordinates, bool $fixFirstAndLast = true): array
    {
        $n = count($coordinates);
        if ($n <= 2) {
            return range(0, $n - 1);
        }

        if (!$fixFirstAndLast) {
            return self::nearestNeighborFull($coordinates);
        }

        $first = 0;
        $last = $n - 1;
        $middle = range(1, $n - 2);

        if (empty($middle)) {
            return [0, $n - 1];
        }

        $order = [$first];
        $current = $first;
        $remaining = $middle;

        while (!empty($remaining)) {
            $best = null;
            $bestDist = PHP_FLOAT_MAX;
            foreach ($remaining as $i) {
                $d = self::distanceKm($coordinates[$current], $coordinates[$i]);
                if ($d < $bestDist) {
                    $bestDist = $d;
                    $best = $i;
                }
            }
            $order[] = $best;
            $current = $best;
            $remaining = array_values(array_filter($remaining, fn ($x) => $x !== $best));
        }

        $order[] = $last;
        return $order;
    }

    /**
     * Nearest neighbour без фиксированных концов (все точки переставляются).
     */
    private static function nearestNeighborFull(array $coordinates): array
    {
        $n = count($coordinates);
        $order = [0];
        $remaining = range(1, $n - 1);

        $current = 0;
        while (!empty($remaining)) {
            $best = null;
            $bestDist = PHP_FLOAT_MAX;
            foreach ($remaining as $i) {
                $d = self::distanceKm($coordinates[$current], $coordinates[$i]);
                if ($d < $bestDist) {
                    $bestDist = $d;
                    $best = $i;
                }
            }
            $order[] = $best;
            $current = $best;
            $remaining = array_values(array_filter($remaining, fn ($x) => $x !== $best));
        }
        return $order;
    }

    /**
     * Переставить массив по заданному порядку индексов.
     */
    public static function reorderByIndices(array $items, array $indices): array
    {
        $out = [];
        foreach ($indices as $i) {
            $out[] = $items[$i];
        }
        return $out;
    }
}
