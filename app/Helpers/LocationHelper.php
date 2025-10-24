<?php

/**
 * === LOCATION HELPER ===
 * Универсальные функции для работы со странами и городами.
 *
 * Работает с конфигурацией:
 *  - config/countries.php
 *  - config/cities/{iso}.php  (например, cities/lv.php)
 *
 * Пример использования:
 *  getCountryByIso('LV')
 *  getCitiesByIso('LV')
 *  getCitiesByCountryId(1)
 *  getCityName('LV', 3)
 */

if (!function_exists('getCountryByIso')) {
    /**
     * Возвращает данные о стране по ISO-коду.
     *
     * @param string $iso
     * @return array|null
     */
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

if (!function_exists('getCountryById')) {
    /**
     * Возвращает данные о стране по ID.
     *
     * @param int $countryId
     * @return array|null
     */
    function getCountryById(int $countryId): ?array
    {
        $countries = config('countries');
        return $countries[$countryId] ?? null;
    }
}

if (!function_exists('getCitiesByIso')) {
    /**
     * Возвращает массив городов по ISO-коду страны.
     * Пример: getCitiesByIso('LV')
     *
     * Файл должен находиться в: config/cities/lv.php
     *
     * @param string $iso
     * @return array
     */
    function getCitiesByIso(string $iso): array
    {
        $iso = strtolower($iso);
        $path = config_path("cities/{$iso}.php");

        if (file_exists($path)) {
            return require $path;
        }

        return [];
    }
}

if (!function_exists('getCitiesByCountryId')) {
    /**
     * Возвращает города по ID страны (через ISO).
     * Пример: getCitiesByCountryId(1)
     *
     * @param int $countryId
     * @return array
     */
    function getCitiesByCountryId(int $countryId): array
    {
        $country = getCountryById($countryId);

        if (!$country || empty($country['iso'])) {
            return [];
        }

        return getCitiesByIso($country['iso']);
    }
}

if (!function_exists('getCityName')) {
    /**
     * Возвращает название города по ISO и ID.
     * Пример: getCityName('LV', 1) → "Riga"
     *
     * @param string $iso
     * @param int|string $cityId
     * @return string|null
     */
    function getCityName(string $iso, int|string $cityId): ?string
    {
        $cities = getCitiesByIso($iso);
        return $cities[$cityId]['name'] ?? null;
    }
}

if (!function_exists('getCityNameByCountryId')) {
    /**
     * Возвращает название города по ID страны и ID города.
     * Пример: getCityNameByCountryId(1, 3)
     *
     * @param int $countryId
     * @param int|string $cityId
     * @return string|null
     */
    function getCityNameByCountryId(int $countryId, int|string $cityId): ?string
    {
        $country = getCountryById($countryId);
        if (!$country || empty($country['iso'])) {
            return null;
        }

        $cities = getCitiesByIso($country['iso']);
        return $cities[$cityId]['name'] ?? null;
    }
}
