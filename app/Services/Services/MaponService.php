<?php

namespace App\Services\Services;

use Illuminate\Support\Facades\Http;

class MaponService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('mapon.base_url', 'https://mapon.com/api/v1');
        $this->apiKey  = (string) config('mapon.key', '');
    }

    public function getUnitData(int|string $unitId, array|string|null $include = null): ?array
    {
        if (empty($this->apiKey)) return null;

        $url = rtrim($this->baseUrl, '/') . '/unit/list.json';

        $query = [
            'key'     => $this->apiKey,
            'unit_id' => $unitId,
        ];

        // Mapon принимает include=can (строкой) — как в твоём рабочем URL
        if (is_string($include) && $include !== '') {
            $query['include'] = $include;
        }

        // На всякий: если вдруг захочешь include[]=can&include[]=something
        if (is_array($include) && !empty($include)) {
            $query['include'] = $include; // Laravel сам превратит в include[0]=...
        }

        $response = Http::timeout(15)->get($url, $query);

        if (!$response->ok()) return null;

        $json = $response->json();

        return $json['data']['units'][0] ?? null;
    }

    public function getMileageRaw(int|string $unitId): ?float
    {
        $data = $this->getUnitData($unitId);
        if (!$data) return null;

        return isset($data['mileage']) ? (float) $data['mileage'] : null;
    }
}
