<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\{Trip, TripCargo, TripCargoItem, Driver, Truck, Trailer, Client};
use App\Helpers\CalculateTax;

class TripsWithItemsSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::inRandomOrder()->take(6)->get();

        if ($clients->count() < 6) {
            $this->command->warn('⚠️ Нужно минимум 6 клиентов в таблице clients');
            return;
        }

        $shippers   = $clients->take(3)->values();
        $consignees = $clients->skip(3)->take(3)->values();

        $countryPool = [16, 17, 12, 13]; // LV, LT, PL, HU
        $today = Carbon::now();

        $expeditors = collect(config('companies', []));
        if ($expeditors->isEmpty()) {
            $this->command->warn('⚠️ config("companies") пуст');
            return;
        }

        foreach ($expeditors as $companyId => $expeditor) {
            $expeditorName = $expeditor['name'] ?? "Company #{$companyId}";
            $this->command->info("🏢 Экспедитор #{$companyId}: {$expeditorName}");

            $driver  = Driver::where('company', $companyId)->inRandomOrder()->first();
            $truck   = Truck::where('company', $companyId)->inRandomOrder()->first();
            $trailer = Trailer::where('company', $companyId)->inRandomOrder()->first();

            if (!$driver || !$truck || !$trailer) {
                $this->command->warn("   ⚠️ Пропуск: нет полного комплекта транспорта");
                continue;
            }

            for ($t = 1; $t <= 2; $t++) {
                DB::transaction(function () use (
                    $today, $t, $companyId, $expeditor, $expeditorName,
                    $driver, $truck, $trailer, $shippers, $consignees, $countryPool
                ) {
                    // === Trip ===
                    $trip = Trip::create([
                        'expeditor_id'      => $companyId,
                        'expeditor_name'    => $expeditorName,
                        'expeditor_reg_nr'  => $expeditor['reg_nr'] ?? '000000000',
                        'expeditor_country' => $expeditor['country'] ?? 'Latvia',
                        'expeditor_city'    => $expeditor['city'] ?? 'Riga',
                        'expeditor_address' => $expeditor['address'] ?? 'Brīvības iela 100',
                        'expeditor_post_code' => $expeditor['post_code'] ?? 'LV-1000',
                        'expeditor_email'   => $expeditor['email'] ?? 'info@example.com',
                        'expeditor_phone'   => $expeditor['phone'] ?? '+37100000000',
                        'driver_id'         => $driver->id,
                        'truck_id'          => $truck->id,
                        'trailer_id'        => $trailer->id,
                        'status'            => 'planned',
                        'currency'          => 'EUR',
                        'start_date'        => $today->copy()->addDays($t * 2),
                        'end_date'          => $today->copy()->addDays($t * 3),
                    ]);

                    // === 2 груза ===
                    for ($c = 1; $c <= 2; $c++) {
                        $shipper   = $shippers->get(($t + $c - 2) % $shippers->count());
                        $consignee = $consignees->get(($t + $c - 2) % $consignees->count());

                        // 🎲 Выбираем случайную ставку для груза
                        $cargoTaxRate = fake()->randomElement([5, 12, 21]);

                        $cargo = TripCargo::create([
                            'trip_id'              => $trip->id,
                            'shipper_id'           => $shipper->id,
                            'consignee_id'         => $consignee->id,
                            'loading_country_id'   => fake()->randomElement($countryPool),
                            'loading_city_id'      => fake()->numberBetween(1, 15),
                            'loading_address'      => 'Warehouse ' . fake()->randomElement(['A', 'B', 'C']),
                            'loading_date'         => $today->copy()->addDays($t),
                            'unloading_country_id' => fake()->randomElement($countryPool),
                            'unloading_city_id'    => fake()->numberBetween(1, 15),
                            'unloading_address'    => 'Terminal ' . fake()->randomElement(['1', '2', '3']),
                            'unloading_date'       => $today->copy()->addDays($t + 2),
                            'cargo_description'    => "Mixed goods shipment T{$trip->id}-C{$c}",
                            'currency'             => 'EUR',
                            'payment_terms'        => $today->copy()->addDays(30),
                            'payer_type_id'        => 1,
                            'tax_percent'          => $cargoTaxRate,
                        ]);

                        // === Генерируем товары ===
                       $items = [];
for ($i = 1; $i <= 3; $i++) {
    $priceWithTax = fake()->randomFloat(2, 300, 900);
    $items[] = [
        'description'        => "Goods #{$i} with {$cargoTaxRate}% VAT",
        'packages'           => rand(2, 8),
        'cargo_paletes'      => rand(0, 3),
        'weight'             => rand(120, 380),
        'cargo_netto_weight' => rand(100, 350),
        'volume'             => rand(1, 6),
        'price_with_tax'     => $priceWithTax,        // ✅ правильное поле
        'tax_percent'        => $cargoTaxRate,
    ];
}

                        // === Расчёт через хелпер ===
                        $calc = CalculateTax::forItems($items);

                        // === Сохраняем позиции ===
                        foreach ($calc['items'] as $item) {
                            TripCargoItem::create([
                                'trip_cargo_id'       => $cargo->id,
                                'description'         => $item['description'],
                                'packages'            => $item['packages'],
                                'cargo_paletes'       => $item['cargo_paletes'],
                                'weight'              => $item['weight'],
                                'cargo_netto_weight'  => $item['cargo_netto_weight'],
                                'volume'              => $item['volume'],
                                'price'               => $item['price'],
                                'tax_percent'         => $item['tax_percent'],
                                'tax_amount'          => $item['tax_amount'],
                                'price_with_tax'      => $item['price_with_tax'],
                            ]);
                        }

                        // === Обновляем агрегаты ===
                        $sumWeightKg = collect($calc['items'])->sum('weight');
                        $cargoTonnes = round($sumWeightKg / 1000, 2);

                        $cargo->update([
                            'cargo_packages'     => collect($calc['items'])->sum('packages'),
                            'cargo_paletes'      => collect($calc['items'])->sum('cargo_paletes'),
                            'cargo_weight'       => $sumWeightKg,
                            'cargo_netto_weight' => collect($calc['items'])->sum('cargo_netto_weight'),
                            'cargo_volume'       => collect($calc['items'])->sum('volume'),
                            'cargo_tonnes'       => $cargoTonnes,
                            'price'              => $calc['subtotal'],
                            'total_tax_amount'   => $calc['total_tax_amount'],
                            'price_with_tax'     => $calc['price_with_tax'],
                        ]);

                        // === 🧾 Вывод в консоль ===
                        $rows = collect($calc['items'])->map(fn($i) => [
                            $i['description'],
                            number_format($i['price'], 2, '.', ' ') . ' €',
                            "{$i['tax_percent']}%",
                            number_format($i['tax_amount'], 2, '.', ' ') . ' €',
                            number_format($i['price_with_tax'], 2, '.', ' ') . ' €',
                        ])->toArray();

                        $rows[] = ['—', '—', '—', '—', '—'];
                        $rows[] = [
                            'Subtotal',
                            number_format($calc['subtotal'], 2, '.', ' ') . ' €',
                            '',
                            number_format($calc['total_tax_amount'], 2, '.', ' ') . ' €',
                            number_format($calc['price_with_tax'], 2, '.', ' ') . ' €',
                        ];

                        $this->command->line('');
                        $this->command->info("📦 Cargo #{$cargo->id} — VAT {$cargoTaxRate}% — {$cargo->cargo_description}");
                        $this->command->table(
                            ['Description', 'Price (no VAT)', 'Tax %', 'Tax Amount', 'Price with VAT'],
                            $rows
                        );
                    } // cargos
                });
            } // trips
        } // expeditors

        $this->command->info('🎉 TripsWithItemsSeeder завершён успешно!');
    }
}
