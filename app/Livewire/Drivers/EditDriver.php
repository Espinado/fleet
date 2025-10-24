<?php

namespace App\Livewire\Drivers;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Driver;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EditDriver extends Component
{
    use WithFileUploads;

    public Driver $driver;

    // Personal info
    public $first_name, $last_name, $pers_code, $phone, $email;
    public  $declared_street, $declared_building, $declared_room, $declared_postcode;
    public  $actual_street, $actual_building, $actual_room, $status, $is_active;
     public $company;
    // Documents
    public $license_number, $license_issued, $license_end;
    public $code95_issued, $code95_end;
    public $permit_issued, $permit_expired;
    public $medical_issued, $medical_expired;
    public $medical_exam_passed, $medical_exam_expired;
    public $declaration_issued, $declaration_expired;
    public ?int $citizenship = null;
   public ?int $declared_country_id = null;
   public ?int $declared_city_id = null;
   public ?int $actual_country_id = null;
   public ?int $actual_city_id = null;


    // Photos
    public $photo, $license_photo, $medical_certificate_photo;

    // Store old photo paths
    public $old_photo, $old_license_photo, $old_medical_certificate_photo;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'pers_code' => 'required|string|max:50',
        'company' => 'required',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:50',
        'license_number' => 'nullable|string|max:50',
        'license_issued' => 'nullable|date',
        'license_end' => 'nullable|date',
        'code95_issued' => 'nullable|date',
        'code95_end' => 'nullable|date',
        'permit_issued' => 'nullable|date',
        'permit_expired' => 'nullable|date',
        'medical_issued' => 'nullable|date',
        'medical_expired' => 'nullable|date',
        'medical_exam_passed' => 'nullable|date',
        'medical_exam_expired' => 'nullable|date',
        'declaration_issued' => 'nullable|date',
        'declaration_expired' => 'nullable|date',
        'photo' => 'nullable|image|max:2048',
        'license_photo' => 'nullable|image|max:2048',
        'medical_certificate_photo' => 'nullable|image|max:2048',
    ];

    public function updatedDeclaredCountryId()
{
    $this->declared_city_id = null;
}

public function updatedActualCountryId()
{
    $this->actual_city_id = null;
}

    public function mount(Driver $driver)
    {
        $this->driver = $driver;


        // Personal info
        $this->first_name = $driver->first_name;
        $this->last_name = $driver->last_name;
        $this->pers_code = $driver->pers_code;
        $this->citizenship = $driver->citizenship;
        $this->phone = $driver->phone;
        $this->email = $driver->email;
        $this->company = $driver->company;

        $this->declared_country_id = $driver->declared_country_id;
        $this->declared_city_id = $driver->declared_city_id;
        $this->declared_street = $driver->declared_street;
        $this->declared_building = $driver->declared_building;
        $this->declared_room = $driver->declared_room;
        $this->declared_postcode = $driver->declared_postcode;

        $this->actual_country_id = $driver->actual_country_id;
        $this->actual_city_id = $driver->actual_city_id;
        $this->actual_street = $driver->actual_street;
        $this->actual_building = $driver->actual_building;
        $this->actual_room = $driver->actual_room;
        $this->status = $driver->status;
        $this->is_active = $driver->is_active;
          $this->license_number = $driver->license_number;

       // Documents (приведение к Y-m-d)
   $this->license_issued = $driver->license_issued ? Carbon::parse($driver->license_issued)->format('Y-m-d') : null;
$this->license_end    = $driver->license_end ? Carbon::parse($driver->license_end)->format('Y-m-d') : null;
$this->code95_issued  = $driver->code95_issued ? Carbon::parse($driver->code95_issued)->format('Y-m-d') : null;
$this->code95_end     = $driver->code95_end ? Carbon::parse($driver->code95_end)->format('Y-m-d') : null;
$this->permit_issued  = $driver->permit_issued ? Carbon::parse($driver->permit_issued)->format('Y-m-d') : null;
$this->permit_expired = $driver->permit_expired ? Carbon::parse($driver->permit_expired)->format('Y-m-d') : null;
$this->medical_issued = $driver->medical_issued ? Carbon::parse($driver->medical_issued)->format('Y-m-d') : null;
$this->medical_expired = $driver->medical_expired ? Carbon::parse($driver->medical_expired)->format('Y-m-d') : null;
$this->medical_exam_passed = $driver->medical_exam_passed ? Carbon::parse($driver->medical_exam_passed)->format('Y-m-d') : null;
$this->medical_exam_expired = $driver->medical_exam_expired ? Carbon::parse($driver->medical_exam_expired)->format('Y-m-d') : null;
$this->declaration_issued = $driver->declaration_issued ? Carbon::parse($driver->declaration_issued)->format('Y-m-d') : null;
$this->declaration_expired = $driver->declaration_expired ? Carbon::parse($driver->declaration_expired)->format('Y-m-d') : null;

        // Photos
        $this->old_photo = $driver->photo;
        $this->old_license_photo = $driver->license_photo;
        $this->old_medical_certificate_photo = $driver->medical_certificate_photo;
    }

    public function save()
    {
        $this->validate();

        // Save photos
        if ($this->photo) {
            $this->driver->photo = $this->photo->store('drivers', 'public');
        } else {
            $this->driver->photo = $this->old_photo;
        }

        if ($this->license_photo) {
            $this->driver->license_photo = $this->license_photo->store('drivers', 'public');
        } else {
            $this->driver->license_photo = $this->old_license_photo;
        }

        if ($this->medical_certificate_photo) {
            $this->driver->medical_certificate_photo = $this->medical_certificate_photo->store('drivers', 'public');
        } else {
            $this->driver->medical_certificate_photo = $this->old_medical_certificate_photo;
        }

        // Save other fields
        $this->driver->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'pers_code' => $this->pers_code,
            'citizenship' => $this->citizenship,
            'company' => $this->company,
            'phone' => $this->phone,
            'email' => $this->email,
            'declared_country_id' => $this->declared_country_id,
            'declared_city_id' => $this->declared_city_id,
            'declared_street' => $this->declared_street,
            'declared_building' => $this->declared_building,
            'declared_room' => $this->declared_room,
            'declared_postcode' => $this->declared_postcode,
            'actual_country_id' => $this->actual_country_id,
            'actual_city_id' => $this->actual_city_id,
            'actual_street' => $this->actual_street,
            'actual_building' => $this->actual_building,
            'actual_room' => $this->actual_room,
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

        session()->flash('success', 'Driver updated successfully!');
         return redirect()->route('drivers.index');
    }

    public function render()
    {
        return view('livewire.drivers.edit-driver')->layout('layouts.app');
    }

    public function destroy()
{
    if ($this->driver) {
        $this->driver->delete();

        // Можно сбросить поля формы, если остаёмся на этой странице
        $this->reset();

        // Сообщение пользователю
        session()->flash('success', 'Driver deleted successfully.');

        // При желании — редирект на список водителей
        return redirect()->route('drivers.index');
    }

    session()->flash('error', 'Driver not found.');
}
}
