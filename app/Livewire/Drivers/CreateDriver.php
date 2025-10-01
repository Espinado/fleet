<?php

namespace App\Livewire\Drivers;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Driver;
use App\Enums\DriverStatus;

class CreateDriver extends Component
{
    use WithFileUploads;

    // Личные данные
    public $first_name, $last_name, $pers_code, $citizenship;
    public $declared_country, $declared_city, $declared_street, $declared_building, $declared_room, $declared_postcode;
    public $actual_country, $actual_city, $actual_street, $actual_building, $actual_room;
    public $phone, $email;
    public $status = DriverStatus::ON_WORK->value;
    public $is_active = true;

    // Документы: License
    public $license_number, $license_issued, $license_end;
    // Документы: 95 Code
    public $code95_issued, $code95_end;
    // Документы: Permission
    public $permit_issued, $permit_expired;
    // Документы: Medical
    public $medical_issued, $medical_expired, $medical_exam_passed, $medical_exam_expired;
    // Документы: Declaration
    public $declaration_issued, $declaration_expired;

    // Фото
    public $photo, $license_photo, $medical_certificate_photo;

    public $successMessage;

    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'pers_code' => 'required|string|unique:drivers,pers_code',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
               'citizenship' => 'required|string|max:255',

        // Адреса
        'declared_country' => 'required|string|max:255',
        'declared_city' => 'required|string|max:255',
        'declared_street' => 'required|string|max:255',
        'declared_building' => 'required|string|max:20',
        'declared_room' => 'nullable|string|max:20',
        'declared_postcode' => 'required|string|max:20',
        'actual_country' => 'required|string|max:255',
        'actual_city' => 'required|string|max:255',
        'actual_street' => 'required|string|max:255',
        'actual_building' => 'required|string|max:20',
        'actual_room' => 'nullable|string|max:20',

            'license_number' => 'required|string|unique:drivers,license_number',
            'license_issued' => 'required|date',
            'license_end'    => 'required|date|after:license_issued',

            'code95_issued'  => 'required|date',
            'code95_end'     => 'required|date|after:code95_issued',

            'permit_issued'  => 'required|date',
            'permit_expired' => 'required|date|after:permit_issued',

            'medical_issued' => 'required|date',
            'medical_expired'=> 'required|date|after:medical_issued',
            'medical_exam_passed'  => 'required|date',
            'medical_exam_expired' => 'required|date|after:medical_exam_passed',

            'declaration_issued' => 'required|date',
            'declaration_expired'=> 'required|date|after:declaration_issued',

            'photo' => 'nullable|image',
            'license_photo' => 'nullable|image',
            'medical_certificate_photo' => 'nullable|image',
        ];
    }

    public function save()
    {
        $this->validate();

        $driver = Driver::create([
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'pers_code' => $this->pers_code,
            'citizenship' => $this->citizenship,
            'declared_country' => $this->declared_country,
            'declared_city' => $this->declared_city,
            'declared_street' => $this->declared_street,
            'declared_building' => $this->declared_building,
            'declared_room' => $this->declared_room,
            'declared_postcode' => $this->declared_postcode,
            'actual_country' => $this->actual_country,
            'actual_city' => $this->actual_city,
            'actual_street' => $this->actual_street,
            'actual_building' => $this->actual_building,
            'actual_room' => $this->actual_room,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'license_number' => $this->license_number,
            'license_issued' => $this->license_issued,
            'license_end' => $this->license_end,
            'code95_issued' => $this->code95_issued,
            'code95_end' => $this->code95_end,
            'permit_issued' => $this->permit_issued,
            'permit_expired' => $this->permit_expired,
            'medical_issued' => $this->medical_issued,
            'medical_expired' => $this->medical_expired,
            'medical_exam_passed' => $this->medical_exam_passed,
            'medical_exam_expired' => $this->medical_exam_expired,
            'declaration_issued' => $this->declaration_issued,
            'declaration_expired' => $this->declaration_expired,
        ]);

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

        $this->successMessage = '✅ Driver created successfully!';
        $this->reset(['photo', 'license_photo', 'medical_certificate_photo']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.drivers.create-driver')
            ->layout('layouts.app');
    }
}
