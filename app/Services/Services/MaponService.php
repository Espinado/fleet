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

    protected function apiKeyForCompany(?int $companyId): string
    {
        if ($companyId !== null) {
            $key = (string) (config("mapon.keys.$companyId") ?? '');
            if ($key !== '') {
                return $key;
            }
        }

        return (string) config('mapon.default_key', '');
    }

    /**
     * ✅ Основной метод: получаем данные Mapon по грузовику
     * (ключ выбирается по $truck->company)
     */
    public function getUnitDataForTruck(Truck $truck, array|string|null $include = null): ?array
    {
        $unitId = $truck->mapon_unit_id;
        if (!$unitId) return null;

        $apiKey = $this->apiKeyForCompany($truck->company);
        if ($apiKey === '') return null;

        $url = rtrim($this->baseUrl, '/') . '/unit/list.json';

        $query = [
            'key'     => $apiKey,
            'unit_id' => $unitId,
        ];

        // Mapon принимает include=can (строкой)
        if (is_string($include) && $include !== '') {
            $query['include'] = $include;
        }

        // если когда-то захочешь include[]=can&include[]=...
        if (is_array($include) && !empty($include)) {
            $query['include'] = $include; // Laravel превратит в include[0]=...
        }

        $response = Http::timeout(15)->get($url, $query);
        if (!$response->ok()) return null;

        $json = $response->json();
        return $json['data']['units'][0] ?? null;
    }

    public function getMileageRawForTruck(Truck $truck): ?float
    {
        $data = $this->getUnitDataForTruck($truck);
        if (!$data) return null;

        return isset($data['mileage']) ? (float) $data['mileage'] : null;
    }

    /**
     * ✅ Оставил обратную совместимость:
     * если где-то в коде уже вызывается getUnitData($unitId, 'can'),
     * то можно продолжать, но нужно передать companyId.
     */
    public function getUnitData(int|string $unitId, int $companyId, array|string|null $include = null): ?array
    {
        $apiKey = $this->apiKeyForCompany($companyId);
        if ($apiKey === '') return null;

        $url = rtrim($this->baseUrl, '/') . '/unit/list.json';

        $query = [
            'key'     => $apiKey,
            'unit_id' => $unitId,
        ];

        if (is_string($include) && $include !== '') {
            $query['include'] = $include;
        }

        if (is_array($include) && !empty($include)) {
            $query['include'] = $include;
        }

        $response = Http::timeout(15)->get($url, $query);
        if (!$response->ok()) return null;

        $json = $response->json();
        return $json['data']['units'][0] ?? null;
    }

    public function getMileageRaw(int|string $unitId, int $companyId): ?float
    {
        $data = $this->getUnitData($unitId, $companyId);
        if (!$data) return null;

        return isset($data['mileage']) ? (float) $data['mileage'] : null;
    }
}
