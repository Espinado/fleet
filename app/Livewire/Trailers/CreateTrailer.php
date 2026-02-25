<?php

namespace App\Livewire\Trailers;

use Livewire\Component;
use App\Models\Trailer;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

class CreateTrailer extends Component
{
    use WithFileUploads;

    public $brand, $plate, $year;

    // ✅ NEW: trailer type (default = 1)
    public $type_id = 1;

    public $inspection_issued, $inspection_expired;
    public $insurance_company, $insurance_number, $insurance_issued, $insurance_expired;
    public $tir_issued, $tir_expired;
    public $vin, $status = 1, $is_active = true;
    public $tech_passport_nr, $tech_passport_issued, $tech_passport_expired, $company;
    public $tech_passport_photo;

    protected function rules()
    {
        return [
            'brand' => 'required|string|max:255',
            'plate' => ['required','string','max:255', Rule::unique('trailers','plate')],
            'year'  => 'required|integer|min:1900|max:' . (date('Y')+1),

            // ✅ NEW: validate type_id from config
            'type_id' => 'required|integer|in:' . implode(',', array_keys(config('trailer-types.types'))),

            'inspection_issued'  => 'required|date',
            'inspection_expired' => 'required|date|after_or_equal:inspection_issued',
            'company' => 'required',

            'insurance_company' => 'required|string|max:255',
            'insurance_number'  => 'required|string|max:255',
            'insurance_issued'  => 'required|date',
            'insurance_expired' => 'required|date|after_or_equal:insurance_issued',

            'tir_issued'  => 'required|date',
            'tir_expired' => 'required|date|after_or_equal:tir_issued',

            'vin' => ['required','string', Rule::unique('trailers','vin')],
            'status' => 'required|integer',
            'is_active' => 'required|boolean',

            'tech_passport_nr' => 'nullable|string|max:255',
            'tech_passport_issued'  => 'nullable|date|required_with:tech_passport_expired',
            'tech_passport_expired' => 'nullable|date|after_or_equal:tech_passport_issued',
            'tech_passport_photo' => 'nullable|image',
        ];
    }

    public function save()
    {
        $this->validate();

        $photoPath = $this->tech_passport_photo
            ? $this->tech_passport_photo->store('trailers', 'public')
            : null;

        Trailer::create([
            'brand' => $this->brand,
            'plate' => $this->plate,
            'year'  => $this->year,

            // ✅ NEW
            'type_id' => $this->type_id,

            'inspection_issued'  => $this->inspection_issued,
            'inspection_expired' => $this->inspection_expired,

            'insurance_company' => $this->insurance_company,
            'insurance_number'  => $this->insurance_number,
            'insurance_issued'  => $this->insurance_issued,
            'insurance_expired' => $this->insurance_expired,

            'tir_issued'  => $this->tir_issued,
            'tir_expired' => $this->tir_expired,

            'company' => $this->company,

            'vin' => $this->vin,
            'status' => $this->status,
            'is_active' => $this->is_active,

            'tech_passport_nr' => $this->tech_passport_nr,
            'tech_passport_issued'  => $this->tech_passport_issued,
            'tech_passport_expired' => $this->tech_passport_expired,
            'tech_passport_photo' => $photoPath,
        ]);

        session()->flash('success', 'Trailer created successfully!');
        return redirect()->route('trailers.index');
    }

    public function render()
    {
        return view('livewire.trailers.create-trailer')->layout('layouts.app', [
        'title' => 'New trailer'
    ]);
    }
}
