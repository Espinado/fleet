<?php

use Illuminate\Support\Facades\File;

/**
 * ============================================================================
 * 🌍 LOCATION HELPER (v3.0 — SAFE VERSION)
 * Все функции теперь безопасно работают с NULL, строками, отсутствующими ключами.
 * ============================================================================
 */


/**
 * 🏳️ Получить название страны по ID
 */
if (!function_exists('getCountryById')) {
    function getCountryById(?int $id): ?string
    {
        if (!$id) return null;

        $country = config("countries.$id");

        if (is_array($country)) {
            return $country['name'] ?? '—';
        }

        return is_string($country) ? $country : '—';
    }
}


/**
 * 🏴‍☠️ Получить данные страны по ISO
 */
if (!function_exists('getCountryByIso')) {
    function getCountryByIso(?string $iso): ?array
    {
        if (!$iso) return null;

        $iso = strtoupper(trim($iso));
        $countries = config('countries');

        foreach ($countries as $country) {
            if (strtoupper($country['iso'] ?? '') === $iso) {
                return $country;
            }
        }

        return null;
    }
}


/**
 * 🏙 Получить список городов по ISO файла config/cities/{iso}.php
 */
if (!function_exists('getCitiesByIso')) {
    function getCitiesByIso(?string $iso): array
    {
        if (!$iso) return [];

        $iso = strtolower(trim($iso));
        $path = config_path("cities/{$iso}.php");

        if (!file_exists($path)) {
            return [];
        }

        $cities = include $path;
        return is_array($cities) ? $cities : [];
    }
}


/**
 * 🏢 Получить компанию по ID
 */
if (!function_exists('getCompanyById')) {
    function getCompanyById(?int $id): ?string
    {
        if (!$id) return null;

        $companies = config('companies');
        $company = $companies[$id] ?? null;

        if (!$company) return '—';

        $name = $company['name'] ?? '—';
        $city = $company['city'] ?? '';
        $country = $company['country'] ?? '';

        return trim("$name ($city, $country)");
    }
}


/**
 * 🇺🇳 Получить ISO страны по ID
 */
if (!function_exists('getCountryIsoById')) {
    function getCountryIsoById(?int $id): ?string
    {
        if (!$id) return null;

        $country = config("countries.$id");

        if (is_array($country) && !empty($country['iso'])) {
            return strtoupper($country['iso']);
        }

        return null;
    }
}


/**
 * 🗺 Получить города по ID страны
 * SAFE VERSION — НЕ падает при null
 */
if (!function_exists('getCitiesByCountryId')) {
    function getCitiesByCountryId(?int $countryId): array
    {
        if ($countryId === null) {
            return [];
        }

        $country = config("countries.$countryId");
        if (!$country || empty($country['iso'])) {
            return [];
        }

        return getCitiesByIso($country['iso']);
    }
}


/**
 * 🏡 Получить название города по ID страны и ID города
 * SAFE VERSION — НЕ падает при null
 */
if (!function_exists('getCityNameByCountryId')) {
    function getCityNameByCountryId(?int $countryId, int|string|null $cityId): ?string
    {
        if (!$countryId || !$cityId) return null;

        $country = config("countries.$countryId");
        if (!$country || empty($country['iso'])) return null;

        $cities = getCitiesByIso($country['iso']);
        return $cities[$cityId]['name'] ?? '—';
    }
}


/**
 * 🔍 Получить название города по ID (универсально)
 * SAFE VERSION — НЕ падает при null
 */
if (!function_exists('getCityById')) {
    function getCityById(?int $cityId, ?int $countryId = null): string
    {
        if (!$cityId) return '—';

        // Если есть страна — ищем в ней
        if ($countryId) {
            $country = config("countries.$countryId");
            if (!$country || empty($country['iso'])) return '—';

            $cities = getCitiesByIso($country['iso']);
            return $cities[$cityId]['name'] ?? '—';
        }

        // Иначе ищем во всех городах
        foreach (File::glob(config_path('cities/*.php')) as $path) {
            $cities = include $path;
            if (!is_array($cities)) continue;

            if (!empty($cities[$cityId]['name'])) {
                return (string) $cities[$cityId]['name'];
            }
        }

        return '—';
    }
}
