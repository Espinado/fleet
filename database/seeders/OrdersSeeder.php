<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\OrderCargo;
use App\Models\OrderStep;
use App\Models\TransportOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Заказы на перевозку — все возможные кейсы.
 * Используем только свои фирмы: экспедиторы и перевозчики (третья сторона не определена).
 * Заказы распределяем по разным экспедиторам.
 * В шагах заполняем city_id по выбранной стране (город — в поле город, не в адрес).
 *
 * 1. Пустой черновик (без шагов и грузов)
 * 2. Только маршрут (шаги без грузов)
 * 3. Только груз (один груз без шагов)
 * 4. Полный заказ: маршрут + один груз
 * 5. Сборный груз: маршрут + несколько грузов (разные клиенты/цены/даты)
 * 6. Заказ с TIR/таможней
 * 7. Груз с полными характеристиками (packages, hazmat, temperature и т.д.)
 * 8. Отменённый заказ
 */
class OrdersSeeder extends Seeder
{
    private array $countryIds = [16, 17, 13, 8, 11]; // LV, LT, HU, EE, DE

    public function run(): void
    {
        // Все свои экспедиторы — распределяем заказы между ними
        $expeditors = Company::where('type', 'expeditor')
            ->where(function ($q) {
                $q->where('is_third_party', false)->orWhereNull('is_third_party');
            })
            ->orderBy('id')
            ->get();
        if ($expeditors->isEmpty()) {
            $this->command->warn('OrdersSeeder: no our expeditor company. Run CompaniesSeeder first.');
            return;
        }

        $clients = Client::orderBy('id')->get();
        if ($clients->count() < 3) {
            $this->command->warn('OrdersSeeder: need at least 3 clients.');
            return;
        }

        $expeditorIndex = 0;
        $nextExpeditor = function () use ($expeditors, &$expeditorIndex) {
            $e = $expeditors[$expeditorIndex % $expeditors->count()];
            $expeditorIndex++;
            return $e->id;
        };

        // 1. Пустой черновик
        $this->createOrder([
            'expeditor_id' => $nextExpeditor(),
            'status'       => OrderStatus::DRAFT,
            'notes'        => 'Пустой заказ — шаги и грузы добавим позже.',
            'steps'        => [],
            'cargos'       => [],
        ]);

        // 2. Только маршрут (2 шага), без грузов — город в city_id, адрес отдельно
        $this->createOrder([
            'expeditor_id' => $nextExpeditor(),
            'status'       => OrderStatus::DRAFT,
            'notes'        => 'Маршрут известен, груз уточним.',
            'steps'        => [
                ['type' => 'loading',   'country_id' => 16, 'city_name' => 'Rīga',   'address' => 'ostas nams', 'date' => now()->addDays(5), 'order' => 1],
                ['type' => 'unloading', 'country_id' => 17, 'city_name' => 'Vilnius', 'address' => 'logistikos centras', 'date' => now()->addDays(6), 'order' => 2],
            ],
            'cargos'       => [],
        ]);

        // 3. Только груз (1 груз), без шагов
        $this->createOrder([
            'expeditor_id' => $nextExpeditor(),
            'customer_id'  => $clients[0]->id,
            'requested_date_from' => now()->addDays(7),
            'requested_date_to'   => now()->addDays(10),
            'quoted_price' => 850.00,
            'status'       => OrderStatus::QUOTED,
            'notes'        => 'Груз известен, маршрут согласуем.',
            'steps'        => [],
            'cargos'       => [
                [
                    'customer_id'         => $clients[0]->id,
                    'shipper_id'          => $clients[0]->id,
                    'consignee_id'        => $clients[1]->id,
                    'description'        => 'Paletes ar pārtiku',
                    'weight_kg'           => 1200,
                    'volume_m3'           => 33,
                    'pallets'             => 24,
                    'quoted_price'        => 850.00,
                    'requested_date_from' => now()->addDays(7),
                    'requested_date_to'   => now()->addDays(10),
                ],
            ],
        ]);

        // 4. Полный заказ: маршрут + один груз (Латвия → Эстония)
        $this->createOrder([
            'expeditor_id' => $nextExpeditor(),
            'customer_id'  => $clients[2]->id,
            'requested_date_from' => now()->addDays(12),
            'requested_date_to'   => now()->addDays(14),
            'quoted_price' => 1200.00,
            'status'       => OrderStatus::CONFIRMED,
            'notes'        => null,
            'steps'        => [
                ['type' => 'loading',   'country_id' => 16, 'city_name' => 'Rīga',   'address' => 'noliktava', 'date' => now()->addDays(12), 'time' => '08:00', 'order' => 1],
                ['type' => 'unloading', 'country_id' => 8,  'city_name' => 'Tallinn', 'address' => 'logistikos centras', 'date' => now()->addDays(14), 'time' => '14:00', 'order' => 2],
            ],
            'cargos'       => [
                [
                    'customer_id'         => $clients[2]->id,
                    'shipper_id'          => $clients[2]->id,
                    'consignee_id'        => $clients[3]->id ?? $clients[0]->id,
                    'description'        => 'Būvmateriāli',
                    'weight_kg'           => 20000,
                    'volume_m3'           => 80,
                    'pallets'             => 20,
                    'quoted_price'        => 1200.00,
                    'requested_date_from' => now()->addDays(12),
                    'requested_date_to'   => now()->addDays(14),
                ],
            ],
        ]);

        // 5. Сборный груз: несколько грузов, разные клиенты и цены (LV → LT → DE)
        $c1 = $clients[4]->id ?? $clients[0]->id;
        $c2 = $clients[5]->id ?? $clients[1]->id;
        $this->createOrder([
            'expeditor_id' => $nextExpeditor(),
            'customer_id'  => $c1,
            'requested_date_from' => now()->addDays(15),
            'requested_date_to'   => now()->addDays(18),
            'quoted_price' => 2100.00, // 900 + 1200
            'status'       => OrderStatus::CONFIRMED,
            'notes'        => 'Konsolidētais kravas reiss.',
            'steps'        => [
                ['type' => 'loading',   'country_id' => 16, 'city_name' => 'Rīga', 'address' => 'A noliktava', 'date' => now()->addDays(15), 'order' => 1],
                ['type' => 'loading',   'country_id' => 16, 'city_name' => 'Rīga', 'address' => 'B noliktava', 'date' => now()->addDays(15), 'order' => 2],
                ['type' => 'unloading', 'country_id' => 17, 'city_name' => 'Kaunas', 'address' => 'logistikos centras', 'date' => now()->addDays(17), 'order' => 3],
                ['type' => 'unloading', 'country_id' => 11, 'city_name' => 'Berlin', 'address' => 'depo', 'date' => now()->addDays(18), 'order' => 4],
            ],
            'cargos'       => [
                [
                    'customer_id'         => $c1,
                    'shipper_id'          => $c1,
                    'consignee_id'        => $clients[6]->id ?? $clients[0]->id,
                    'description'        => 'Grupa 1 — elektronika',
                    'weight_kg'           => 500,
                    'volume_m3'           => 15,
                    'pallets'             => 5,
                    'quoted_price'        => 900.00,
                    'requested_date_from' => now()->addDays(15),
                    'requested_date_to'   => now()->addDays(17),
                ],
                [
                    'customer_id'         => $c2,
                    'shipper_id'          => $c2,
                    'consignee_id'        => $clients[7]->id ?? $clients[1]->id,
                    'description'        => 'Grupa 2 — metālis',
                    'weight_kg'           => 8000,
                    'volume_m3'           => 40,
                    'pallets'             => 16,
                    'quoted_price'        => 1200.00,
                    'requested_date_from' => now()->addDays(15),
                    'requested_date_to'   => now()->addDays(18),
                ],
            ],
        ]);

        // 6. Заказ с TIR / таможня (LV → HU)
        $this->createOrder([
            'expeditor_id'   => $nextExpeditor(),
            'customer_id'    => $clients[0]->id,
            'requested_date_from' => now()->addDays(20),
            'requested_date_to'   => now()->addDays(23),
            'quoted_price'   => 2500.00,
            'status'         => OrderStatus::QUOTED,
            'customs'        => true,
            'customs_address'=> 'TIR punkts "Šķēršļi", robeža LV/BY',
            'notes'          => 'TIR noformēšana.',
            'steps'          => [
                ['type' => 'loading',   'country_id' => 16, 'city_name' => 'Rīga',   'address' => 'ostas zona', 'date' => now()->addDays(20), 'order' => 1],
                ['type' => 'unloading', 'country_id' => 13, 'city_name' => 'Budapest', 'address' => 'noliktava', 'date' => now()->addDays(23), 'order' => 2],
            ],
            'cargos'       => [
                [
                    'customer_id'         => $clients[0]->id,
                    'shipper_id'          => $clients[0]->id,
                    'consignee_id'        => $clients[1]->id,
                    'description'        => 'TIR kravas apraksts',
                    'weight_kg'           => 18000,
                    'volume_m3'           => 82,
                    'pallets'             => 22,
                    'quoted_price'        => 2500.00,
                    'requested_date_from' => now()->addDays(20),
                    'requested_date_to'   => now()->addDays(23),
                ],
            ],
        ]);

        // 7. Груз с полными характеристиками (packages, hazmat, temperature, instructions, remarks)
        $this->createOrder([
            'expeditor_id' => $nextExpeditor(),
            'customer_id'  => $clients[1]->id,
            'requested_date_from' => now()->addDays(25),
            'requested_date_to'   => now()->addDays(26),
            'quoted_price' => 1800.00,
            'status'       => OrderStatus::QUOTED,
            'notes'        => 'Ārējais temperatūras režīms, ADR.',
            'steps'        => [
                ['type' => 'loading',   'country_id' => 16, 'city_name' => 'Rīga',   'address' => 'saldētava', 'date' => now()->addDays(25), 'time' => '06:00', 'order' => 1],
                ['type' => 'unloading', 'country_id' => 17, 'city_name' => 'Vilnius', 'address' => 'noliktava', 'date' => now()->addDays(26), 'order' => 2],
            ],
            'cargos'       => [
                [
                    'customer_id'         => $clients[1]->id,
                    'shipper_id'          => $clients[1]->id,
                    'consignee_id'        => $clients[2]->id,
                    'description'        => 'Ārējais temperatūras režīms +2..+6 °C, ADR 3',
                    'weight_kg'           => 5000,
                    'net_weight'          => 4800,
                    'gross_weight'        => 5200,
                    'tonnes'              => 5.0,
                    'volume_m3'           => 45,
                    'loading_meters'      => 12.5,
                    'pallets'             => 18,
                    'packages'            => 72,
                    'units'               => 360,
                    'customs_code'        => '0402 10',
                    'hazmat'              => 'ADR 3',
                    'temperature'         => '+2..+6',
                    'stackable'           => true,
                    'instructions'        => 'Ielādēt ar saldētāju. Pārbaudīt temperatūru.',
                    'remarks'             => 'Pirmā piegāde šim klientam.',
                    'quoted_price'        => 1800.00,
                    'requested_date_from' => now()->addDays(25),
                    'requested_date_to'   => now()->addDays(26),
                ],
            ],
        ]);

        // 8. Отменённый заказ
        $this->createOrder([
            'expeditor_id' => $nextExpeditor(),
            'customer_id'  => $clients[0]->id,
            'requested_date_from' => now()->addDays(-5),
            'requested_date_to'   => now()->addDays(-3),
            'quoted_price' => 600.00,
            'status'       => OrderStatus::CANCELLED,
            'notes'        => 'Klients atcēlis pasūtījumu.',
            'steps'        => [
                ['type' => 'loading',   'country_id' => 16, 'city_name' => 'Rīga',    'address' => 'noliktava', 'date' => now()->addDays(-5), 'order' => 1],
                ['type' => 'unloading', 'country_id' => 17, 'city_name' => 'Klaipėda', 'address' => 'ostas iela', 'date' => now()->addDays(-3), 'order' => 2],
            ],
            'cargos'       => [
                [
                    'customer_id'         => $clients[0]->id,
                    'description'        => 'Atcelts kravas apraksts',
                    'quoted_price'        => 600.00,
                    'requested_date_from' => now()->addDays(-5),
                    'requested_date_to'   => now()->addDays(-3),
                ],
            ],
        ]);

        $this->command->info('OrdersSeeder: orders with all cases created.');
    }

    /**
     * @param array{expeditor_id: int, customer_id?: int, requested_date_from?: Carbon|string, requested_date_to?: Carbon|string, quoted_price?: float, status: OrderStatus, trip_id?: int, notes?: ?string, customs?: bool, customs_address?: ?string, steps: array, cargos: array} $data
     */
    private function createOrder(array $data, ?int $tripId = null): TransportOrder
    {
        $order = TransportOrder::create([
            'number'              => TransportOrder::generateNumber(),
            'order_date'          => now()->format('Y-m-d'),
            'expeditor_id'        => $data['expeditor_id'],
            'customer_id'         => $data['customer_id'] ?? null,
            'requested_date_from' => isset($data['requested_date_from'])
                ? Carbon::parse($data['requested_date_from'])->format('Y-m-d')
                : null,
            'requested_date_to'   => isset($data['requested_date_to'])
                ? Carbon::parse($data['requested_date_to'])->format('Y-m-d')
                : null,
            'quoted_price'        => $data['quoted_price'] ?? null,
            'currency'            => 'EUR',
            'status'              => $data['status']->value,
            'trip_id'             => $tripId ?? ($data['trip_id'] ?? null),
            'notes'               => $data['notes'] ?? null,
            'customs'             => $data['customs'] ?? false,
            'customs_address'     => $data['customs_address'] ?? null,
        ]);

        foreach ($data['steps'] as $idx => $s) {
            $countryId = $s['country_id'] ?? null;
            $cityId = $s['city_id'] ?? null;
            if ($cityId === null && $countryId !== null && !empty($s['city_name'])) {
                $cityId = $this->resolveStepCityId((int) $countryId, $s['city_name']);
            }
            OrderStep::create([
                'transport_order_id' => $order->id,
                'type'               => $s['type'] ?? 'loading',
                'country_id'         => $countryId,
                'city_id'            => $cityId,
                'address'            => $s['address'] ?? null,
                'date'               => isset($s['date']) ? Carbon::parse($s['date'])->format('Y-m-d') : null,
                'time'               => $s['time'] ?? null,
                'contact_phone'      => $s['contact_phone'] ?? null,
                'order'              => $s['order'] ?? ($idx + 1),
                'notes'              => $s['notes'] ?? null,
            ]);
        }

        foreach ($data['cargos'] as $c) {
            OrderCargo::create([
                'transport_order_id'   => $order->id,
                'customer_id'          => $c['customer_id'] ?? null,
                'shipper_id'           => $c['shipper_id'] ?? null,
                'consignee_id'         => $c['consignee_id'] ?? null,
                'description'          => $c['description'] ?? null,
                'weight_kg'            => $c['weight_kg'] ?? null,
                'net_weight'           => $c['net_weight'] ?? null,
                'gross_weight'         => $c['gross_weight'] ?? null,
                'tonnes'              => $c['tonnes'] ?? null,
                'volume_m3'            => $c['volume_m3'] ?? null,
                'loading_meters'      => $c['loading_meters'] ?? null,
                'pallets'             => $c['pallets'] ?? null,
                'packages'            => $c['packages'] ?? null,
                'units'               => $c['units'] ?? null,
                'customs_code'        => $c['customs_code'] ?? null,
                'hazmat'              => $c['hazmat'] ?? null,
                'temperature'         => $c['temperature'] ?? null,
                'stackable'           => $c['stackable'] ?? false,
                'instructions'        => $c['instructions'] ?? null,
                'remarks'             => $c['remarks'] ?? null,
                'quoted_price'        => $c['quoted_price'] ?? null,
                'requested_date_from' => isset($c['requested_date_from'])
                    ? Carbon::parse($c['requested_date_from'])->format('Y-m-d')
                    : null,
                'requested_date_to'   => isset($c['requested_date_to'])
                    ? Carbon::parse($c['requested_date_to'])->format('Y-m-d')
                    : null,
            ]);
        }

        return $order;
    }

    /**
     * Получить city_id по стране и названию города (для подстановки в поле «Город», а не в адрес).
     */
    private function resolveStepCityId(int $countryId, ?string $cityName): ?int
    {
        if (!function_exists('getCitiesByCountryId')) {
            return null;
        }
        $cities = getCitiesByCountryId($countryId);
        if (empty($cities)) {
            return null;
        }
        if ($cityName !== null && $cityName !== '') {
            $cityName = trim($cityName);
            foreach ($cities as $id => $data) {
                $name = is_array($data) ? ($data['name'] ?? '') : (string) $data;
                if (strcasecmp($name, $cityName) === 0) {
                    return (int) $id;
                }
            }
        }
        return (int) array_key_first($cities);
    }
}
