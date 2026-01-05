<?php

namespace App\Livewire\Trailers;

use Livewire\Component;
use App\Models\Trailer;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EditTrailer extends Component
{
    use WithFileUploads;

    public $trailer;

    public $brand, $plate, $year;
    public $inspection_issued, $inspection_expired;
    public $insurance_company, $insurance_number, $insurance_issued, $insurance_expired;
    public $tir_issued, $tir_expired;
    public $vin, $status, $is_active;
    public $tech_passport_nr, $tech_passport_issued, $tech_passport_expired, $company;
    public $tech_passport_photo, $current_photo;
    public $type_id;

   public function mount(Trailer $trailer)
{
    $this->trailer = $trailer;
    $this->type_id = $trailer->type_id;

    // Основные поля
    $this->brand = $trailer->brand;
    $this->plate = $trailer->plate;
    $this->year = $trailer->year;
    $this->company = $trailer->company;

     $this->insurance_number = $trailer->insurance_number;
      $this->insurance_company = $trailer->insurance_company;
    // Inspection
    $this->inspection_issued = $trailer->inspection_issued ? Carbon::parse($trailer->inspection_issued)->format('Y-m-d') : null;
$this->inspection_expired = $trailer->inspection_expired ? Carbon::parse($trailer->inspection_expired)->format('Y-m-d') : null;

$this->insurance_issued = $trailer->insurance_issued ? Carbon::parse($trailer->insurance_issued)->format('Y-m-d') : null;
$this->insurance_expired = $trailer->insurance_expired ? Carbon::parse($trailer->insurance_expired)->format('Y-m-d') : null;

$this->tir_issued = $trailer->tir_issued ? Carbon::parse($trailer->tir_issued)->format('Y-m-d') : null;
$this->tir_expired = $trailer->tir_expired ? Carbon::parse($trailer->tir_expired)->format('Y-m-d') : null;

$this->tech_passport_issued = $trailer->tech_passport_issued ? Carbon::parse($trailer->tech_passport_issued)->format('Y-m-d') : null;
$this->tech_passport_expired = $trailer->tech_passport_expired ? Carbon::parse($trailer->tech_passport_expired)->format('Y-m-d') : null;

    // VIN и статус
    $this->vin = $trailer->vin;
    $this->status = $trailer->status;
    $this->is_active = $trailer->is_active;

    // Tech passport
    $this->tech_passport_nr = $trailer->tech_passport_nr;

    $this->current_photo = $trailer->tech_passport_photo;
}

    protected function rules()
    {
        return [
            'brand' => 'required|string|max:255',
            'plate' => ['required','string','max:255', Rule::unique('trailers','plate')->ignore($this->trailer->id)],
            'year' => 'required|integer|min:1900|max:' . (date('Y')+1),

            'inspection_issued' => 'required|date',
            'inspection_expired' => 'required|date|after_or_equal:inspection_issued',

            'insurance_company' => 'required|string|max:255',
            'insurance_number' => 'required|string|max:255',
            'insurance_issued' => 'required|date',
            'insurance_expired' => 'required|date|after_or_equal:insurance_issued',
            'company' => 'required',

            'tir_issued' => 'required|date',
            'tir_expired' => 'required|date|after_or_equal:tir_issued',

            'vin' => ['required','string', Rule::unique('trailers','vin')->ignore($this->trailer->id)],
            'status' => 'required|integer',
            'is_active' => 'required|boolean',

            'tech_passport_nr' => 'nullable|string|max:255',
            'tech_passport_issued' => 'nullable|date|required_with:tech_passport_expired',
            'tech_passport_expired' => 'nullable|date|after_or_equal:tech_passport_issued',
            'tech_passport_photo' => 'nullable|image|max:22048',
            'type_id' => 'required|integer|in:' . implode(',', array_keys(config('trailer-types.types'))),
        ];
    }

    public function update()
    {
        $this->validate();

        // Если загружена новая фотография
        if ($this->tech_passport_photo) {
            $photoPath = $this->tech_passport_photo->store('trailers', 'public');
        } else {
            $photoPath = $this->current_photo;
        }

        $this->trailer->update([
            'brand' => $this->brand,
            'plate' => $this->plate,
            'year' => $this->year,
            'company' => $this->company,
            'inspection_issued' => $this->inspection_issued,
            'inspection_expired' => $this->inspection_expired,
            'insurance_company' => $this->insurance_company,
            'insurance_number' => $this->insurance_number,
            'insurance_issued' => $this->insurance_issued,
            'insurance_expired' => $this->insurance_expired,
            'tir_issued' => $this->tir_issued,
            'tir_expired' => $this->tir_expired,
            'vin' => $this->vin,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'tech_passport_nr' => $this->tech_passport_nr,
            'tech_passport_issued' => $this->tech_passport_issued,
            'tech_passport_expired' => $this->tech_passport_expired,
            'tech_passport_photo' => $photoPath,
            'type_id' => $this->type_id,
        ]);

        session()->flash('success', 'Trailer updated successfully!');
        return redirect()->route('trailers.index');
    }

    public function render()
    {
        return view('livewire.trailers.edit-trailer')->layout('layouts.app');
    }
}
