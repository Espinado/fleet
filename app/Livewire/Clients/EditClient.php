<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class EditClient extends Component
{
    public $client;
    public $form = [];
    

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

    // ✅ Используем ID, а не текстовые значения
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

    // Documents (Y-m-d формат)
    $this->license_issued = optional($driver->license_issued)->format('Y-m-d');
    $this->license_end = optional($driver->license_end)->format('Y-m-d');
    $this->code95_issued = optional($driver->code95_issued)->format('Y-m-d');
    $this->code95_end = optional($driver->code95_end)->format('Y-m-d');
    $this->permit_issued = optional($driver->permit_issued)->format('Y-m-d');
    $this->permit_expired = optional($driver->permit_expired)->format('Y-m-d');
    $this->medical_issued = optional($driver->medical_issued)->format('Y-m-d');
    $this->medical_expired = optional($driver->medical_expired)->format('Y-m-d');
    $this->medical_exam_passed = optional($driver->medical_exam_passed)->format('Y-m-d');
    $this->medical_exam_expired = optional($driver->medical_exam_expired)->format('Y-m-d');
    $this->declaration_issued = optional($driver->declaration_issued)->format('Y-m-d');
    $this->declaration_expired = optional($driver->declaration_expired)->format('Y-m-d');

    // Photos
    $this->old_photo = $driver->photo;
    $this->old_license_photo = $driver->license_photo;
    $this->old_medical_certificate_photo = $driver->medical_certificate_photo;
}

    protected $rules = [
        'form.company_name'   => 'required|string|max:255',
        'form.reg_nr'         => 'nullable|string|max:50',
        'form.jur_country'    => 'nullable|string|max:100',
        'form.jur_city'       => 'nullable|string|max:100',
        'form.jur_address'    => 'nullable|string|max:255',
        'form.jur_post_code'  => 'nullable|string|max:20',
        'form.fiz_country'    => 'nullable|string|max:100',
        'form.fiz_city'       => 'nullable|string|max:100',
        'form.fiz_address'    => 'nullable|string|max:255',
        'form.fiz_post_code'  => 'nullable|string|max:20',
        'form.bank_name'      => 'nullable|string|max:255',
        'form.swift'          => 'nullable|string|max:50',
        'form.email'          => 'nullable|email|max:255',
        'form.phone'          => 'nullable|string|max:50',
        'form.representative' => 'nullable|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        // Обновляем модель
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


        session()->flash('success', 'Client updated successfully!');
        return redirect()->route('clients.show', $this->client->id);
    }

    public function render()
    {
        return view('livewire.clients.edit-client')
            ->layout('layouts.app')
            ->title('Edit Client');
    }
}
