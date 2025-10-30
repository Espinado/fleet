<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\Trailer;
use Carbon\Carbon;
use App\Enums\TripStatus;

class TripsWithCargosSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $expeditors = config('companies');

            if (empty($expeditors)) {
                $this->command->warn('⚠️ Нет данных в config/companies.php');
                return;
            }

            $clients = Client::inRandomOrder()->take(6)->get();
            if ($clients->count() < 6) {
                $this->command->warn('⚠️ Нужно минимум 6 клиентов в таблице clients');
                return;
            }

            // 🔹 Разделяем клиентов на отправителей и получателей
            $shippers   = $clients->take(3);
            $consignees = $clients->skip(3)->take(3);

            // === Основной маршрут (фиксированный) ===
            $loadingCountryId   = 16; // Latvia
            $loadingCityId      = 1;  // Riga
            $unloadingCountryId = 17; // Lithuania
            $unloadingCityId    = 1;  // Vilnius

            foreach ($expeditors as $companyId => $expeditor) {
                $expeditorName = $expeditor['name'] ?? 'Unknown Expeditor';
                $this->command->info("🏢 Обработка компании #{$companyId}: {$expeditorName}");

                // === Ищем ресурсы компании ===
                $driver  = Driver::where('company', $companyId)->inRandomOrder()->first();
                $truck   = Truck::where('company', $companyId)->inRandomOrder()->first();
                $trailer = Trailer::where('company', $companyId)->inRandomOrder()->first();

                if (!$driver || !$truck || !$trailer) {
                    $this->command->warn("⚠️ У компании {$expeditorName} нет полного комплекта транспорта");
                    $this->command->line("   Driver: " . ($driver?->id ?? '❌') . ", Truck: " . ($truck?->id ?? '❌') . ", Trailer: " . ($trailer?->id ?? '❌'));
                    continue;
                }

                // === Создаём рейс (Trip) ===
                $trip = Trip::create([
                    'expeditor_id'      => $companyId,
                    'expeditor_name'    => $expeditorName,
                    'expeditor_reg_nr'  => $expeditor['reg_nr'] ?? '000000000',
                    'expeditor_country' => $expeditor['country'] ?? 'Latvia',
                    'expeditor_city'    => $expeditor['city'] ?? 'Riga',
                    'expeditor_address' => $expeditor['address'] ?? 'Default Street 1',
                    'expeditor_email'   => $expeditor['email'] ?? 'info@example.com',
                    'expeditor_phone'   => $expeditor['phone'] ?? '+37100000000',

                    'driver_id'  => $driver->id,
                    'truck_id'   => $truck->id,
                    'trailer_id' => $trailer->id,

                    'status'     => TripStatus::PLANNED->value,
                    'currency'   => 'EUR',
                    'price'      => 0,
                    'start_date' => Carbon::now()->addDays(1),
                    'end_date'   => Carbon::now()->addDays(7),
                ]);

                $this->command->info("🚛 Создан Trip #{$trip->id} для {$expeditorName}");

                // === Добавляем сборные грузы ===
                $totalPrice = 0;

                foreach ($shippers as $index => $shipper) {
                    $consignee = $consignees[$index] ?? $consignees->random();

                    for ($cargoIndex = 1; $cargoIndex <= 2; $cargoIndex++) {
                        $items = [
                            [
                                'description' => fake()->randomElement(['wood', 'metal', 'hay', 'cement', 'bricks']),
                                'weight' => fake()->numberBetween(300, 1000),
                                'volume' => fake()->randomFloat(2, 1, 3),
                            ],
                            [
                                'description' => fake()->randomElement(['paper', 'fuel', 'tools', 'food']),
                                'weight' => fake()->numberBetween(300, 1000),
                                'volume' => fake()->randomFloat(2, 1, 3),
                            ],
                        ];

                        $price = fake()->randomFloat(2, 400, 1200);
                        $totalPrice += $price;

                        TripCargo::create([
                            'trip_id'           => $trip->id,
                            'shipper_id'        => $shipper->id,
                            'consignee_id'      => $consignee->id,

                            // === Фиксированный маршрут Rīga → Vilnius ===
                            'loading_country_id'   => $loadingCountryId,
                            'loading_city_id'      => $loadingCityId,
                            'loading_address'      => 'Rīga Warehouse, Brīvības iela 120',
                            'loading_date'         => Carbon::now()->addDays(fake()->numberBetween(1, 2)),

                            'unloading_country_id' => $unloadingCountryId,
                            'unloading_city_id'    => $unloadingCityId,
                            'unloading_address'    => 'Vilnius Logistics Park, Ukmergės g. 100',
                            'unloading_date'       => Carbon::now()->addDays(fake()->numberBetween(4, 6)),

                            // === Грузовые данные ===
                            'cargo_description' => implode(', ', array_column($items, 'description')),
                            'cargo_packages'    => fake()->numberBetween(1, 5),
                            'cargo_weight'      => array_sum(array_column($items, 'weight')),
                            'cargo_volume'      => array_sum(array_column($items, 'volume')),

                            'price'             => $price,
                            'currency'          => 'EUR',
                            'payment_terms'     => Carbon::now()->addDays(30),
                            'payer_type_id'     => fake()->randomElement([1, 2]),

                            'cargo_instructions' => fake()->sentence(),
                            'cargo_remarks'      => fake()->sentence(),
                            'items_json'         => $items,
                        ]);
                    }
                }

                // === Обновляем общую сумму рейса ===
                $trip->update(['price' => $totalPrice]);
                $this->command->info("✅ Добавлены сборные грузы на сумму €{$totalPrice}");
                $this->command->line(str_repeat('—', 60));
            }

            $this->command->info('🎯 Все компании успешно обработаны. Генерация завершена!');
        });
    }
}
