<?php

namespace App\Services\Services;

use App\Models\Truck;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        // у тебя в config/mapon.php это 'key' (fallback)
        return (string) config('mapon.key', '');
    }

    protected function maponLogger()
    {
        return Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/mapon.log'),
        ]);
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
        } elseif (is_array($include) && !empty($include)) {
            $query['include'] = $include; // include[0]=...
        }

        $this->maponLogger()->info('Mapon request', [
            'env'        => app()->environment(),
            'company_id' => (string) ($truck->company ?? ''),
            'truck_id'   => (string) ($truck->id ?? ''),
            'unit_id'    => (string) $unitId,
            'include'    => $include,
            'query'      => $query,
            'url'        => $url,
        ]);

        $response = Http::timeout(15)->get($url, $query);

        if (!$response->ok()) {
            $this->maponLogger()->warning('Mapon response not ok', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        }

        $json  = $response->json();
        $units = data_get($json, 'data.units', []);

        if (!is_array($units) || empty($units)) {
            $this->maponLogger()->warning('Mapon response has no units', [
                'unit_id' => (string) $unitId,
                'json'    => $json,
            ]);

            return null;
        }

        // ✅ выбираем именно тот unit_id, который просили
        $unit = collect($units)->firstWhere('unit_id', (int) $unitId) ?? $units[0];

        if (!is_array($unit)) {
            $this->maponLogger()->warning('Mapon unit is not array', [
                'unit_id' => (string) $unitId,
                'unit'    => $unit,
            ]);

            return null;
        }

        // ✅ снимок ключевых полей (поймем сразу есть ли CAN)
        $this->maponLogger()->info('Mapon response snapshot', [
            'unit_id'     => (string) $unitId,
            'has_can'     => array_key_exists('can', $unit),
            'can_odom'    => data_get($unit, 'can.odom.value'),
            'mileage'     => data_get($unit, 'mileage'),
            'last_update' => data_get($unit, 'last_update'),
            'keys'        => array_keys($unit),
        ]);

        return $unit;
    }

    public function getMileageRawForTruck(Truck $truck): ?float
    {
        $data = $this->getUnitDataForTruck($truck);
        if (!$data) return null;

        return isset($data['mileage']) ? (float) $data['mileage'] : null;
    }

    /**
     * ✅ Обратная совместимость: нужно передавать companyId
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
        } elseif (is_array($include) && !empty($include)) {
            $query['include'] = $include;
        }

        $this->maponLogger()->info('Mapon request (legacy)', [
            'env'        => app()->environment(),
            'company_id' => (string) $companyId,
            'unit_id'    => (string) $unitId,
            'include'    => $include,
            'query'      => $query,
            'url'        => $url,
        ]);

        $response = Http::timeout(15)->get($url, $query);

        if (!$response->ok()) {
            $this->maponLogger()->warning('Mapon response not ok (legacy)', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        }

        $json  = $response->json();
        $units = data_get($json, 'data.units', []);

        if (!is_array($units) || empty($units)) {
            $this->maponLogger()->warning('Mapon response has no units (legacy)', [
                'unit_id' => (string) $unitId,
                'json'    => $json,
            ]);
            return null;
        }

        $unit = collect($units)->firstWhere('unit_id', (int) $unitId) ?? $units[0];

        if (!is_array($unit)) return null;

        $this->maponLogger()->info('Mapon response snapshot (legacy)', [
            'unit_id'     => (string) $unitId,
            'has_can'     => array_key_exists('can', $unit),
            'can_odom'    => data_get($unit, 'can.odom.value'),
            'mileage'     => data_get($unit, 'mileage'),
            'last_update' => data_get($unit, 'last_update'),
        ]);

        return $unit;
    }

    public function getMileageRaw(int|string $unitId, int $companyId): ?float
    {
        $data = $this->getUnitData($unitId, $companyId);
        if (!$data) return null;

        return isset($data['mileage']) ? (float) $data['mileage'] : null;
    }
}
