<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow space-y-6">
    <h2 class="text-xl font-bold">➕ Create Driver</h2>

    {{-- ✅ Сообщения --}}
    @if(session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6 relative">

        {{-- Loader --}}
        <div wire:loading.flex wire:target="save, declared_country_id, actual_country_id"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
            <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        {{-- Personal --}}
        <h3 class="font-semibold text-lg border-b pb-1">Personal Information</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>First Name</label>
                <input type="text" wire:model.live="first_name" class="w-full border rounded p-2">
                @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Last Name</label>
                <input type="text" wire:model.live="last_name" class="w-full border rounded p-2">
                @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Personal Code</label>
                <input type="text" wire:model.live="pers_code" class="w-full border rounded p-2">
                @error('pers_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Citizenship</label>
                <select wire:model.live="citizenship" class="w-full border rounded p-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>
                @error('citizenship') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Phone</label>
                <input type="text" wire:model.live="phone" class="w-full border rounded p-2">
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Email</label>
                <input type="email" wire:model.live="email" class="w-full border rounded p-2">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Declared Address --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Declared Address</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Country</label>
                <select wire:model.live="declared_country_id" class="w-full border rounded p-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>
                @error('declared_country_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="relative">
                <label>City</label>
                <select wire:model.live="declared_city_id" class="w-full border rounded p-2"
                        @disabled(empty($declared_country_id))>
                    <option value="">Select City</option>
                    @if(!empty($declared_country_id))
                        @foreach(getCitiesByCountryId($declared_country_id) as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    @endif
                </select>
                <div wire:loading wire:target="declared_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>
                @error('declared_city_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Street</label>
                <input type="text" wire:model.live="declared_street" class="w-full border rounded p-2">
                @error('declared_street') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Building</label>
                <input type="text" wire:model.live="declared_building" class="w-full border rounded p-2">
                @error('declared_building') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Room</label>
                <input type="text" wire:model.live="declared_room" class="w-full border rounded p-2">
                @error('declared_room') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Postcode</label>
                <input type="text" wire:model.live="declared_postcode" class="w-full border rounded p-2">
                @error('declared_postcode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Actual Address --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Actual Address</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Country</label>
                <select wire:model.live="actual_country_id" class="w-full border rounded p-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>
                @error('actual_country_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="relative">
                <label>City</label>
                <select wire:model.live="actual_city_id" class="w-full border rounded p-2"
                        @disabled(empty($actual_country_id))>
                    <option value="">Select City</option>
                    @if(!empty($actual_country_id))
                        @foreach(getCitiesByCountryId($actual_country_id) as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    @endif
                </select>
                <div wire:loading wire:target="actual_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>
                @error('actual_city_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Street</label>
                <input type="text" wire:model.live="actual_street" class="w-full border rounded p-2">
                @error('actual_street') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Building</label>
                <input type="text" wire:model.live="actual_building" class="w-full border rounded p-2">
                @error('actual_building') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Room</label>
                <input type="text" wire:model.live="actual_room" class="w-full border rounded p-2">
                @error('actual_room') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Status</label>
                <select wire:model.live="status" class="w-full border rounded p-2">
                    @foreach(\App\Enums\DriverStatus::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center gap-2 mt-2">
            <input type="checkbox" wire:model.live="is_active" class="mr-2">
            <span>Active</span>
        </div>

        {{-- License --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">License</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>License Number</label>
                <input type="text" wire:model.live="license_number" class="w-full border rounded p-2">
                @error('license_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Issued</label>
                <input type="date" wire:model.live="license_issued" class="w-full border rounded p-2">
                @error('license_issued') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>End</label>
                <input type="date" wire:model.live="license_end" class="w-full border rounded p-2">
                @error('license_end') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- 95 Code --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">95 Code</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Issued</label>
                <input type="date" wire:model.live="code95_issued" class="w-full border rounded p-2">
                @error('code95_issued') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>End</label>
                <input type="date" wire:model.live="code95_end" class="w-full border rounded p-2">
                @error('code95_end') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Permit --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Permit</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Issued</label>
                <input type="date" wire:model.live="permit_issued" class="w-full border rounded p-2">
                @error('permit_issued') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Expired</label>
                <input type="date" wire:model.live="permit_expired" class="w-full border rounded p-2">
                @error('permit_expired') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Medical --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Medical</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Issued</label>
                <input type="date" wire:model.live="medical_issued" class="w-full border rounded p-2">
                @error('medical_issued') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Expired</label>
                <input type="date" wire:model.live="medical_expired" class="w-full border rounded p-2">
                @error('medical_expired') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Medical OVP --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Medical OVP</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Passed</label>
                <input type="date" wire:model.live="medical_exam_passed" class="w-full border rounded p-2">
                @error('medical_exam_passed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Expired</label>
                <input type="date" wire:model.live="medical_exam_expired" class="w-full border rounded p-2">
                @error('medical_exam_expired') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Road Declaration --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Road Declaration</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Issued</label>
                <input type="date" wire:model.live="declaration_issued" class="w-full border rounded p-2">
                @error('declaration_issued') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Expired</label>
                <input type="date" wire:model.live="declaration_expired" class="w-full border rounded p-2">
                @error('declaration_expired') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Company --}}
        <div>
            <label>Company</label>
            <select wire:model.live="company" class="w-full border rounded p-2">
                <option value="">Select company</option>
                @foreach(config('companies') as $id => $company)
                    <option value="{{ $id }}">{{ $company['name'] }}</option>
                @endforeach
            </select>
            @error('company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Photos --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Photos</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Driver Photo</label>
                <input type="file" wire:model="photo" class="w-full border rounded p-2">
                @error('photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-32 mt-2 rounded">
                @endif
            </div>
            <div>
                <label>License Photo</label>
                <input type="file" wire:model="license_photo" class="w-full border rounded p-2">
                @error('license_photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                @if($license_photo)
                    <img src="{{ $license_photo->temporaryUrl() }}" class="w-32 mt-2 rounded">
                @endif
            </div>
            <div>
                <label>Medical Certificate</label>
                <input type="file" wire:model="medical_certificate_photo" class="w-full border rounded p-2">
                @error('medical_certificate_photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                @if($medical_certificate_photo)
                    <img src="{{ $medical_certificate_photo->temporaryUrl() }}" class="w-32 mt-2 rounded">
                @endif
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="px-4 py-2 bg-green-600 text-white rounded mt-4 hover:bg-green-700 transition">
            ✅ Create
        </button>
    </form>
</div>
