<?php

namespace App\Livewire\Trucks;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Truck;
use App\Models\Company;
use App\Helpers\ImageCompress;
use Illuminate\Validation\Rule;

class EditTruck extends Component
{
    use WithFileUploads;

    public Truck $truck;

    public $brand, $model, $plate, $year;
    public $inspection_issued, $inspection_expired;
    public $insurance_company, $insurance_number, $insurance_issued, $insurance_expired;
    public $vin, $tech_passport_nr, $tech_passport_issued, $tech_passport_expired;

    public $tech_passport_photo; // новое фото
    public $existing_photo;      // старое фото

    // ✅ было $company, стало $company_id
    public ?int $company_id = null;

    public $license_number, $license_issued, $license_expired;

    /** Fleet Maintenance: следующее ТО и интервалы (все необязательные). */
    public $next_service_km, $next_service_date, $service_interval_km, $service_interval_months;

    public function mount(Truck $truck)
    {
        $this->truck = $truck;

        $this->brand = $truck->brand;
        $this->model = $truck->model;
        $this->plate = $truck->plate;
        $this->year  = $truck->year;

        // ✅ company_id
        $this->company_id = $truck->company_id ?? null;

        // даты в HTML формате
        $this->inspection_issued  = optional($truck->inspection_issued)->format('Y-m-d');
        $this->inspection_expired = optional($truck->inspection_expired)->format('Y-m-d');

        $this->insurance_company = $truck->insurance_company;
        $this->insurance_number  = $truck->insurance_number;

        $this->insurance_issued  = optional($truck->insurance_issued)->format('Y-m-d');
        $this->insurance_expired = optional($truck->insurance_expired)->format('Y-m-d');

        $this->vin = $truck->vin;

        $this->tech_passport_nr      = $truck->tech_passport_nr;
        $this->tech_passport_issued  = optional($truck->tech_passport_issued)->format('Y-m-d');
        $this->tech_passport_expired = optional($truck->tech_passport_expired)->format('Y-m-d');

        $this->existing_photo = $truck->tech_passport_photo;

        $this->license_number  = $truck->license_number;
        $this->license_issued  = optional($truck->license_issued)->format('Y-m-d');
        $this->license_expired = optional($truck->license_expired)->format('Y-m-d');

        $this->next_service_km = $truck->next_service_km;
        $this->next_service_date = optional($truck->next_service_date)->format('Y-m-d');
        $this->service_interval_km = $truck->service_interval_km;
        $this->service_interval_months = $truck->service_interval_months;
    }

    public function save()
    {
        $validated = $this->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'plate' => 'required|string|max:255',
            'year'  => 'required|integer|min:1900|max:' . (date('Y') + 1),

            // ✅ company_id
            'company_id' => 'required|integer|exists:companies,id',

            'inspection_issued'  => 'required|date',
            'inspection_expired' => 'required|date|after_or_equal:inspection_issued',

            'insurance_company' => 'required|string|max:255',
            'insurance_number'  => 'required|string|max:255',
            'insurance_issued'  => 'required|date',
            'insurance_expired' => 'required|date|after_or_equal:insurance_issued',

            'vin' => ['required', 'string', Rule::unique('trucks', 'vin')->ignore($this->truck->id)],

            'tech_passport_nr' => 'required|string|max:255',
            'tech_passport_issued'  => 'required|date',
            'tech_passport_expired' => 'required|date|after_or_equal:tech_passport_issued',
            'tech_passport_photo' => 'nullable|image',

            'license_number'  => 'nullable|string|max:50',
            'license_issued'  => 'nullable|date',
            'license_expired' => 'nullable|date|after_or_equal:license_issued',

            'next_service_km' => 'nullable|integer|min:0',
            'next_service_date' => 'nullable|date',
            'service_interval_km' => 'nullable|integer|min:0',
            'service_interval_months' => 'nullable|integer|min:0|max:120',
        ]);

        $this->dispatch('scroll-top');

        // фото: новое (сжатие + коррекция ориентации) или оставляем старое
        if ($this->tech_passport_photo) {
            $path = ImageCompress::storeUpload($this->tech_passport_photo, 'trucks/tech_passports', 'public') ?? $this->tech_passport_photo->store('trucks/tech_passports', 'public');
            $validated['tech_passport_photo'] = $path;
        } else {
            $validated['tech_passport_photo'] = $this->existing_photo;
        }

        $validated['next_service_km'] = $this->next_service_km ? (int) $this->next_service_km : null;
        $validated['next_service_date'] = $this->next_service_date ?: null;
        $validated['service_interval_km'] = $this->service_interval_km ? (int) $this->service_interval_km : null;
        $validated['service_interval_months'] = $this->service_interval_months ? (int) $this->service_interval_months : null;

        $this->truck->update($validated);

        session()->flash('success', __('app.truck.edit.save'));
        return redirect()->route('trucks.show', $this->truck->id);
    }

    public function render()
    {
        $companies = Company::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'slug']);

        return view('livewire.trucks.edit-truck', [
            'companies' => $companies,
        ])->layout('layouts.app', [
            'title' => __('app.truck.edit.title'),
        ]);
    }
}
