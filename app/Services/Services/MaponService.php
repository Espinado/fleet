<?php

namespace App\Services\Services;

use Illuminate\Support\Facades\Http;

class MaponService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        // ✅ config/mapon.php -> ['base_url' => ..., 'key' => ...]
        $this->baseUrl = config('mapon.base_url', 'https://mapon.com/api/v1');
        $this->apiKey  = (string) config('mapon.key', '');
    }

   public function getUnitData(int|string $unitId): ?array
{
    if (empty($this->apiKey)) return null;

    $url = rtrim($this->baseUrl, '/') . '/unit/list.json';

    $response = Http::timeout(15)->get($url, [
        'key'     => $this->apiKey,
        'unit_id' => $unitId,
    ]);

    if (!$response->ok()) return null;

    $json = $response->json();

    return $json['data']['units'][0] ?? null;
}



    public function getMileage(int|string $unitId): ?float
    {
        $data = $this->getUnitData($unitId);
        if (!$data) return null;

        return isset($data['mileage']) ? (float) $data['mileage'] : null;
    }
}
