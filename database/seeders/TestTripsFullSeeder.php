<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\Trip;
use App\Models\TripStep;
use App\Models\TripCargo;
use App\Models\TripCargoItem;
use App\Models\TripDocument;
use App\Models\TripStepDocument;
use App\Models\TripExpense;
use App\Models\Company;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\Trailer;

use App\Enums\TripStatus;
use App\Enums\TripDocumentType;
use App\Enums\StepDocumentType;
use App\Enums\TripExpenseCategory;
use App\Helpers\CalculateTax;

class TestTripsFullSeeder extends Seeder
{
    private string $placeholderPath = '';

    public function run(): void
    {
        $this->ensurePlaceholderFile();

        $countries = config('countries', []);
        if (empty($countries)) {
            $this->command->warn('config/countries.php пуст.');
            return;
        }

        $countryIdsWithCities = $this->getCountryIdsWithCities();
        if (empty($countryIdsWithCities)) {
            $this->command->warn('Нет стран с городами в config/cities/*.php');
            return;
        }

        $clients = Client::orderBy('id')->get();
        if ($clients->count() < 9) {
            $this->command->warn('Нужно минимум 9 клиентов. Запустите ClientsTableSeeder.');
            return;
        }

        $companies = Company::where('is_active', 1)->orderBy('id')->get();
        $driver = Driver::where('is_active', 1)->find(18);
        if (!$driver) {
            $this->command->warn('Водитель с id 18 не найден или не активен.');
            return;
        }

        $truck = Truck::where('company_id', $driver->company_id)->where('is_active', 1)->first();
        $trailer = Trailer::where('company_id', $driver->company_id)->where('is_active', 1)->first();
        if (!$truck || !$trailer) {
            $this->command->warn('У водителя 18 нет активного тягача или прицепа.');
            return;
        }

        $expeditor = Company::find($driver->company_id) ?? $companies->first();
        if (!$expeditor) {
            $this->command->warn('Компания экспедитора не найдена.');
            return;
        }

        $this->createTrip(
            $driver,
            $truck,
            $trailer,
            $expeditor,
            $expeditor,
            $clients,
            $countryIdsWithCities,
            false
        );
        $created = 1;

        $this->command->info("TestTripsFullSeeder: создано рейсов: {$created}");
    }

    private function ensurePlaceholderFile(): void
    {
        $dir = 'seeders';
        $path = "{$dir}/placeholder.txt";
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, 'Placeholder for test documents.');
        }
        $this->placeholderPath = $path;
    }

    private function getCountryIdsWithCities(): array
    {
        $countries = config('countries', []);
        $out = [];
        foreach ($countries as $id => $c) {
            $iso = $c['iso'] ?? null;
            if (!$iso) {
                continue;
            }
            $path = config_path('cities/' . strtolower($iso) . '.php');
            if (is_file($path)) {
                $out[] = (int) $id;
            }
        }
        return array_slice($out, 0, 10);
    }

    private function ensureThirdPartyCompanies(int $count): array
    {
        $list = [];
        for ($i = 1; $i <= $count; $i++) {
            $list[] = Company::firstOrCreate(
                ['slug' => 'third_party_test_' . $i],
                [
                    'name'          => 'Third Party Carrier Test ' . $i,
                    'type'          => 'carrier',
                    'reg_nr'        => 'TP' . $i . '000000',
                    'country'       => 'Latvia',
                    'city'          => 'Riga',
                    'address'       => 'Test address',
                    'post_code'     => 'LV-1000',
                    'email'         => 'tp' . $i . '@test.lv',
                    'phone'         => '+3710000000' . $i,
                    'is_third_party'=> true,
                    'is_system'     => false,
                    'is_active'     => true,
                ]
            );
        }
        return $list;
    }

    private function createTrip(
        Driver $driver,
        Truck $truck,
        Trailer $trailer,
        Company $expeditor,
        Company $carrier,
        $clients,
        array $countryIdsWithCities,
        bool $isThirdParty
    ): void {
        DB::transaction(function () use (
            $driver, $truck, $trailer, $expeditor, $carrier, $clients,
            $countryIdsWithCities, $isThirdParty
        ) {
            $startDate = Carbon::now()->addDays(rand(1, 5));
            $endDate = $startDate->copy()->addDays(3);
            $banks = $expeditor->banks_json;
            if (is_string($banks)) {
                $banks = json_decode($banks, true) ?: [];
            }
            $banks = is_array($banks) ? $banks : [];
            $bank = !empty($banks) ? reset($banks) : null;

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
                'expeditor_bank_id'   => 1,
                'expeditor_bank'      => $bank['name'] ?? null,
                'expeditor_iban'      => $bank['iban'] ?? null,
                'expeditor_bic'       => $bank['bic'] ?? null,
                'carrier_company_id'   => $carrier->id,
                'driver_id'           => $driver->id,
                'truck_id'            => $truck->id,
                'trailer_id'          => $trailer->id,
                'start_date'          => $startDate->toDateString(),
                'end_date'            => $endDate->toDateString(),
                'currency'            => 'EUR',
                'status'              => TripStatus::PLANNED,
                'cont_nr'             => 'CONT-' . strtoupper(substr(uniqid(), -8)),
                'seal_nr'             => 'SEAL-' . rand(1000, 9999),
                'customs'             => (bool) rand(0, 1),
                'customs_address'     => 'Customs point, Riga',
                'notes'               => 'Test trip (TestTripsFullSeeder)',
            ]);

            $steps = [];
            $stepTypes = ['loading', 'loading', 'unloading', 'unloading'];
            foreach ($stepTypes as $order => $type) {
                $countryId = $countryIdsWithCities[array_rand($countryIdsWithCities)];
                $cities = getCitiesByCountryId($countryId);
                $cityId = $cities ? (int) array_key_first($cities) : 1;
                $steps[] = TripStep::create([
                    'trip_id'    => $trip->id,
                    'type'       => $type,
                    'country_id' => $countryId,
                    'city_id'    => $cityId,
                    'address'    => ($type === 'loading' ? 'Loading' : 'Unloading') . ' point ' . ($order + 1),
                    'date'       => $startDate->copy()->addDays((int) ($order / 2))->toDateString(),
                    'time'       => $order % 2 === 0 ? '08:00' : '16:00',
                    'order'      => $order + 1,
                ]);
            }

            $shippers = $clients->take(3)->values();
            $consignees = $clients->skip(3)->take(3)->values();
            $customers = $clients->skip(6)->take(3)->values();

            $tripDocumentTypes = [TripDocumentType::CMR, TripDocumentType::TransportOrder];
            foreach ($tripDocumentTypes as $idx => $type) {
                TripDocument::create([
                    'trip_id'     => $trip->id,
                    'type'        => $type,
                    'name'        => 'Test ' . $type->value . ' ' . $trip->id,
                    'file_path'   => $this->placeholderPath,
                    'uploaded_at' => now(),
                ]);
            }

            $stepDocTypes = StepDocumentType::cases();
            $tripCargos = [];

            for ($c = 0; $c < 3; $c++) {
                $customer = $customers[$c] ?? $clients[$c];
                $shipper = $shippers[$c] ?? $clients[$c];
                $consignee = $consignees[$c] ?? $clients[$c + 3];
                $price = (float) rand(500, 1500);
                $taxPercent = (float) [5, 10, 21][array_rand([5, 10, 21])];
                $tax = CalculateTax::calculate($price, $taxPercent);
                $hasDelay = (bool) rand(0, 1);
                $delayDays = $hasDelay ? rand(1, 3) : null;
                $delayAmount = $hasDelay ? (float) rand(50, 200) : null;

                $cargo = TripCargo::create([
                    'trip_id'                     => $trip->id,
                    'customer_id'                 => $customer->id,
                    'shipper_id'                  => $shipper->id,
                    'consignee_id'                => $consignee->id,
                    'price'                       => $price,
                    'tax_percent'                 => $taxPercent,
                    'total_tax_amount'            => $tax['tax_amount'],
                    'price_with_tax'              => $tax['price_with_tax'],
                    'currency'                    => 'EUR',
                    'payment_terms'               => $endDate->copy()->addDays(14),
                    'payment_days'                => 14,
                    'payer_type_id'               => 1,
                    'commercial_invoice_nr'       => 'INV-T' . $trip->id . '-C' . ($c + 1),
                    'commercial_invoice_amount'   => $price + $tax['tax_amount'],
                    'has_delay'                   => $hasDelay,
                    'delay_days'                  => $delayDays,
                    'delay_amount'                => $delayAmount,
                ]);
                $tripCargos[] = $cargo;

                $cargo->steps()->attach([
                    $steps[0]->id => ['role' => 'loading'],
                    $steps[1]->id => ['role' => 'loading'],
                    $steps[2]->id => ['role' => 'unloading'],
                    $steps[3]->id => ['role' => 'unloading'],
                ]);

                for ($item = 1; $item <= 3; $item++) {
                    $itemPrice = $price / 3;
                    $itemTax = CalculateTax::calculate($itemPrice, $taxPercent);
                    TripCargoItem::create([
                        'trip_cargo_id'   => $cargo->id,
                        'description'     => "Test item {$item} cargo " . ($c + 1),
                        'packages'        => rand(5, 20),
                        'pallets'         => rand(0, 5),
                        'units'           => rand(10, 100),
                        'net_weight'      => (float) rand(100, 500),
                        'gross_weight'    => (float) rand(110, 550),
                        'tonnes'          => round(rand(100, 500) / 1000, 2),
                        'volume'          => (float) rand(1, 15),
                        'loading_meters' => (float) rand(1, 5),
                        'customs_code'   => 'TEST' . $c . $item,
                        'stackable'      => (bool) rand(0, 1),
                        'instructions'   => 'Handle with care',
                        'remarks'        => 'Test',
                        'price'           => $itemTax['price'] ?? $itemPrice,
                        'tax_percent'     => $taxPercent,
                        'tax_amount'      => $itemTax['tax_amount'] ?? 0,
                        'price_with_tax'  => $itemTax['price_with_tax'] ?? $itemPrice,
                    ]);
                }

                foreach (array_slice($stepDocTypes, 0, 2) as $type) {
                    TripStepDocument::create([
                        'trip_step_id'       => $steps[0]->id,
                        'trip_id'            => $trip->id,
                        'cargo_id'           => $cargo->id,
                        'uploader_driver_id' => $driver->id,
                        'type'               => $type,
                        'file_path'          => $this->placeholderPath,
                        'original_name'      => 'driver_doc_' . $type->value . '.pdf',
                        'comment'            => 'Test doc (driver)',
                    ]);
                }

                $expenseCategories = TripExpenseCategory::cases();
                foreach (array_slice($expenseCategories, 0, 2) as $cat) {
                    TripExpense::create([
                        'trip_id'       => $trip->id,
                        'trip_cargo_id'=> $cargo->id,
                        'category'     => $cat,
                        'description'  => 'Test expense ' . $cat->value . ' cargo ' . ($c + 1),
                        'amount'       => (float) rand(20, 150),
                        'currency'     => 'EUR',
                        'expense_date' => $startDate->toDateString(),
                        'file_path'    => $this->placeholderPath,
                    ]);
                }
            }

            foreach (array_slice($stepDocTypes, 2, 2) as $type) {
                TripStepDocument::create([
                    'trip_step_id'       => $steps[1]->id,
                    'trip_id'            => $trip->id,
                    'cargo_id'           => null,
                    'uploader_driver_id' => $driver->id,
                    'type'               => $type,
                    'file_path'          => $this->placeholderPath,
                    'original_name'      => 'trip_step_doc_' . $type->value . '.pdf',
                    'comment'            => 'Test step doc',
                ]);
            }
        });
    }
}
