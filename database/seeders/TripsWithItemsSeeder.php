<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Models\{Trip, TripCargo, TripCargoItem, Driver, Truck, Trailer, Client};
use App\Helpers\CalculateTax;

class TripsWithItemsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Нужны минимум 9 клиентов (3 shipper, 3 consignee, 3 customer)
        $clients = Client::inRandomOrder()->take(9)->get();
        if ($clients->count() < 9) {
            $this->command->warn('⚠️ Нужно минимум 9 клиентов в таблице clients.');
            return;
        }

        $shippers   = $clients->take(3)->values();
        $consignees = $clients->skip(3)->take(3)->values();
        $customers  = $clients->skip(6)->take(3)->values();

        // Разрешённые страны и города для адресов
        $countryPool = [16, 17, 13, 21, 8]; // LV, LT, PL, EE, DE
        $today = Carbon::now();

        $expeditors = collect(config('companies', []));
        if ($expeditors->isEmpty()) {
            $this->command->warn('⚠️ config("companies") пуст');
            return;
        }

        $payers = collect(config('payers', []));
        if ($payers->isEmpty()) {
            $this->command->warn('⚠️ config("payers") пуст');
            return;
        }

        foreach ($expeditors as $companyId => $expeditor) {
            $expeditorName = $expeditor['name'] ?? "Company #{$companyId}";
            $banks = $expeditor['bank'] ?? [];
            $bankId = $banks ? array_rand($banks) : null;
            $bank   = $bankId ? ($banks[$bankId] ?? null) : null;

            $this->command->info("🏢 Экспедитор #{$companyId}: {$expeditorName}");

            // Транспорт от этой компании
            $driver  = Driver::where('company', $companyId)->inRandomOrder()->first();
            $truck   = Truck::where('company', $companyId)->inRandomOrder()->first();
            $trailer = Trailer::where('company', $companyId)->inRandomOrder()->first();

            if (!$driver || !$truck || !$trailer) {
                $this->command->warn("⚠️ Пропуск: нет полного комплекта транспорта для компании #{$companyId}");
                continue;
            }

            // Создаём по 2 рейса на экспедитора
            for ($t = 1; $t <= 2; $t++) {
                DB::transaction(function () use (
                    $faker, $today, $t,
                    $companyId, $expeditor, $expeditorName, $bank, $bankId,
                    $driver, $truck, $trailer,
                    $shippers, $consignees, $customers,
                    $countryPool, $payers
                ) {
                    // === Trip ===
                    $trip = Trip::create([
                        'expeditor_id'        => $companyId,
                        'expeditor_name'      => $expeditorName,
                        'expeditor_reg_nr'    => $expeditor['reg_nr'] ?? null,
                        'expeditor_country'   => $expeditor['country'] ?? null,
                        'expeditor_city'      => $expeditor['city'] ?? null,
                        'expeditor_address'   => $expeditor['address'] ?? null,
                        'expeditor_post_code' => $expeditor['post_code'] ?? null,
                        'expeditor_email'     => $expeditor['email'] ?? null,
                        'expeditor_phone'     => $expeditor['phone'] ?? null,

                        // Банк экспедитора
                        'expeditor_bank_id'   => $bankId,
                        'expeditor_bank'      => $bank['name'] ?? null,
                        'expeditor_iban'      => $bank['iban'] ?? null,
                        'expeditor_bic'       => $bank['bic'] ?? null,

                        // Транспорт
                        'driver_id'  => $driver->id,
                        'truck_id'   => $truck->id,
                        'trailer_id' => $trailer->id,

                        // Общая инфо
                        'status'     => 'planned',
                        'currency'   => 'EUR',
                        'start_date' => $today->copy()->addDays($t * 2),
                        'end_date'   => $today->copy()->addDays($t * 3),
                    ]);

                    $tripTotalWithTax = 0;

                    // === 2 груза на рейс ===
                    for ($c = 1; $c <= 2; $c++) {
                        $shipper   = $shippers->random();
                        $consignee = $consignees->random();
                        $customer  = $customers->random();

                        // Загрузка
                        $loadCountryId = Arr::random($countryPool);
                        $loadIso       = config("countries.$loadCountryId.iso") ?? 'lv';
                        $loadCities    = config("cities.$loadIso") ?? [];
                        $loadCityId    = $loadCities ? array_rand($loadCities) : null;

                        // Разгрузка
                        $unloadCountryId = Arr::random($countryPool);
                        $unloadIso       = config("countries.$unloadCountryId.iso") ?? 'lv';
                        $unloadCities    = config("cities.$unloadIso") ?? [];
                        $unloadCityId    = $unloadCities ? array_rand($unloadCities) : null;

                        // Плательщик
                        $payerType = Arr::random($payers->keys()->toArray());

                        // Ставка НДС ровно из диапазона [5,12,21]
                        $cargoTaxRate = fake()->randomElement([5, 12, 21]);

                        // === TripCargo ===
                        $cargo = TripCargo::create([
                            'trip_id'              => $trip->id,
                            'shipper_id'           => $shipper->id,
                            'consignee_id'         => $consignee->id,
                            'customer_id'          => $customer->id,

                            'loading_country_id'   => $loadCountryId,
                            'loading_city_id'      => $loadCityId,
                            'loading_address'      => 'Warehouse ' . $faker->randomElement(['A', 'B', 'C']),
                            'loading_date'         => $today->copy()->addDays($t),

                            'unloading_country_id' => $unloadCountryId,
                            'unloading_city_id'    => $unloadCityId,
                            'unloading_address'    => 'Terminal ' . $faker->randomElement(['1', '2', '3']),
                            'unloading_date'       => $today->copy()->addDays($t + 2),

                            'cargo_description'    => "Mixed goods shipment T{$trip->id}-C{$c}",
                            'currency'             => 'EUR',
                            'payment_terms'        => $today->copy()->addDays(30),
                            'payer_type_id'        => $payerType,
                            'tax_percent'          => $cargoTaxRate,
                        ]);

                        // === Позиции груза (3 шт) ===
                        $items = [];
                        for ($i = 1; $i <= 3; $i++) {
                            $priceWithTax = $faker->randomFloat(2, 300, 900);
                            $items[] = [
                                'description'        => "Goods #{$i} ({$cargoTaxRate}%)",
                                'packages'           => rand(2, 8),
                                'cargo_paletes'      => rand(0, 3),
                                'weight'             => rand(120, 380),
                                'cargo_netto_weight' => rand(100, 350),
                                'volume'             => rand(1, 6),
                                'price_with_tax'     => $priceWithTax,
                                'tax_percent'        => $cargoTaxRate,
                            ];
                        }

                        // Расчёт (из цены с НДС получаем price и tax_amount)
                        $calc = CalculateTax::forItems($items);

                        // Сохраняем позиции
                        foreach ($calc['items'] as $item) {
                            TripCargoItem::create([
                                'trip_cargo_id'       => $cargo->id,
                                'description'         => $item['description'],
                                'packages'            => $item['packages'],
                                'cargo_paletes'       => $item['cargo_paletes'],
                                'weight'              => $item['weight'],
                                'cargo_netto_weight'  => $item['cargo_netto_weight'],
                                'volume'              => $item['volume'],
                                'price'               => $item['price'],          // без НДС
                                'tax_percent'         => $item['tax_percent'],
                                'tax_amount'          => $item['tax_amount'],
                                'price_with_tax'      => $item['price_with_tax'],// с НДС
                            ]);
                        }

                        // Агрегаты для TripCargo
                        $sumPackages = collect($calc['items'])->sum('packages');
                        $sumPaletes  = collect($calc['items'])->sum('cargo_paletes');
                        $sumWeight   = collect($calc['items'])->sum('weight');
                        $sumNetto    = collect($calc['items'])->sum('cargo_netto_weight');
                        $sumVolume   = collect($calc['items'])->sum('volume');

                        $cargo->update([
                            'cargo_packages'     => (int) $sumPackages,
                            'cargo_paletes'      => (int) $sumPaletes,
                            'cargo_weight'       => $sumWeight,
                            'cargo_netto_weight' => $sumNetto,
                            'cargo_volume'       => $sumVolume,
                            'cargo_tonnes'       => round($sumWeight / 1000, 2),

                            // Финансы
                            'price'              => $calc['subtotal'],          // без НДС
                            'total_tax_amount'   => $calc['total_tax_amount'],  // НДС
                            'price_with_tax'     => $calc['price_with_tax'],    // с НДС
                        ]);

                        $this->command->info("📦 Cargo #{$cargo->id} — VAT {$cargoTaxRate}% — {$cargo->cargo_description}");
                        $tripTotalWithTax += $calc['price_with_tax'];
                    }

                    // Итог рейса: заполним trips.price суммой по грузам (с НДС), чтобы было видно “стоимость рейса”
                    $trip->update([
                        'price' => $tripTotalWithTax,
                    ]);

                    $this->command->info("🚛 Trip #{$trip->id} — total with VAT: " . number_format($tripTotalWithTax, 2, '.', ' ') . ' €');
                });
            }
        }

        $this->command->info('🎉 TripsWithItemsSeeder завершён успешно!');
    }
}
