<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{Trip, TripCargo, TripCargoItem, Driver, Truck, Trailer, Client};

class TripsWithItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Нужны минимум 6 клиентов
        $clients = Client::inRandomOrder()->take(6)->get();
        if ($clients->count() < 6) {
            $this->command->warn('⚠️ Нужно минимум 6 клиентов в таблице clients');
            return;
        }

        // 3 отправителя + 3 получателя
        $shippers   = $clients->take(3)->values();
        $consignees = $clients->skip(3)->take(3)->values();

        // Фиксированный маршрут: Riga → Vilnius
        $loadingCountryId   = 16; // LV
        $loadingCityId      = 1;  // Riga
        $unloadingCountryId = 17; // LT
        $unloadingCityId    = 1;  // Vilnius

        // Компании-экспедиторы из конфига
        $expeditors = collect(config('companies', []));
        if ($expeditors->isEmpty()) {
            $this->command->warn('⚠️ config("companies") пуст');
            return;
        }

        $today = Carbon::now();

        foreach ($expeditors as $companyId => $expeditor) {
            $expeditorName = $expeditor['name'] ?? "Company #{$companyId}";
            $this->command->info("🏢 Экспедитор #{$companyId}: {$expeditorName}");

            // Ресурсы ТОЛЬКО этой компании
            $driver  = Driver::where('company', $companyId)->inRandomOrder()->first();
            $truck   = Truck::where('company', $companyId)->inRandomOrder()->first();
            $trailer = Trailer::where('company', $companyId)->inRandomOrder()->first();

            if (!$driver || !$truck || !$trailer) {
                $this->command->warn("   ⚠️ Пропускаем: нет полного комплекта транспорта у {$expeditorName}");
                $this->command->line("   Driver: ".($driver?->id ?? '—').", Truck: ".($truck?->id ?? '—').", Trailer: ".($trailer?->id ?? '—'));
                continue;
            }

            // Для примера создадим 2 рейса на экспедитора
            for ($t = 1; $t <= 2; $t++) {
                DB::transaction(function () use (
                    $today, $t, $companyId, $expeditor, $expeditorName,
                    $driver, $truck, $trailer,
                    $loadingCountryId, $loadingCityId, $unloadingCountryId, $unloadingCityId,
                    $shippers, $consignees
                ) {
                    // === Trip ===
                    $trip = Trip::create([
                        'expeditor_id'        => $companyId,
                        'expeditor_name'      => $expeditorName,
                        'expeditor_reg_nr'    => $expeditor['reg_nr'] ?? '000000000',
                        'expeditor_country'   => $expeditor['country'] ?? 'Latvia',
                        'expeditor_city'      => $expeditor['city'] ?? 'Rīga',
                        'expeditor_address'   => $expeditor['address'] ?? 'Default Street 1',
                        'expeditor_post_code' => $expeditor['post_code'] ?? 'LV-1000',
                        'expeditor_email'     => $expeditor['email'] ?? 'info@example.com',
                        'expeditor_phone'     => $expeditor['phone'] ?? '+37100000000',

                        'driver_id'           => $driver->id,
                        'truck_id'            => $truck->id,
                        'trailer_id'          => $trailer->id,

                        'status'              => 'planned',
                        'start_date'          => $today->copy()->addDays($t * 2),
                        'end_date'            => $today->copy()->addDays($t * 3),
                        'currency'            => 'EUR',
                    ]);

                    // === Два груза (две пары shipper→consignee) ===
                    for ($c = 1; $c <= 2; $c++) {
                        $shipper   = $shippers->get(($t + $c - 2) % $shippers->count());
                        $consignee = $consignees->get(($t + $c - 2) % $consignees->count());

                        $cargo = TripCargo::create([
                            'trip_id'              => $trip->id,
                            'shipper_id'           => $shipper->id,
                            'consignee_id'         => $consignee->id,

                            'loading_country_id'   => $loadingCountryId,
                            'loading_city_id'      => $loadingCityId,
                            'loading_address'      => 'Industrial Park, Warehouse A',
                            'loading_date'         => $today->copy()->addDays($t),

                            'unloading_country_id' => $unloadingCountryId,
                            'unloading_city_id'    => $unloadingCityId,
                            'unloading_address'    => 'Logistics Center B',
                            'unloading_date'       => $today->copy()->addDays($t + 2),

                            'cargo_description'    => "Mixed goods shipment T{$trip->id}-C{$c}",
                            'cargo_packages'       => 0, // заполним позже суммами из items
                            'cargo_paletes'        => 0,
                            'cargo_tonnes'         => 0,
                            'cargo_weight'         => 0,
                            'cargo_netto_weight'   => 0,
                            'cargo_volume'         => 0,

                            'price'                => 0,
                            'total_tax_amount'     => 0,
                            'price_with_tax'       => 0,
                            'currency'             => 'EUR',
                            'payment_terms'        => $today->copy()->addDays($t + 10),
                            'payer_type_id'        => 1,
                        ]);

                        // === Три позиции с НДС 5%, 10%, 21%
                        $taxRates = [5, 10, 21];
                        $sum = [
                            'packages' => 0,
                            'paletes'  => 0,
                            'tonnes'   => 0,
                            'gross'    => 0,
                            'netto'    => 0,
                            'volume'   => 0,
                            'price'    => 0,
                            'tax'      => 0,
                        ];

                        foreach ($taxRates as $rate) {
                            $packages = rand(2, 8);
                            $paletes  = rand(0, 3);
                            $weight   = rand(120, 380);  // кг
                            $netto    = max(0, $weight - rand(10, 40));
                            $volume   = rand(1, 6);
                            $price    = rand(300, 900);  // без НДС

                            $taxAmount    = round($price * $rate / 100, 2);
                            $priceWithTax = round($price + $taxAmount, 2);

                            TripCargoItem::create([
                                'trip_cargo_id'       => $cargo->id,
                                'description'         => "Product with {$rate}% VAT",
                                'packages'            => $packages,
                                'cargo_paletes'       => $paletes,
                                'cargo_tonnes'        => 0,
                                'weight'              => $weight,
                                'cargo_netto_weight'  => $netto,
                                'volume'              => $volume,
                                'price'               => $price,
                                'tax_percent'         => $rate,
                                'tax_amount'          => $taxAmount,
                                'price_with_tax'      => $priceWithTax,
                                'instructions'        => '',
                                'remarks'             => '',
                            ]);

                            // агрегаты
                            $sum['packages'] += $packages;
                            $sum['paletes']  += $paletes;
                            $sum['gross']    += $weight;
                            $sum['netto']    += $netto;
                            $sum['volume']   += $volume;
                            $sum['price']    += $price;
                            $sum['tax']      += $taxAmount;
                        }

                        // обновляем агрегаты груза
                        $cargo->update([
                            'cargo_packages'     => $sum['packages'],
                            'cargo_paletes'      => $sum['paletes'],
                            'cargo_weight'       => $sum['gross'],
                            'cargo_netto_weight' => $sum['netto'],
                            'cargo_volume'       => $sum['volume'],

                            'price'              => $sum['price'],
                            'total_tax_amount'   => $sum['tax'],
                            'price_with_tax'     => $sum['price'] + $sum['tax'],
                        ]);
                    } // end for cargos
                }); // end transaction
            } // end for trips
        } // end foreach expeditor

        $this->command->info('🎉 Сидер TripsWithItemsSeeder: готово!');
    }
}
