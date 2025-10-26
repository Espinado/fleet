<div class="p-6 max-w-5xl mx-auto">

    {{-- ✅ Уведомления --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 relative">

        {{-- 🔄 Лоадер --}}
        <div wire:loading.flex wire:target="save, declared_country_id, actual_country_id, photo, license_photo, medical_certificate_photo"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
            <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <h2 class="text-2xl font-semibold mb-6">➕ Add New Driver</h2>

        <form wire:submit.prevent="save" class="space-y-8">

            {{-- 1️⃣ Company --}}
            <div>
                <label class="block font-medium mb-1">Expeditor Company *</label>
                <select wire:model="company" class="w-full border rounded px-3 py-2">
                    <option value="">Select company</option>
                    @foreach($companies as $id => $company)
                        <option value="{{ $id }}">
                            {{ $company['name'] }}
                            — {{ $company['country'] ?? '' }}{{ isset($company['city']) ? ', '.$company['city'] : '' }}
                        </option>
                    @endforeach
                </select>
                @error('company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- 2️⃣ Personal Info --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">First Name *</label>
                    <input type="text" wire:model="first_name" class="w-full border rounded px-3 py-2">
                    @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium mb-1">Last Name *</label>
                    <input type="text" wire:model="last_name" class="w-full border rounded px-3 py-2">
                    @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium mb-1">Personal Code *</label>
                    <input type="text" wire:model="pers_code" class="w-full border rounded px-3 py-2">
                    @error('pers_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium mb-1">Citizenship *</label>
                    <select wire:model="citizenship" class="w-full border rounded px-3 py-2">
                        <option value="">Select Country</option>
                        @foreach($countries as $id => $country)
                            <option value="{{ $id }}">{{ $country['name'] }}</option>
                        @endforeach
                    </select>
                    @error('citizenship') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium mb-1">Phone *</label>
                    <input type="text" wire:model="phone" class="w-full border rounded px-3 py-2">
                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block font-medium mb-1">Email *</label>
                <input type="email" wire:model="email" class="w-full border rounded px-3 py-2">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- 3️⃣ Declared Address --}}
            <div class="border-t pt-4 space-y-4">
                <h3 class="text-lg font-semibold">Declared Address</h3>

                <div class="grid grid-cols-2 gap-4">
                    <select wire:model.live="declared_country_id" class="border rounded px-3 py-2">
                        <option value="">Select Country</option>
                        @foreach($countries as $id => $country)
                            <option value="{{ $id }}">{{ $country['name'] }}</option>
                        @endforeach
                    </select>

                    <select wire:model="declared_city_id" class="border rounded px-3 py-2">
                        <option value="">Select City</option>
                        @foreach($declaredCities as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <input type="text" wire:model="declared_street" placeholder="Street"
                           class="border rounded px-3 py-2 col-span-2">
                    <input type="text" wire:model="declared_building" placeholder="Building"
                           class="border rounded px-3 py-2">
                    <input type="text" wire:model="declared_room" placeholder="Room"
                           class="border rounded px-3 py-2">
                </div>
                <input type="text" wire:model="declared_postcode" placeholder="Post code"
                       class="border rounded px-3 py-2 w-1/2">
            </div>

            {{-- 4️⃣ Actual Address --}}
            <div class="border-t pt-4 space-y-4">
                <h3 class="text-lg font-semibold">Actual Address</h3>

                <div class="grid grid-cols-2 gap-4">
                    <select wire:model.live="actual_country_id" class="border rounded px-3 py-2">
                        <option value="">Select Country</option>
                        @foreach($countries as $id => $country)
                            <option value="{{ $id }}">{{ $country['name'] }}</option>
                        @endforeach
                    </select>

                    <select wire:model="actual_city_id" class="border rounded px-3 py-2">
                        <option value="">Select City</option>
                        @foreach($actualCities as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <input type="text" wire:model="actual_street" placeholder="Street"
                           class="border rounded px-3 py-2 col-span-2">
                    <input type="text" wire:model="actual_building" placeholder="Building"
                           class="border rounded px-3 py-2">
                    <input type="text" wire:model="actual_room" placeholder="Room"
                           class="border rounded px-3 py-2">
                </div>
            </div>

            {{-- 5️⃣ Documents --}}
            <div class="border-t pt-4 space-y-3">
                <h3 class="text-lg font-semibold">Documents</h3>

                <div class="grid grid-cols-3 gap-4">
                    <input type="text" wire:model="license_number" placeholder="License Number"
                           class="border rounded px-3 py-2">
                    <input type="date" wire:model="license_issued" class="border rounded px-3 py-2">
                    <input type="date" wire:model="license_end" class="border rounded px-3 py-2">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <label>95 Code Issued <input type="date" wire:model="code95_issued" class="border rounded px-3 py-2 w-full"></label>
                    <label>95 Code Expired <input type="date" wire:model="code95_end" class="border rounded px-3 py-2 w-full"></label>
                    <label>Medical Expired <input type="date" wire:model="medical_expired" class="border rounded px-3 py-2 w-full"></label>
                </div>
            </div>

            {{-- 6️⃣ Photos --}}
            <div class="border-t pt-4 space-y-4">
                <h3 class="text-lg font-semibold">Driver Photos</h3>

                <div class="grid grid-cols-3 gap-6">
                    {{-- Driver photo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Driver Photo</label>
                        <input type="file" wire:model="photo" class="w-full border rounded p-2">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @endif
                    </div>

                    {{-- License photo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">License Photo</label>
                        <input type="file" wire:model="license_photo" class="w-full border rounded p-2">
                        @if ($license_photo)
                            <img src="{{ $license_photo->temporaryUrl() }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @endif
                    </div>

                    {{-- Medical certificate photo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Medical Certificate</label>
                        <input type="file" wire:model="medical_certificate_photo" class="w-full border rounded p-2">
                        @if ($medical_certificate_photo)
                            <img src="{{ $medical_certificate_photo->temporaryUrl() }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @endif
                    </div>
                </div>
            </div>

            {{-- 7️⃣ Actions --}}
            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('drivers.index') }}"
                   class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    💾 Save Driver
                </button>
            </div>
        </form>
    </div>
</div>
