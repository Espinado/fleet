<?php

namespace App\Livewire\Trucks;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Truck;
use Illuminate\Validation\Rule;


class CreateTruck extends Component
{
    use WithFileUploads;

    public $brand;
    public $model;
    public $plate;
    public $year;

    public $inspection_issued;
    public $inspection_expired;

    public $insurance_number;
    public $insurance_issued;
    public $insurance_expired;
    public $insurance_company;

    public $vin;
    public $status = 1;
    public $is_active = true;
    public $company;
    public $tech_passport_nr;
    public $tech_passport_issued;
    public $tech_passport_expired;
    public $tech_passport_photo; // <-- обязательно: свойство для файла

    protected function rules()
    {
        return [
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'plate' => ['required','string','max:255', Rule::unique('trucks','plate')],
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'company' => 'required|',
            'inspection_issued' => 'required|date',
            'inspection_expired' => 'required|date|after_or_equal:inspection_issued',

            'insurance_company' => 'required|string|max:255',
            'insurance_number' => 'required|string|max:255',
            'insurance_issued' => 'required|date',
            'insurance_expired' => 'required|date|after_or_equal:insurance_issued',

            'vin' => ['required','string', Rule::unique('trucks','vin')],

            'tech_passport_nr' => 'required|string|max:255',
            'tech_passport_issued' => 'required|date',
            'tech_passport_expired' => 'required|date|after_or_equal:tech_passport_issued',
            'tech_passport_photo' => 'nullable|image|max:24096', // up to 4MB
        ];
    }

    public function save()
    {
        $this->validate();

        // Сохраняем фото на диск public
        $photoPath = null;
        if ($this->tech_passport_photo) {
            $photoPath = $this->tech_passport_photo->store('trucks/tech_passports', 'public');
        }

        $truck = Truck::create([
            'brand' => $this->brand,
            'model' => $this->model,
            'plate' => $this->plate,
            'year' => $this->year,
            'company' => $this->company,
            'inspection_issued' => $this->inspection_issued,
            'inspection_expired' => $this->inspection_expired,

            'insurance_number' => $this->insurance_number,
            'insurance_issued' => $this->insurance_issued,
            'insurance_expired' => $this->insurance_expired,
            'insurance_company' => $this->insurance_company,

            'vin' => $this->vin,
            'status' => $this->status,
            'is_active' => $this->is_active,

            'tech_passport_nr' => $this->tech_passport_nr,
            'tech_passport_issued' => $this->tech_passport_issued,
            'tech_passport_expired' => $this->tech_passport_expired,
            'tech_passport_photo' => $photoPath,
        ]);

        session()->flash('success', 'Truck added successfully!');

        return redirect()->route('trucks.index');
    }

    public function render()
    {
        return view('livewire.trucks.create-truck')->layout('layouts.app');
    }


}
