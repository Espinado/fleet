<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\{Trip, Driver, Truck, Trailer, Client};
use App\Enums\TripStatus;
use Illuminate\Support\Facades\Log;

class CreateTrip extends Component
{
    public $expeditorId, $driverId, $truckId, $trailerId, $clientId;
    public $selectedClient = null;
    public $origin_country, $origin_address;
public $destination_country, $destination_address;

    public $route_from, $route_to, $start_date, $end_date, $cargo, $price, $currency = 'EUR';
    public $status = TripStatus::PLANNED;

    public $drivers = [];
    public $trucks = [];
    public $trailers = [];

    protected $rules = [
        'expeditorId' => 'required|integer',
        'driverId'    => 'required|integer|exists:drivers,id',
        'truckId'     => 'required|integer|exists:trucks,id',
        'trailerId'   => 'required|integer|exists:trailers,id',
        'clientId'    => 'required|integer|exists:clients,id',
       
        'start_date'  => 'required|date',
        'end_date'    => 'required|date|after_or_equal:start_date',
        'cargo'       => 'required|string|max:255',
        'price'       => 'required|numeric|min:0',
        'currency'    => 'required|string|max:10',
        'status'      => 'required',
        'origin_country'       => 'required|integer',
        'origin_address'       => 'required|string|max:255',
        'destination_country'  => 'required|integer',
        'destination_address'  => 'required|string|max:255',
    ];

    public function updatedExpeditorId($value)
    {
        if (!$value) {
            $this->drivers = $this->trucks = $this->trailers = [];
            $this->driverId = $this->truckId = $this->trailerId = null;
            return;
        }

        $this->drivers  = Driver::where('company', $value)->orderBy('first_name')->get();
        $this->trucks   = Truck::where('company', $value)->orderBy('plate')->get();
        $this->trailers = Trailer::where('company', $value)->orderBy('plate')->get();

        Log::info("✅ Loaded company data for expeditor {$value}", [
            'drivers' => $this->drivers->count(),
            'trucks'  => $this->trucks->count(),
            'trailers'=> $this->trailers->count(),
        ]);

        $this->driverId = $this->truckId = $this->trailerId = null;
    }

    public function updatedClientId($value)
    {
        $this->selectedClient = $value ? Client::find($value) : null;
    }

    public function save()
    {
        $this->price = str_replace(',', '.', $this->price);
        try {
            $this->validate();

            $company = config('companies.' . $this->expeditorId);

            Trip::create([
                'expeditor_id'        => $this->expeditorId,
                'expeditor_name'      => $company['name'] ?? null,
                'expeditor_reg_nr'    => $company['reg_nr'] ?? null,
                'expeditor_country'   => $company['country'] ?? null,
                'expeditor_city'      => $company['city'] ?? null,
                'expeditor_address'   => $company['address'] ?? null,
                'expeditor_post_code' => $company['post_code'] ?? null,
                'expeditor_email'     => $company['email'] ?? null,
                'expeditor_phone'     => $company['phone'] ?? null,

                'driver_id'  => $this->driverId,
                'truck_id'   => $this->truckId,
                'trailer_id' => $this->trailerId,
                'client_id'  => $this->clientId,
                 'origin_country'      => $this->origin_country,
                'origin_address'      => $this->origin_address,
                'destination_country' => $this->destination_country,
                'destination_address' => $this->destination_address,

               
                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,
                'cargo'      => $this->cargo,
                'price'      => $this->price,
                'currency'   => $this->currency,
                'status'     => TripStatus::PLANNED, // ✅ Enum автоматически сконвертируется
            ]);

            Log::info("✅ Trip successfully created", ['expeditor' => $this->expeditorId]);

            session()->flash('success', 'Trip created successfully!');
            return redirect()->route('trips.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation failed when creating trip', ['errors' => $e->errors()]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('💥 Failed to create trip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => [
                    'expeditorId' => $this->expeditorId,
                    'driverId' => $this->driverId,
                    'truckId' => $this->truckId,
                    'trailerId' => $this->trailerId,
                    'clientId' => $this->clientId,
                ],
            ]);

            session()->flash('error', 'An unexpected error occurred while saving the trip.');
        }
    }

    public function render()
    {
        $clients = Client::orderBy('company_name')->get();

        return view('livewire.trips.create-trip', compact('clients'))
            ->layout('layouts.app')
            ->title('Create Trip');
    }
}
