<?php

namespace App\Livewire\Drivers;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Driver;
use App\Models\Company;
use App\Helpers\ImageCompress;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EditDriver extends Component
{
    use WithFileUploads;

    public Driver $driver;

    // Personal info
    public $first_name, $last_name, $pers_code, $phone, $email;

    // ✅ было $company, стало $company_id
    public ?int $company_id = null;

    public $declared_street, $declared_building, $declared_room, $declared_postcode;
    public $actual_street, $actual_building, $actual_room, $status, $is_active;

    // IDs
    public ?int $citizenship_id = null;
    public ?int $declared_country_id = null;
    public ?int $declared_city_id = null;
    public ?int $actual_country_id = null;
    public ?int $actual_city_id = null;

    // (у тебя в таблице actual_postcode строкой, судя по дампу — оставляем string)
    public $actual_postcode = null;

    // Documents
    public $license_number, $license_issued, $license_end;
    public $code95_issued, $code95_end;
    public $permit_issued, $permit_expired;
    public $medical_issued, $medical_expired;
    public $medical_exam_passed, $medical_exam_expired;
    public $declaration_issued, $declaration_expired;

    // Photos
    public $photo, $license_photo, $medical_certificate_photo;
    public $old_photo, $old_license_photo, $old_medical_certificate_photo;

    protected function rules(): array
    {
        $r = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'pers_code' => 'required|string|max:50',
            'company_id' => 'required|integer|exists:companies,id',
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
            'photo' => 'nullable|image',
            'license_photo' => 'nullable|image',
            'medical_certificate_photo' => 'nullable|image',
        ];
        if (filled($this->code95_issued ?? null)) {
            $r['code95_end'] = ['nullable', 'date', 'after:code95_issued'];
        }
        if (filled($this->permit_issued ?? null)) {
            $r['permit_expired'] = ['nullable', 'date', 'after:permit_issued'];
        }
        if (filled($this->medical_issued ?? null)) {
            $r['medical_expired'] = ['nullable', 'date', 'after:medical_issued'];
        }
        if (filled($this->medical_exam_passed ?? null)) {
            $r['medical_exam_expired'] = ['nullable', 'date', 'after:medical_exam_passed'];
        }
        if (filled($this->declaration_issued ?? null)) {
            $r['declaration_expired'] = ['nullable', 'date', 'after:declaration_issued'];
        }
        return $r;
    }

    public function mount(Driver $driver)
    {
        $this->driver = $driver;

        $this->first_name = $driver->first_name;
        $this->last_name = $driver->last_name;
        $this->pers_code = $driver->pers_code;
        $this->citizenship_id = $driver->citizenship_id;
        $this->phone = $driver->phone;
        $this->email = $driver->email;

        // ✅ company_id
        $this->company_id = $driver->company_id;

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
        $this->actual_postcode = $driver->actual_postcode;

        $this->status = $driver->status;
        $this->is_active = $driver->is_active;
        $this->license_number = $driver->license_number;

        // Convert dates for form inputs
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

        // Old photos
        $this->old_photo = $driver->photo;
        $this->old_license_photo = $driver->license_photo;
        $this->old_medical_certificate_photo = $driver->medical_certificate_photo;
    }

    public function updatedDeclaredCountryId()
    {
        $this->declared_city_id = null;
    }

    public function updatedActualCountryId()
    {
        $this->actual_city_id = null;
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            // Save photos (replace old if new uploaded; сжатие + коррекция ориентации по EXIF)
            if ($this->photo) {
                if ($this->old_photo && Storage::disk('public')->exists($this->old_photo)) {
                    Storage::disk('public')->delete($this->old_photo);
                }
                $this->driver->photo = ImageCompress::storeUpload($this->photo, 'drivers/photos', 'public') ?? $this->photo->store('drivers/photos', 'public');
            }

            if ($this->license_photo) {
                if ($this->old_license_photo && Storage::disk('public')->exists($this->old_license_photo)) {
                    Storage::disk('public')->delete($this->old_license_photo);
                }
                $this->driver->license_photo = ImageCompress::storeUpload($this->license_photo, 'drivers/licenses', 'public') ?? $this->license_photo->store('drivers/licenses', 'public');
            }

            if ($this->medical_certificate_photo) {
                if ($this->old_medical_certificate_photo && Storage::disk('public')->exists($this->old_medical_certificate_photo)) {
                    Storage::disk('public')->delete($this->old_medical_certificate_photo);
                }
                $this->driver->medical_certificate_photo = ImageCompress::storeUpload($this->medical_certificate_photo, 'drivers/medical', 'public') ?? $this->medical_certificate_photo->store('drivers/medical', 'public');
            }

            // Update driver
            $this->driver->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'pers_code' => $this->pers_code,
                'citizenship_id' => $this->citizenship_id,

                // ✅ company_id
                'company_id' => $this->company_id,

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
                'actual_postcode' => $this->actual_postcode,

                'status' => $this->status,
                'is_active' => $this->is_active,

                'license_number' => $this->license_number,
                'license_issued' => $this->license_issued,
                'license_end' => $this->license_end,
                'code95_issued' => filled($this->code95_issued) ? $this->code95_issued : null,
                'code95_end' => filled($this->code95_end) ? $this->code95_end : null,
                'permit_issued' => filled($this->permit_issued) ? $this->permit_issued : null,
                'permit_expired' => filled($this->permit_expired) ? $this->permit_expired : null,
                'medical_issued' => filled($this->medical_issued) ? $this->medical_issued : null,
                'medical_expired' => filled($this->medical_expired) ? $this->medical_expired : null,
                'medical_exam_passed' => filled($this->medical_exam_passed) ? $this->medical_exam_passed : null,
                'medical_exam_expired' => filled($this->medical_exam_expired) ? $this->medical_exam_expired : null,
                'declaration_issued' => filled($this->declaration_issued) ? $this->declaration_issued : null,
                'declaration_expired' => filled($this->declaration_expired) ? $this->declaration_expired : null,

                'photo' => $this->driver->photo,
                'license_photo' => $this->driver->license_photo,
                'medical_certificate_photo' => $this->driver->medical_certificate_photo,
            ]);

            // ✅ Синхронизируем users.company_id для связанного пользователя
            if ($this->driver->user_id) {
                \App\Models\User::where('id', $this->driver->user_id)
                    ->where('role', 'driver')
                    ->update(['company_id' => $this->company_id]);
            }
        });

        session()->flash('success', __('app.driver.edit.success'));
        return redirect()->route('drivers.index');
    }

    public function render()
    {
        $countries = config('countries');

        // ✅ компании из БД
        $companies = Company::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'slug']);

        $declaredCities = $this->declared_country_id
            ? getCitiesByCountryId($this->declared_country_id)
            : [];

        $actualCities = $this->actual_country_id
            ? getCitiesByCountryId($this->actual_country_id)
            : [];

        return view('livewire.drivers.edit-driver', [
            'companies'      => $companies,
            'countries'      => $countries,
            'declaredCities' => $declaredCities,
            'actualCities'   => $actualCities,
        ])->layout('layouts.app', [
            'title' => __('app.drivers.title'),
        ]);
    }
}
