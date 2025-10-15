<?php

namespace App\Livewire\Trucks;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Truck;
use Illuminate\Validation\Rule;

class EditTruck extends Component
{
    use WithFileUploads;

    public Truck $truck;

    public $brand, $model, $plate, $year;
    public $inspection_issued, $inspection_expired;
    public $insurance_company, $insurance_number, $insurance_issued, $insurance_expired;
    public $vin, $tech_passport_nr, $tech_passport_issued, $tech_passport_expired;
    public $tech_passport_photo; // новое фото (temporary)
    public $existing_photo;      // старое фото (из базы)
    public $company;
    public function mount(Truck $truck)
    {
        $this->truck = $truck;

        $this->brand = $truck->brand;
        $this->model = $truck->model;
        $this->plate = $truck->plate;
        $this->year = $truck->year;
        $this->company = $truck->company;
        $this->inspection_issued = $truck->inspection_issued;
        $this->inspection_expired = $truck->inspection_expired;
        $this->insurance_company = $truck->insurance_company;
        $this->insurance_number = $truck->insurance_number;
        $this->insurance_issued = $truck->insurance_issued;
        $this->insurance_expired = $truck->insurance_expired;
        $this->vin = $truck->vin;
        $this->tech_passport_nr = $truck->tech_passport_nr;
        $this->tech_passport_issued = $truck->tech_passport_issued;
        $this->tech_passport_expired = $truck->tech_passport_expired;
        $this->existing_photo = $truck->tech_passport_photo;
    }

    public function save()
    {
        $validated = $this->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'plate' => ['required', 'string', 'max:255', Rule::unique('trucks', 'plate')->ignore($this->truck->id)],
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'inspection_issued' => 'required|date',
            'inspection_expired' => 'required|date|after_or_equal:inspection_issued',
            'insurance_company' => 'required|string|max:255',
            'insurance_number' => 'required|string|max:255',
            'insurance_issued' => 'required|date',
            'company' => 'required',
            'insurance_expired' => 'required|date|after_or_equal:insurance_issued',
            'vin' => ['required', 'string', Rule::unique('trucks', 'vin')->ignore($this->truck->id)],
            'tech_passport_nr' => 'required|string|max:255',
            'tech_passport_issued' => 'required|date',
            'tech_passport_expired' => 'required|date|after_or_equal:tech_passport_issued',
            'tech_passport_photo' => 'nullable|image', // необязательное новое фото
        ]);

        if ($this->tech_passport_photo) {
            $path = $this->tech_passport_photo->store('trucks', 'public');
            $validated['tech_passport_photo'] = $path;
        } else {
            $validated['tech_passport_photo'] = $this->existing_photo;
        }

        $this->truck->update($validated);

        session()->flash('success', 'Truck updated successfully!');
        return redirect()->route('trucks.show', $this->truck->id);
    }

    public function render()
    {
        return view('livewire.trucks.edit-truck')
            ->layout('layouts.app');
    }


}
