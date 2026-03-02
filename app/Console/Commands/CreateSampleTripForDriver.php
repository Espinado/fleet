<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripCargoItem;
use App\Models\TripStep;
use App\Models\Company;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Truck;
use App\Enums\TripStatus;

class CreateSampleTripForDriver extends Command
{
    protected $signature = 'demo:create-sample-trip
                            {driver_id=16 : Driver ID}
                            {company_id=2 : Carrier company ID}';

    protected $description = 'Create a demo trip with 2 cargos and 2 items each for a given driver/company';

    public function handle(): int
    {
        $driverId = (int) $this->argument('driver_id');
        $companyId = (int) $this->argument('company_id');

        /** @var Driver $driver */
        $driver = Driver::query()
            ->where('id', $driverId)
            ->where('company_id', $companyId)
            ->first();

        if (!$driver) {
            $this->error("Driver #{$driverId} for company #{$companyId} not found.");
            return self::FAILURE;
        }

        /** @var Company $carrier */
        $carrier = Company::query()->find($companyId);
        if (!$carrier) {
            $this->error("Carrier company #{$companyId} not found.");
            return self::FAILURE;
        }

        /** @var Truck $truck */
        $truck = Truck::query()
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('id')
            ->first();

        if (!$truck) {
            $this->error("No active truck found for company #{$companyId}.");
            return self::FAILURE;
        }

        // Expeditor = carrier for this demo
        $expeditor = $carrier;

        // Pick any country/city from config
        $countries = config('countries', []);
        if (empty($countries)) {
            $this->error('config/countries.php is empty or not configured.');
            return self::FAILURE;
        }

        $countryId = (int) array_key_first($countries);
        $cities    = $countries[$countryId]['cities'] ?? [];
        $cityId    = $cities ? (int) array_key_first($cities) : 1;

        $startDate = Carbon::now()->startOfDay();
        $endDate   = Carbon::now()->addDays(3)->startOfDay();

        // 1) Trip
        $trip = Trip::create([
            'expeditor_id'        => $expeditor->id,
            'expeditor_name'      => $expeditor->name,
            'expeditor_reg_nr'    => $expeditor->reg_nr,
            'expeditor_country'   => $expeditor->country,
            'expeditor_city'      => $expeditor->city,
            'expeditor_address'   => $expeditor->address,
            'expeditor_post_code' => $expeditor->post_code,
            'expeditor_email'     => $expeditor->email,
            'expeditor_phone'     => $expeditor->phone,

            'carrier_company_id'  => $carrier->id,
            'driver_id'           => $driver->id,
            'truck_id'            => $truck->id,
            'trailer_id'          => null,

            'start_date' => $startDate->toDateString(),
            'end_date'   => $endDate->toDateString(),
            'currency'   => 'EUR',
            'status'     => TripStatus::PLANNED,

            'cont_nr'    => null,
            'seal_nr'    => null,
        ]);

        // 2) Steps (simple loading/unloading)
        $loadingStep = TripStep::create([
            'trip_id'    => $trip->id,
            'order'      => 1,
            'type'       => 'loading',
            'country_id' => $countryId,
            'city_id'    => $cityId,
            'address'    => 'Loading address (demo)',
            'date'       => $startDate->toDateString(),
            'time'       => '08:00',
        ]);

        $unloadingStep = TripStep::create([
            'trip_id'    => $trip->id,
            'order'      => 2,
            'type'       => 'unloading',
            'country_id' => $countryId,
            'city_id'    => $cityId,
            'address'    => 'Unloading address (demo)',
            'date'       => $endDate->toDateString(),
            'time'       => '16:00',
        ]);

        // 3) Two cargos with 2 items each; customers from clients table
        $clients = Client::query()
            ->orderBy('id')
            ->take(2)
            ->get();

        if ($clients->count() < 2) {
            $this->error('Need at least 2 clients in clients table to use as demo customers.');
            return self::FAILURE;
        }

        foreach ([1, 2] as $i) {
            $client = $clients[$i - 1];

            $price = 1000 * $i;
            $taxPercent = 21;
            $taxAmount = round($price * $taxPercent / 100, 2);
            $priceWithTax = $price + $taxAmount;

            $cargo = TripCargo::create([
                'trip_id'      => $trip->id,
                'customer_id'  => $client->id,
                'shipper_id'   => $client->id,
                'consignee_id' => $client->id,

                'price'            => $price,
                'tax_percent'      => $taxPercent,
                'total_tax_amount' => $taxAmount,
                'price_with_tax'   => $priceWithTax,
                'currency'         => 'EUR',
                'payment_terms'    => $endDate->copy()->addDays(14),
                'payer_type_id'    => 1,

                'commercial_invoice_nr'     => 'INV-' . $trip->id . '-' . $i,
                'commercial_invoice_amount' => $priceWithTax,
            ]);

            // Link cargo to steps
            $cargo->steps()->attach([
                $loadingStep->id   => ['role' => 'loading'],
                $unloadingStep->id => ['role' => 'unloading'],
            ]);

            // 2 items per cargo
            for ($j = 1; $j <= 2; $j++) {
                TripCargoItem::create([
                    'trip_cargo_id' => $cargo->id,

                    'description'   => "Demo cargo {$i}-{$j}",

                    'packages'      => 10 * $j,
                    'pallets'       => 2 * $j,
                    'units'         => 100 * $j,

                    'net_weight'    => 500 * $j,
                    'gross_weight'  => 520 * $j,
                    'tonnes'        => (500 * $j) / 1000,

                    'volume'        => 5.5 * $j,
                    'loading_meters'=> 2.4 * $j,

                    'hazmat'        => null,
                    'temperature'   => null,
                    'stackable'     => true,
                    'customs_code'  => 'demo-' . $j,

                    'instructions'  => 'Handle with care (demo)',
                    'remarks'       => 'Generated by demo:create-sample-trip',

                    'price'         => $priceWithTax / 2,
                    'tax_percent'   => $taxPercent,
                    'tax_amount'    => $taxAmount / 2,
                    'price_with_tax'=> $priceWithTax / 2,
                ]);
            }
        }

        $this->info("Demo trip #{$trip->id} created for driver #{$driverId} (company #{$companyId}).");

        return self::SUCCESS;
    }
}

