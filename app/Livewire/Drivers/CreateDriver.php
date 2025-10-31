<?php

namespace App\Livewire\Drivers;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Driver;
use App\Enums\DriverStatus;
use Illuminate\Support\Facades\Log;

class CreateDriver extends Component
{
    use WithFileUploads;

    // Личные данные
    public $first_name, $last_name, $pers_code, $citizenship_id;
    public $phone, $email, $company;
    public $status = DriverStatus::ON_WORK->value;
    public $is_active = true;

    // Адрес (Declared)
    public ?int $declared_country_id = null;
    public ?int $declared_city_id = null;
    public $declared_street, $declared_building, $declared_room, $declared_postcode;

    // Адрес (Actual)
    public ?int $actual_country_id = null;
    public ?int $actual_city_id = null;
    public $actual_street, $actual_building, $actual_room, $actual_postcode;

    // Документы
    public $license_number, $license_issued, $license_end;
    public $code95_issued, $code95_end;
    public $permit_issued, $permit_expired;
    public $medical_issued, $medical_expired;
    public $medical_exam_passed, $medical_exam_expired;
    public $declaration_issued, $declaration_expired;

    // Фото
    public $photo, $license_photo, $medical_certificate_photo;

    public $successMessage;

    // ================= RULES =================
    protected function rules(): array
    {
        return [
            // Основное
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'pers_code'   => 'required|string|unique:drivers,pers_code',
            'citizenship_id' => 'required|integer',
            'phone'       => 'required|string|max:20',
            'email'       => 'required|email|max:255',
            'company'     => 'required|integer',

            // Declared address
            'declared_country_id' => 'required|integer',
            'declared_city_id'    => 'required|integer',
            'declared_street'     => 'required|string|max:255',
            'declared_building'   => 'required|string|max:50',
            'declared_room'       => 'nullable|string|max:20',
            'declared_postcode'   => 'required|string|max:20',

            // Actual address
            'actual_country_id' => 'required|integer',
            'actual_city_id'    => 'required|integer',
            'actual_street'     => 'required|string|max:255',
            'actual_building'   => 'required|string|max:50',
            'actual_room'       => 'nullable|string|max:20',

            // Документы
            'license_number'       => 'required|string|unique:drivers,license_number',
            'license_issued'       => 'required|date',
            'license_end'          => 'required|date|after:license_issued',
            'code95_issued'        => 'required|date',
            'code95_end'           => 'required|date|after:code95_issued',
            'permit_issued'        => 'required|date',
            'permit_expired'       => 'required|date|after:permit_issued',
            'medical_issued'       => 'required|date',
            'medical_expired'      => 'required|date|after:medical_issued',
            'medical_exam_passed'  => 'required|date',
            'medical_exam_expired' => 'required|date|after:medical_exam_passed',
            'declaration_issued'   => 'required|date',
            'declaration_expired'  => 'required|date|after:declaration_issued',

            // Фото
            'photo'                     => 'nullable|image|max:2048',
            'license_photo'             => 'nullable|image|max:2048',
            'medical_certificate_photo' => 'nullable|image|max:2048',
        ];
    }

    // ================= SAVE =================
    public function save()
{
    $this->validate();

    try {
        $driver = Driver::create([
            'first_name'          => $this->first_name,
            'last_name'           => $this->last_name,
            'pers_code'           => $this->pers_code,
            'citizenship_id'         => $this->citizenship_id,
            'declared_country_id' => $this->declared_country_id,
            'declared_city_id'    => $this->declared_city_id,
            'declared_street'     => $this->declared_street,
            'declared_building'   => $this->declared_building,
            'declared_room'       => $this->declared_room,
            'declared_postcode'   => $this->declared_postcode,
            'actual_country_id'   => $this->actual_country_id,
            'actual_city_id'      => $this->actual_city_id,
            'actual_street'       => $this->actual_street,
            'actual_building'     => $this->actual_building,
            'actual_room'         => $this->actual_room,
            'actual_postcode'     => $this->actual_postcode,
            'phone'               => $this->phone,
            'email'               => $this->email,
            'status'              => $this->status,
            'is_active'           => $this->is_active,
            'license_number'      => $this->license_number,
            'license_issued'      => $this->license_issued,
            'license_end'         => $this->license_end,
            'code95_issued'       => $this->code95_issued,
            'code95_end'          => $this->code95_end,
            'permit_issued'       => $this->permit_issued,
            'permit_expired'      => $this->permit_expired,
            'medical_issued'      => $this->medical_issued,
            'medical_expired'     => $this->medical_expired,
            'medical_exam_passed' => $this->medical_exam_passed,
            'medical_exam_expired'=> $this->medical_exam_expired,
            'declaration_issued'  => $this->declaration_issued,
            'declaration_expired' => $this->declaration_expired,
            'company'             => $this->company,
        ]);

        // Фото
        if ($this->photo) {
            $driver->photo = $this->photo->store('drivers/photos', 'public');
        }
        if ($this->license_photo) {
            $driver->license_photo = $this->license_photo->store('drivers/licenses', 'public');
        }
        if ($this->medical_certificate_photo) {
            $driver->medical_certificate_photo = $this->medical_certificate_photo->store('drivers/medical', 'public');
        }

        $driver->save();

        Log::info('✅ Driver successfully saved', [
            'driver_id' => $driver->id,
            'name' => "{$driver->first_name} {$driver->last_name}",
            'company' => $driver->company,
            'created_by' => auth()->user()->name ?? 'system',
        ]);

        session()->flash('success', 'Driver successfully created!');
         return redirect()->route('drivers.index');
    } catch (Exception $e) {
        Log::error('❌ Error while saving driver', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        session()->flash('error', 'Error saving driver: ' . $e->getMessage());
    }
}

    // ================= EVENTS =================
    public function updatedDeclaredCountryId($value)
    {
        $this->declared_city_id = null;
    }

    public function updatedActualCountryId($value)
    {
        $this->actual_city_id = null;
    }

    // ================= RENDER =================
    public function render()
    { 
        $countries = config('countries');
        $companies = config('companies');

        $declaredCities = $this->declared_country_id
            ? getCitiesByCountryId($this->declared_country_id)
            : [];

        $actualCities = $this->actual_country_id
            ? getCitiesByCountryId($this->actual_country_id)
            : [];

       
   

    return view('livewire.drivers.create-driver', [
    'companies' => $companies,
    'countries' => $countries,
    'declaredCities' => $declaredCities,
    'actualCities' => $actualCities,
])->layout('layouts.app')->title('Add Driver');
    }
}
