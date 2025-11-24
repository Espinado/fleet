<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ClientsTableSeeder;
use App\Models\{
    Driver,
    Truck,
    Trailer,
    Client,
    Trip,
    TripStep,
    TripCargo,
    TripCargoItem
};
use App\Helpers\CalculateTax;

class FleetTestSeeder extends Seeder
{
     public function run(): void
    {
        $taxRates = [0, 5, 10, 21];

        // === Basic demo data ===
        $drivers  = Driver::factory()->count(5)->create();
        $trucks   = Truck::factory()->count(5)->create();
        $trailers = Trailer::factory()->count(5)->create();

        $this->call(\Database\Seeders\ClientsTableSeeder::class);
        $clients = Client::all();

        // === 3 рейса ===
        foreach (range(1, 3) as $tripIndex) {

            /* ============================================================
             *  EXPEDITOR SNAPSHOT
             * ============================================================ */
            $expeditors = config('companies');
            $expeditorId = array_key_first($expeditors);
            $exp = $expeditors[$expeditorId] ?? [];
            $bankIndex = isset($exp['bank']) ? array_key_first($exp['bank']) : null;
            $bankData = $bankIndex ? ($exp['bank'][$bankIndex] ?? []) : [];

            $expSnapshot = [
                'expeditor_id'        => $expeditorId,
                'expeditor_name'      => $exp['name'] ?? 'Expeditor Test',
                'expeditor_reg_nr'    => $exp['reg_nr'] ?? 'LV123456789',
                'expeditor_country'   => $exp['country'] ?? 'Latvia',
                'expeditor_city'      => $exp['city'] ?? 'Riga',
                'expeditor_address'   => $exp['address'] ?? 'Street 1',
                'expeditor_post_code' => $exp['post_code'] ?? 'LV-1001',
                'expeditor_email'     => $exp['email'] ?? 'expeditor@test.lv',
                'expeditor_phone'     => $exp['phone'] ?? '+37120000000',

                'expeditor_bank_id' => $bankIndex,
                'expeditor_bank'    => $bankData['name'] ?? 'Bank Test',
                'expeditor_iban'    => $bankData['iban'] ?? 'LV00BANKTEST',
                'expeditor_bic'     => $bankData['bic'] ?? 'BANKLV2X',
            ];

            /* ============================================================
             *  CREATE TRIP
             * ============================================================ */
            $trip = Trip::create(array_merge($expSnapshot, [
                'driver_id'  => $drivers->random()->id,
                'truck_id'   => $trucks->random()->id,
                'trailer_id' => $trailers->random()->id,

                'currency'   => 'EUR',
                'status'     => 'planned',
                'start_date' => now(),
                'end_date'   => now()->addDays(7),
            ]));

            /* ============================================================
             *  CREATE STEPS (12 steps: 2 load + 2 unload per client × 3 clients)
             * ============================================================ */

            $steps = collect();
            $order = 1;

            // The 3 clients for this trip
            $tripClients = $clients->random(3);

            foreach ($tripClients as $clIndex => $client) {

                // 2 LOAD POINTS
                for ($i = 1; $i <= 2; $i++) {
                    $steps->push(
                        TripStep::create([
                            'trip_id'    => $trip->id,
                            'type'       => 'loading',
                            'client_id'  => $client->id,
                            'country_id' => rand(1,10),
                            'city_id'    => rand(1,10),
                            'address'    => "Loading address C".($clIndex+1)."/$i",
                            'date'       => now()->addDays($order),
                            'time'       => '08:00',
                            'order'      => $order++
                        ])
                    );
                }

                // 2 UNLOAD POINTS
                for ($i = 1; $i <= 2; $i++) {
                    $steps->push(
                        TripStep::create([
                            'trip_id'    => $trip->id,
                            'type'       => 'unloading',
                            'client_id'  => $client->id,
                            'country_id' => rand(1,10),
                            'city_id'    => rand(1,10),
                            'address'    => "Unloading address C".($clIndex+1)."/$i",
                            'date'       => now()->addDays($order),
                            'time'       => '15:00',
                            'order'      => $order++
                        ])
                    );
                }
            }

            /* ============================================================
             *  CREATE CARGOS (2 per client = 6 cargos)
             * ============================================================ */

            foreach ($tripClients as $client) {

                foreach (range(1, 2) as $cargoIndex) {

                    $price = rand(400, 1200);
                    $percent = $taxRates[array_rand($taxRates)];
                    $tax = CalculateTax::calculate($price, $percent);

                    $cargo = TripCargo::create([
                        'trip_id'      => $trip->id,

                        'customer_id'  => $client->id,
                        'shipper_id'   => $client->id,
                        'consignee_id' => $clients->random()->id,

                        'price'            => $price,
                        'tax_percent'      => $percent,
                        'total_tax_amount' => $tax['tax_amount'],
                        'price_with_tax'   => $tax['price_with_tax'],

                        'currency'      => 'EUR',
                        'payment_terms' => now()->addDays(rand(5,30)),
                        'payer_type_id' => rand(1,3),
                    ]);

                    /* -----------------------------------------------
                     *  ITEMS (2 items per cargo)
                     * ----------------------------------------------- */
                    foreach (range(1, 2) as $itemIndex) {
                        $itemPrice = rand(50, 200);
                        $itemTax = CalculateTax::calculate($itemPrice, $percent);

                        TripCargoItem::create([
                            'trip_cargo_id' => $cargo->id,
                            'description'   => "Item $itemIndex for Cargo $cargoIndex",

                            'packages' => rand(1,10),
                            'pallets'  => rand(0,3),
                            'units'    => rand(1,40),

                            'gross_weight' => rand(100, 500),
                            'net_weight'   => rand(90, 450),
                            'tonnes'       => rand(1,8) / 10,
                            'volume'       => rand(1,5),
                            'loading_meters'=> rand(1,3),

                            'stackable'     => rand(0,1),

                            'price'          => $itemPrice,
                            'tax_percent'    => $percent,
                            'tax_amount'     => $itemTax['tax_amount'],
                            'price_with_tax' => $itemTax['price_with_tax'],
                        ]);
                    }

                    /* ====================================================
                     *  PIVOT: SELECT 2 LOAD + 2 UNLOAD STEPS FOR THIS CARGO
                     * ==================================================== */

                    // Steps belonging to this client
                    $clientSteps = $steps->filter(fn($s) => $s->client_id == $client->id);

                    $loadSteps  = $clientSteps->where('type','loading')->take(2);
                    $unloadSteps= $clientSteps->where('type','unloading')->take(2);

                    $pivot = [];

                    foreach ($loadSteps as $step) {
                        $pivot[$step->id] = ['role'=>'loading'];
                    }
                    foreach ($unloadSteps as $step) {
                        $pivot[$step->id] = ['role'=>'unloading'];
                    }

                    $cargo->steps()->attach($pivot);
                }
            }
        }

        echo "✔ MultiRouteSeeder completed\n";
    }
}