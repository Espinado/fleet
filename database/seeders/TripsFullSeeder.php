<?php

namespace Database\Seeders;

use App\Enums\StepDocumentType;
use App\Enums\TripExpenseCategory;
use App\Enums\TripStatus;
use App\Enums\TripStepStatus;
use App\Helpers\CalculateTax;
use App\Http\Controllers\CmrController;
use App\Models\Client;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripCargoItem;
use App\Models\TripExpense;
use App\Models\TripStatusHistory;
use App\Models\TripStep;
use App\Models\TripStepDocument;
use App\Models\Trailer;
use App\Models\Truck;
use App\Models\TruckOdometerEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TripsFullSeeder extends Seeder
{
    private array $countryPool = [16, 17, 13, 21, 8]; // LV, LT, HU, EE, DE

    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            auth()->login($admin);
        }

        $carrierCompanies = Company::where('type', 'carrier')
            ->where(function ($q) {
                $q->where('is_third_party', false)->orWhereNull('is_third_party');
            })
            ->orderBy('id')->get();
        $expeditorCompany = Company::where('type', 'expeditor')->first();
        $clients = Client::all();
        if ($clients->count() < 9) {
            $this->command->warn('Need at least 9 clients. Run ClientsTableSeeder first.');
            return;
        }

        $this->seedCarrierTrips($carrierCompanies, $clients);
        $this->seedPerClientTrips($carrierCompanies, $clients);
        $this->seedThirdPartyTrips($expeditorCompany, $clients);

        $this->command->info('TripsFullSeeder finished.');
    }

    private function seedCarrierTrips($carrierCompanies, $clients): void
    {
        $baseDate = Carbon::now()->subDays(60);

        foreach ($carrierCompanies as $company) {
            $trucks = Truck::where('company_id', $company->id)->get();
            $drivers = Driver::where('company_id', $company->id)->get();
            $trailers = Trailer::where('company_id', $company->id)->get();
            if ($trucks->isEmpty() || $drivers->isEmpty() || $trailers->isEmpty()) {
                continue;
            }

            $d = 0;
            $t = 0;
            $tr = 0;
            foreach ($trucks as $truck) {
                for ($tripNum = 0; $tripNum < 4; $tripNum++) {
                    $driver = $drivers[$d % $drivers->count()];
                    $trailer = $trailers[$tr % $trailers->count()];
                    $d++;
                    $tr++;
                    $startDate = $baseDate->copy()->addDays($t * 3);
                    $endDate = $startDate->copy()->addDays(2);
                    $t++;

                    // Часть рейсов — planned / in_progress, часть — completed
                    $status = match ($tripNum) {
                        0 => TripStatus::PLANNED,
                        1 => TripStatus::IN_PROGRESS,
                        default => TripStatus::COMPLETED,
                    };

                    $trip = $this->createTrip(
                        $company,
                        $company,
                        $driver->id,
                        $truck->id,
                        $trailer->id,
                        $startDate,
                        $endDate,
                        $status
                    );

                    $steps = $this->createStepsAndCargosForTrip($trip, $clients, 2, 2, $startDate);
                    $this->generateCmrForTrip($trip);

                    if ($status === TripStatus::COMPLETED) {
                        $this->completeTripWithOdoAndDocs($trip, $steps);
                        $this->addExpensesForTrip($trip);
                        $this->generateInvoiceForTrip($trip);
                    }
                }
            }
        }
    }

    private function seedPerClientTrips($carrierCompanies, $clients): void
    {
        $baseDate = Carbon::now()->subDays(30);
        $company = $carrierCompanies->first();
        $trucks = Truck::where('company_id', $company->id)->get();
        $drivers = Driver::where('company_id', $company->id)->get();
        $trailers = Trailer::where('company_id', $company->id)->get();
        if ($trucks->isEmpty() || $drivers->isEmpty() || $trailers->isEmpty()) {
            return;
        }

        $tripIndex = 0;
        foreach ($clients as $client) {
            $truck = $trucks[$tripIndex % $trucks->count()];
            $driver = $drivers[$tripIndex % $drivers->count()];
            $trailer = $trailers[$tripIndex % $trailers->count()];
            $tripIndex++;

            $startDate = $baseDate->copy()->addDays($tripIndex * 2);
            $endDate = $startDate->copy()->addDays(1);

            $trip = $this->createTrip($company, $company, $driver->id, $truck->id, $trailer->id, $startDate, $endDate, TripStatus::COMPLETED);

            $others = $clients->where('id', '!=', $client->id)->random(min(4, $clients->count() - 1))->values();
            $steps = $this->createStepsForClientTrip($trip, $client, $others);
            $this->generateCmrForTrip($trip);
            $this->completeTripWithOdoAndDocs($trip, $steps);
            $this->addExpensesForTrip($trip);
            $this->generateInvoiceForTrip($trip);
        }
    }

    private function seedThirdPartyTrips($expeditorCompany, $clients): void
    {
        if (!$expeditorCompany) {
            return;
        }

        $baseDate = Carbon::now()->subDays(45);
        $thirdPartyNames = ['Vēja Trans', 'Zvaigzne Logistika', 'Baltijas Kravas', 'Nordic Sub', 'Delta Pārvadājumi', 'Europa Cargo'];
        for ($i = 0; $i < 6; $i++) {
            $thirdParty = Company::create([
                'slug' => '3p-' . ($i + 1),
                'name' => $thirdPartyNames[$i],
                'type' => 'carrier',
                'is_third_party' => true,
                'is_system' => false,
                'is_active' => true,
            ]);

            $truck = Truck::create([
                'brand' => Arr::random(['Volvo', 'Scania', 'MAN', 'DAF']),
                'plate' => strtoupper(chr(65 + random_int(0, 25)) . chr(65 + random_int(0, 25))) . '-' . rand(1000, 9999),
                'company_id' => $thirdParty->id,
                'status' => 1,
                'is_active' => true,
            ]);

            $trailer = Trailer::create([
                'brand' => Arr::random(['Krone', 'Schmitz', 'Kögel']),
                'plate' => strtoupper(chr(65 + random_int(0, 25))) . '-' . rand(1000, 9999),
                'company_id' => $thirdParty->id,
                'status' => 1,
                'is_active' => true,
            ]);

            $startDate = $baseDate->copy()->addDays($i * 5);
            $endDate = $startDate->copy()->addDays(2);

            // Водителей 3-й стороны в БД не храним — driver_id = null
            $trip = $this->createTrip($expeditorCompany, $thirdParty, null, $truck->id, $trailer->id, $startDate, $endDate, TripStatus::COMPLETED);

            $steps = $this->createStepsAndCargosForTrip($trip, $clients, 2, 2, $startDate);

            $freightTotal = (float) $trip->cargos->sum('price_with_tax');
            $subcontractorAmount = round($freightTotal * (0.70 + (rand(0, 25) / 100)), 2);
            TripExpense::create([
                'trip_id' => $trip->id,
                'category' => TripExpenseCategory::SUBCONTRACTOR,
                'amount' => $subcontractorAmount,
                'currency' => 'EUR',
                'expense_date' => $endDate,
                'supplier_company_id' => $thirdParty->id,
            ]);
            // Ещё 4–5 расходов по рейсу (итого 5–6 с SUBCONTRACTOR)
            $extraCategories = [TripExpenseCategory::TOLL, TripExpenseCategory::PARKING, TripExpenseCategory::FUEL, TripExpenseCategory::OTHER];
            foreach (collect($extraCategories)->shuffle()->take(rand(4, 5)) as $cat) {
                TripExpense::create([
                    'trip_id' => $trip->id,
                    'category' => $cat,
                    'amount' => $cat === TripExpenseCategory::FUEL ? rand(80, 200) : rand(10, 80),
                    'currency' => 'EUR',
                    'expense_date' => $startDate->copy()->addHours(rand(1, 24)),
                ]);
            }

            $this->generateInvoiceAndOrderForThirdPartyTrip($trip);
        }
    }

    private function createTrip(Company $expeditor, Company $carrier, ?int $driverId, int $truckId, int $trailerId, Carbon $startDate, Carbon $endDate, TripStatus $status): Trip
    {
        $banks = $expeditor->banks_json ?? [];
        $bank = null;
        $bankId = null;
        if (is_array($banks) && !empty($banks)) {
            $bankId = key($banks);
            $bank = current($banks);
        }

        $trip = Trip::withoutGlobalScopes()->create([
            'expeditor_id' => $expeditor->id,
            'expeditor_name' => $expeditor->name,
            'expeditor_reg_nr' => $expeditor->reg_nr,
            'expeditor_country' => $expeditor->country,
            'expeditor_city' => $expeditor->city,
            'expeditor_address' => $expeditor->address ?? '',
            'expeditor_post_code' => $expeditor->post_code ?? '',
            'expeditor_email' => $expeditor->email ?? '',
            'expeditor_phone' => $expeditor->phone ?? '',
            'expeditor_bank_id' => $bankId,
            'expeditor_bank' => is_array($bank) ? ($bank['name'] ?? null) : null,
            'expeditor_iban' => is_array($bank) ? ($bank['iban'] ?? null) : null,
            'expeditor_bic' => is_array($bank) ? ($bank['bic'] ?? null) : null,
            'carrier_company_id' => $carrier->id,
            'driver_id' => $driverId,
            'truck_id' => $truckId,
            'trailer_id' => $trailerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'currency' => 'EUR',
        ]);

        if ($status === TripStatus::COMPLETED && $driverId !== null) {
            TripStatusHistory::create([
                'trip_id' => $trip->id,
                'driver_id' => $driverId,
                'status' => $status->value,
                'time' => $endDate,
            ]);
        }

        return $trip;
    }

    private function createStepsAndCargosForTrip(Trip $trip, $clients, int $numCargos, int $itemsPerCargo, Carbon $startDate): \Illuminate\Support\Collection
    {
        $steps = collect();
        $order = 0;

        for ($c = 0; $c < $numCargos; $c++) {
            $loadCountryId = Arr::random($this->countryPool);
            $loadCityId = $this->randomCityId($loadCountryId);
            $unloadCountryId = Arr::random($this->countryPool);
            $unloadCityId = $this->randomCityId($unloadCountryId);

            $stepLoad = TripStep::create([
                'trip_id' => $trip->id,
                'type' => 'loading',
                'country_id' => $loadCountryId,
                'city_id' => $loadCityId,
                'address' => 'Warehouse ' . ($c + 1),
                'date' => $startDate->copy()->addHours($c * 4),
                'time' => '08:00',
                'order' => $order++,
                'status' => TripStepStatus::NOT_STARTED,
            ]);
            $steps->push($stepLoad);

            $stepUnload = TripStep::create([
                'trip_id' => $trip->id,
                'type' => 'unloading',
                'country_id' => $unloadCountryId,
                'city_id' => $unloadCityId,
                'address' => 'Terminal ' . ($c + 1),
                'date' => $startDate->copy()->addDay()->addHours($c * 4),
                'time' => '14:00',
                'order' => $order++,
                'status' => TripStepStatus::NOT_STARTED,
            ]);
            $steps->push($stepUnload);

            $shipper = $clients->random();
            $consignee = $clients->where('id', '!=', $shipper->id)->random();
            $customer = $clients->whereNotIn('id', [$shipper->id, $consignee->id])->random();

            $taxRate = (float) Arr::random([5, 12, 21]);
            $items = [];
            for ($i = 0; $i < $itemsPerCargo; $i++) {
                $priceWithTax = round(rand(200, 800) + (rand(0, 99) / 100), 2);
                $items[] = [
                    'description' => 'Goods ' . ($i + 1),
                    'packages' => rand(2, 10),
                    'pallets' => rand(0, 4),
                    'gross_weight' => rand(100, 500),
                    'net_weight' => rand(80, 450),
                    'volume' => rand(1, 8),
                    'price_with_tax' => $priceWithTax,
                    'tax_percent' => $taxRate,
                ];
            }
            $calc = CalculateTax::forItems($items);

            $cargo = TripCargo::create([
                'trip_id' => $trip->id,
                'customer_id' => $customer->id,
                'shipper_id' => $shipper->id,
                'consignee_id' => $consignee->id,
                'price' => $calc['subtotal'],
                'tax_percent' => $taxRate,
                'total_tax_amount' => $calc['total_tax_amount'],
                'price_with_tax' => $calc['price_with_tax'],
                'currency' => 'EUR',
                'payment_days' => 30,
                'payer_type_id' => Arr::random([1, 2, 3]),
            ]);

            foreach ($calc['items'] as $item) {
                TripCargoItem::create([
                    'trip_cargo_id' => $cargo->id,
                    'description' => $item['description'],
                    'packages' => $item['packages'] ?? 0,
                    'pallets' => $item['pallets'] ?? 0,
                    'gross_weight' => $item['gross_weight'] ?? 0,
                    'net_weight' => $item['net_weight'] ?? 0,
                    'volume' => $item['volume'] ?? 0,
                    'price' => $item['price'],
                    'tax_percent' => $item['tax_percent'],
                    'tax_amount' => $item['tax_amount'],
                    'price_with_tax' => $item['price_with_tax'],
                ]);
            }

            DB::table('trip_cargo_step')->insert([
                ['trip_step_id' => $stepLoad->id, 'trip_cargo_id' => $cargo->id, 'role' => 'loading'],
                ['trip_step_id' => $stepUnload->id, 'trip_cargo_id' => $cargo->id, 'role' => 'unloading'],
            ]);
        }

        return $steps;
    }

    private function createStepsForClientTrip(Trip $trip, Client $mainClient, $otherClients): \Illuminate\Support\Collection
    {
        $steps = collect();
        $order = 0;
        $startDate = $trip->start_date;

        $cargosData = [
            ['shipper' => $mainClient, 'consignee' => $otherClients[0] ?? $mainClient, 'customer' => $otherClients[1] ?? $mainClient],
            ['shipper' => $otherClients[0] ?? $mainClient, 'consignee' => $mainClient, 'customer' => $otherClients[1] ?? $mainClient],
            ['shipper' => $otherClients[1] ?? $mainClient, 'consignee' => $otherClients[0] ?? $mainClient, 'customer' => $mainClient],
        ];

        foreach ($cargosData as $c => $roles) {
            $loadCountryId = Arr::random($this->countryPool);
            $loadCityId = $this->randomCityId($loadCountryId);
            $unloadCountryId = Arr::random($this->countryPool);
            $unloadCityId = $this->randomCityId($unloadCountryId);

            $stepLoad = TripStep::create([
                'trip_id' => $trip->id,
                'type' => 'loading',
                'country_id' => $loadCountryId,
                'city_id' => $loadCityId,
                'address' => 'Loading ' . ($c + 1),
                'date' => $startDate->copy()->addHours($c * 3),
                'time' => '09:00',
                'order' => $order++,
                'status' => TripStepStatus::NOT_STARTED,
            ]);
            $steps->push($stepLoad);

            $stepUnload = TripStep::create([
                'trip_id' => $trip->id,
                'type' => 'unloading',
                'country_id' => $unloadCountryId,
                'city_id' => $unloadCityId,
                'address' => 'Unload ' . ($c + 1),
                'date' => $startDate->copy()->addDay()->addHours($c * 3),
                'time' => '15:00',
                'order' => $order++,
                'status' => TripStepStatus::NOT_STARTED,
            ]);
            $steps->push($stepUnload);

            $taxRate = (float) Arr::random([5, 12, 21]);
            $items = [];
            for ($i = 0; $i < 2; $i++) {
                $priceWithTax = round(rand(150, 600) + (rand(0, 99) / 100), 2);
                $items[] = [
                    'description' => 'Item ' . ($i + 1),
                    'packages' => rand(1, 6),
                    'pallets' => rand(0, 2),
                    'gross_weight' => rand(80, 400),
                    'net_weight' => rand(70, 350),
                    'volume' => rand(1, 5),
                    'price_with_tax' => $priceWithTax,
                    'tax_percent' => $taxRate,
                ];
            }
            $calc = CalculateTax::forItems($items);

            $cargo = TripCargo::create([
                'trip_id' => $trip->id,
                'customer_id' => $roles['customer']->id,
                'shipper_id' => $roles['shipper']->id,
                'consignee_id' => $roles['consignee']->id,
                'price' => $calc['subtotal'],
                'tax_percent' => $taxRate,
                'total_tax_amount' => $calc['total_tax_amount'],
                'price_with_tax' => $calc['price_with_tax'],
                'currency' => 'EUR',
                'payment_days' => 30,
                'payer_type_id' => Arr::random([1, 2, 3]),
            ]);

            foreach ($calc['items'] as $item) {
                TripCargoItem::create([
                    'trip_cargo_id' => $cargo->id,
                    'description' => $item['description'],
                    'packages' => $item['packages'] ?? 0,
                    'pallets' => $item['pallets'] ?? 0,
                    'gross_weight' => $item['gross_weight'] ?? 0,
                    'net_weight' => $item['net_weight'] ?? 0,
                    'volume' => $item['volume'] ?? 0,
                    'price' => $item['price'],
                    'tax_percent' => $item['tax_percent'],
                    'tax_amount' => $item['tax_amount'],
                    'price_with_tax' => $item['price_with_tax'],
                ]);
            }

            DB::table('trip_cargo_step')->insert([
                ['trip_step_id' => $stepLoad->id, 'trip_cargo_id' => $cargo->id, 'role' => 'loading'],
                ['trip_step_id' => $stepUnload->id, 'trip_cargo_id' => $cargo->id, 'role' => 'unloading'],
            ]);
        }

        return $steps;
    }

    private function completeTripWithOdoAndDocs(Trip $trip, $steps): void
    {
        $driverId = $trip->driver_id;
        $truckId = $trip->truck_id;
        $baseOdo = rand(50000, 200000);
        $occurred = $trip->start_date->copy();

        foreach ($steps as $idx => $step) {
            $step->update([
                'status' => TripStepStatus::COMPLETED,
                'started_at' => $occurred,
                'completed_at' => $occurred->copy()->addMinutes(30),
            ]);
            $baseOdo += rand(50, 200);
            TruckOdometerEvent::create([
                'truck_id' => $truckId,
                'driver_id' => $driverId,
                'trip_id' => $trip->id,
                'trip_step_id' => $step->id,
                'type' => TruckOdometerEvent::TYPE_STEP,
                'step_status' => TripStepStatus::COMPLETED->value,
                'odometer_km' => $baseOdo,
                'occurred_at' => $occurred,
                'source' => TruckOdometerEvent::SOURCE_MANUAL,
            ]);
            $occurred = $occurred->addHours(2);
        }

        // 5–6 документов на рейс: по одному каждого типа из StepDocumentType (JPG для просмотра в браузере)
        $docTypes = StepDocumentType::cases();
        $docCount = rand(5, 6);
        $stepsList = $steps->values()->all();
        $jpegPlaceholder = $this->getMinimalJpegBytes();
        for ($d = 0; $d < $docCount; $d++) {
            $docType = $docTypes[$d % count($docTypes)];
            $step = $stepsList[$d % count($stepsList)];
            $path = "seeder_docs/trip_{$trip->id}_step_{$step->id}_{$docType->value}.jpg";
            Storage::disk('public')->put($path, $jpegPlaceholder);
            TripStepDocument::create([
                'trip_step_id' => $step->id,
                'trip_id' => $trip->id,
                'type' => $docType,
                'file_path' => $path,
            ]);
        }

        $departOdo = $baseOdo - ($steps->count() * 150);
        $returnOdo = $baseOdo + rand(80, 150);

        TruckOdometerEvent::create([
            'truck_id' => $truckId,
            'driver_id' => $driverId,
            'trip_id' => $trip->id,
            'type' => TruckOdometerEvent::TYPE_DEPARTURE,
            'odometer_km' => $departOdo,
            'occurred_at' => $trip->start_date,
            'source' => TruckOdometerEvent::SOURCE_MANUAL,
        ]);
        TruckOdometerEvent::create([
            'truck_id' => $truckId,
            'driver_id' => $driverId,
            'trip_id' => $trip->id,
            'type' => TruckOdometerEvent::TYPE_RETURN,
            'odometer_km' => $returnOdo,
            'occurred_at' => $trip->end_date,
            'source' => TruckOdometerEvent::SOURCE_MANUAL,
        ]);

        $trip->update([
            'odo_start_km' => $departOdo,
            'odo_end_km' => $returnOdo,
            'started_at' => $trip->start_date,
            'ended_at' => $trip->end_date,
        ]);
    }

    private function addExpensesForTrip(Trip $trip): void
    {
        // 5–6 расходов на рейс, категории из enum (кроме SUBCONTRACTOR для своих рейсов)
        $categories = [
            TripExpenseCategory::FUEL,
            TripExpenseCategory::ADBLUE,
            TripExpenseCategory::WASHER_FLUID,
            TripExpenseCategory::CAR_WASH,
            TripExpenseCategory::TOLL,
            TripExpenseCategory::PARKING,
            TripExpenseCategory::FINE,
            TripExpenseCategory::PERMIT,
            TripExpenseCategory::REPAIR,
            TripExpenseCategory::HOTEL,
            TripExpenseCategory::OTHER,
        ];
        $count = rand(5, 6);
        $selected = collect($categories)->shuffle()->take($count)->values()->all();
        $odo = (float) ($trip->odo_start_km ?? rand(50000, 150000));
        $date = $trip->start_date->copy();

        foreach ($selected as $cat) {
            $odo += rand(30, 120);
            $amount = match ($cat) {
                TripExpenseCategory::FUEL => rand(80, 280),
                TripExpenseCategory::ADBLUE => rand(20, 80),
                TripExpenseCategory::TOLL => rand(15, 120),
                TripExpenseCategory::PARKING => rand(5, 35),
                TripExpenseCategory::HOTEL => rand(40, 120),
                TripExpenseCategory::REPAIR, TripExpenseCategory::SPARE_PARTS => rand(50, 300),
                default => rand(5, 60),
            };
            $liters = null;
            if ($cat === TripExpenseCategory::FUEL) {
                $liters = rand(30, 90);
            } elseif ($cat === TripExpenseCategory::ADBLUE) {
                $liters = rand(5, 25);
            }
            TripExpense::create([
                'trip_id' => $trip->id,
                'category' => $cat,
                'amount' => $amount,
                'currency' => 'EUR',
                'expense_date' => $date,
                'odometer_km' => $odo,
                'liters' => $liters,
            ]);
            $date = $date->addHours(6);
        }
    }

    /** CMR генерируем на все рейсы (и planned, и in_progress, и completed). */
    private function generateCmrForTrip(Trip $trip): void
    {
        $controller = app(CmrController::class);
        foreach ($trip->cargos as $cargo) {
            $cargo->update([
                'cmr_nr' => 'CMR-' . $trip->id . '-' . $cargo->id,
                'order_nr' => 'ORD-' . $trip->id . '-' . $cargo->id,
                'payer_type_id' => $cargo->payer_type_id ?: 1,
            ]);
            try {
                $controller->generateAndSave($cargo->fresh());
            } catch (\Throwable $e) {
                $this->command->warn('CMR gen failed trip ' . $trip->id . ' cargo ' . $cargo->id . ': ' . $e->getMessage());
            }
        }
    }

    /** Инвойс — только по завершённым рейсам. */
    private function generateInvoiceForTrip(Trip $trip): void
    {
        $controller = app(CmrController::class);
        foreach ($trip->cargos as $cargo) {
            $cargo->update([
                'inv_nr' => 'INV-' . $trip->id . '-' . $cargo->id,
                'payer_type_id' => $cargo->payer_type_id ?: 1,
            ]);
            try {
                $controller->generateInvoiceAndSave($cargo->fresh());
            } catch (\Throwable $e) {
                $this->command->warn('Invoice gen failed trip ' . $trip->id . ' cargo ' . $cargo->id . ': ' . $e->getMessage());
            }
        }
    }

    private function generateInvoiceAndOrderForThirdPartyTrip(Trip $trip): void
    {
        $controller = app(CmrController::class);
        foreach ($trip->cargos as $cargo) {
            $cargo->update([
                'order_nr' => 'ORD-3P-' . $trip->id . '-' . $cargo->id,
                'inv_nr' => 'INV-3P-' . $trip->id . '-' . $cargo->id,
                'payer_type_id' => $cargo->payer_type_id ?: 1,
            ]);
            try {
                $controller->generateTransportOrder($cargo->fresh());
                $controller->generateInvoiceAndSave($cargo->fresh());
            } catch (\Throwable $e) {
                $this->command->warn('Order/Invoice gen failed trip ' . $trip->id . ': ' . $e->getMessage());
            }
        }
    }

    private function randomCityId(int $countryId): int
    {
        $iso = config("countries.{$countryId}.iso") ?? 'lv';
        $cities = config("cities.{$iso}") ?? [1 => ['name' => 'Riga']];
        $ids = array_keys($cities);
        return (int) $ids[array_rand($ids)];
    }

    /** Минимальный валидный JPEG (1×1 px) для просмотра документов в сидере. */
    private function getMinimalJpegBytes(): string
    {
        if (function_exists('imagecreate') && function_exists('imagejpeg')) {
            $img = imagecreate(1, 1);
            if ($img !== false) {
                imagecolorallocate($img, 245, 245, 245);
                ob_start();
                imagejpeg($img, null, 80);
                $bytes = ob_get_clean();
                imagedestroy($img);
                if ($bytes !== false && $bytes !== '') {
                    return $bytes;
                }
            }
        }
        // Fallback: минимальный валидный JPEG (1×1 серый пиксель)
        return base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBEQACEQADAP/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAT8A0n//2Q==');
    }
}
