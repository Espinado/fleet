<?php

use Illuminate\Support\Facades\File;

/**
 * ============================================================================
 * 🌍 LOCATION HELPER (v2.0)
 * Универсальные функции для работы со странами и городами.
 * Работает с конфигами:
 *   - config/countries.php
 *   - config/cities/{iso}.php  (например: config/cities/lv.php)
 * ============================================================================
 *
 * Примеры:
 *  getCountryById(1) → "Latvia"
 *  getCitiesByCountryId(1) → [1 => ['name' => 'Riga'], ...]
 *  getCityNameByCountryId(1, 3) → "Riga"
 *  getCityById(3, 1) → "Riga"   // теперь учитывает страну!
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
    function getCountryByIso(string $iso): ?array
    {
        $countries = config('countries');

        foreach ($countries as $country) {
            if (strtoupper($country['iso']) === strtoupper($iso)) {
                return $country;
            }
        }

        return null;
    }
}


/**
 * 🏙 Получить список городов по ISO
 */
if (!function_exists('getCitiesByIso')) {
    function getCitiesByIso(string $iso): array
    {
        $iso = strtolower(trim($iso));
        $path = config_path("cities/{$iso}.php");

        if (file_exists($path)) {
            $cities = include $path;
            return is_array($cities) ? $cities : [];
        }

        return [];
    }
}
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

if (!function_exists('getCountryIsoById')) {
    function getCountryIsoById(?int $id): ?string
    {
        if (!$id) return null;

        $country = config("countries.$id");
        if (is_array($country)) {
            return strtoupper($country['iso'] ?? '—');
        }

        return '—';
    }
}



/**
 * 🗺 Получить города по ID страны
 */
if (!function_exists('getCitiesByCountryId')) {
    function getCitiesByCountryId(int $countryId): array
    {
        $country = config("countries.$countryId");

        if (!$country || empty($country['iso'])) {
            return [];
        }

        return getCitiesByIso($country['iso']);
    }
}


/**
 * 🏡 Получить название города по ID страны и ID города
 */
if (!function_exists('getCityNameByCountryId')) {
    function getCityNameByCountryId(int $countryId, int|string $cityId): ?string
    {
        $country = config("countries.$countryId");

        if (!$country || empty($country['iso'])) {
            return null;
        }

        $cities = getCitiesByIso($country['iso']);
        return $cities[$cityId]['name'] ?? '—';
    }
}


/**
 * 🔍 Получить название города по ID (универсально)
 *  Если известна страна — ищет строго в её списке.
 *  Если страна не указана — ищет по всем config/cities/*.php.
 */
if (!function_exists('getCityById')) {
    function getCityById(?int $cityId, ?int $countryId = null): string
    {
        if (!$cityId) return '—';

        // Если известна страна — ищем строго в её списке
        if ($countryId) {
            $country = config("countries.$countryId");
            if (!$country || empty($country['iso'])) return '—';

            $cities = getCitiesByIso($country['iso']);
            return $cities[$cityId]['name'] ?? '—';
        }

        // Иначе ищем во всех странах (старое поведение)
        foreach (File::glob(config_path('cities/*.php')) as $path) {
            $cities = include $path;
            if (!is_array($cities)) continue;

            if (isset($cities[$cityId]['name'])) {
                return (string) $cities[$cityId]['name'];
            }
        }

        return '—';
    }
}
