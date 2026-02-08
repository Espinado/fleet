<?php

namespace App\Services\Services;

use App\Models\Truck;
use Illuminate\Support\Facades\Http;

class MaponService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('mapon.base_url', 'https://mapon.com/api/v1');
    }

   protected function apiKeyForCompany(int|string|null $companyId): string
{
    $companyId = (int) ($companyId ?? 0);
    $companyId = $companyId > 0 ? $companyId : null;

    if ($companyId !== null) {
        $key = (string) (config("mapon.keys.$companyId") ?? '');
        if ($key !== '') return $key;
    }

    // fallback ключ (старый вариант)
    return (string) config('mapon.key', '');
}


    /**
     * Основной метод: получить unit данные Mapon по Truck
     * (ключ выбирается по $truck->company)
     */
    public function getUnitDataForTruck(Truck $truck, array|string|null $include = null): ?array
    {
        $unitId = $truck->mapon_unit_id;
        if (!$unitId) return null;

        $apiKey = $this->apiKeyForCompany($truck->company);
        if ($apiKey === '') return null;

        return $this->fetchUnit((int) $unitId, $apiKey, $include);
    }

    /**
     * Обратная совместимость: получить unit данные по unitId + companyId
     */
   public function getUnitData(int|string $unitId, int|string|null $companyId, array|string|null $include = null): ?array
{
    $apiKey = $this->apiKeyForCompany($companyId);
    if ($apiKey === '') return null;

    return $this->fetchUnit((int) $unitId, $apiKey, $include);
}


    /**
     * Пробег (сырой) по Truck (как отдаёт Mapon)
     */
    public function getMileageRawForTruck(Truck $truck): ?float
    {
        $data = $this->getUnitDataForTruck($truck);
        if (!$data) return null;

        return isset($data['mileage']) ? (float) $data['mileage'] : null;
    }

    /**
     * Пробег (сырой) по unitId + companyId
     */
   public function getMileageRaw(int|string $unitId, int|string|null $companyId): ?float
{
    $data = $this->getUnitData($unitId, $companyId);
    if (!$data) return null;

    return isset($data['mileage']) ? (float) $data['mileage'] : null;
}


    /**
     * Низкоуровневый запрос к Mapon.
     * Возвращает именно тот unit, который запрошен.
     */
    protected function fetchUnit(int $unitId, string $apiKey, array|string|null $include = null): ?array
    {
        $url = rtrim($this->baseUrl, '/') . '/unit/list.json';

        $query = [
            'key'     => $apiKey,
            'unit_id' => $unitId,
        ];

        if (is_string($include) && $include !== '') {
            $query['include'] = $include;        // include=can
        } elseif (is_array($include) && !empty($include)) {
            $query['include'] = $include;        // include[0]=can
        }

        $response = Http::timeout(15)->get($url, $query);
        if (!$response->ok()) return null;

        $json  = $response->json();
        $units = data_get($json, 'data.units', []);

        if (!is_array($units) || empty($units)) return null;

        $unit = collect($units)->firstWhere('unit_id', $unitId) ?? $units[0];

        return is_array($unit) ? $unit : null;
    }
}
