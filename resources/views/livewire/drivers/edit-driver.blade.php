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


    {{-- 🧾 Форма редактирования --}}
    <form wire:submit.prevent="save">
        <div class="bg-white shadow rounded-lg p-8 relative space-y-10">

            {{-- 🔄 Лоадер --}}
            <div wire:loading.flex
                 wire:target="save, declared_country_id, actual_country_id, photo, license_photo, medical_certificate_photo"
                 class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
                <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
            </div>

            {{-- 🧾 Заголовок --}}
            <div>
                <h2 class="text-3xl font-bold mb-2">✏️ Edit Driver</h2>
                <p class="text-gray-600">Update the driver's personal, contact, and document information below.</p>
            </div>

            {{-- ======================= 1️⃣ COMPANY ======================= --}}
            <section class="space-y-4">
                <h3 class="text-xl font-semibold border-b pb-2">🧭 Company Information</h3>
                <div>
                    <label class="block font-medium mb-1">Expeditor Company *</label>
                    <select wire:model="company" class="w-full border rounded px-3 py-2">
                        <option value="">Select company</option>
                        @foreach($companies as $id => $company)
                            <option value="{{ $id }}">
                                {{ $company['name'] }} — {{ $company['country'] ?? '' }}
                                {{ isset($company['city']) ? ', '.$company['city'] : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('company') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- ======================= 2️⃣ PERSONAL INFO ======================= --}}
            <section class="space-y-4">
                <h3 class="text-xl font-semibold border-b pb-2">👤 Personal Information</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">First Name *</label>
                        <input type="text" wire:model="first_name" class="w-full border rounded px-3 py-2">
                        @error('first_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Last Name *</label>
                        <input type="text" wire:model="last_name" class="w-full border rounded px-3 py-2">
                        @error('last_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium mb-1">Personal Code *</label>
                        <input type="text" wire:model="pers_code" class="w-full border rounded px-3 py-2">
                        @error('pers_code') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Citizenship *</label>
                        <select wire:model="citizenship_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Country</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                        @error('citizenship_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Phone *</label>
                        <input type="text" wire:model="phone" class="w-full border rounded px-3 py-2">
                        @error('phone') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-medium mb-1">Email *</label>
                    <input type="email" wire:model="email" class="w-full border rounded px-3 py-2">
                    @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- ======================= 3️⃣ ADDRESSES ======================= --}}
            <section class="space-y-10">
                <h3 class="text-xl font-semibold border-b pb-2">📍 Addresses</h3>

                {{-- Declared --}}
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-700">Declared Address</h4>
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
                        <input type="text" wire:model="declared_street" placeholder="Street" class="border rounded px-3 py-2 col-span-2">
                        <input type="text" wire:model="declared_building" placeholder="Building" class="border rounded px-3 py-2">
                        <input type="text" wire:model="declared_room" placeholder="Room" class="border rounded px-3 py-2">
                    </div>

                    <input type="text" wire:model="declared_postcode" placeholder="Post code" class="border rounded px-3 py-2 w-1/2">
                </div>

                {{-- Actual --}}
                <div class="space-y-4 border-t pt-4">
                    <h4 class="font-semibold text-gray-700">Actual Address</h4>
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
                        <input type="text" wire:model="actual_street" placeholder="Street" class="border rounded px-3 py-2 col-span-2">
                        <input type="text" wire:model="actual_building" placeholder="Building" class="border rounded px-3 py-2">
                        <input type="text" wire:model="actual_room" placeholder="Room" class="border rounded px-3 py-2">
                    </div>
                </div>
            </section>

            {{-- ======================= 4️⃣ DOCUMENTS ======================= --}}
            <section class="space-y-6">
                <h3 class="text-xl font-semibold border-b pb-2">📑 Driver Documents</h3>

                {{-- License --}}
                <div class="space-y-2">
                    <h4 class="font-semibold text-gray-700">Driver License</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <input type="text" wire:model="license_number" placeholder="License Number" class="border rounded px-3 py-2">
                        <input type="date" wire:model="license_issued" class="border rounded px-3 py-2">
                        <input type="date" wire:model="license_end" class="border rounded px-3 py-2">
                    </div>
                </div>

                {{-- Code 95 --}}
                <div class="space-y-2 border-t pt-4">
                    <h4 class="font-semibold text-gray-700">Code 95 Certificate</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="date" wire:model="code95_issued" class="border rounded px-3 py-2">
                        <input type="date" wire:model="code95_end" class="border rounded px-3 py-2">
                    </div>
                </div>

                {{-- Medical --}}
                <div class="space-y-2 border-t pt-4">
                    <h4 class="font-semibold text-gray-700">Medical Certificates</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="date" wire:model="medical_issued" class="border rounded px-3 py-2">
                        <input type="date" wire:model="medical_expired" class="border rounded px-3 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="date" wire:model="medical_exam_passed" class="border rounded px-3 py-2">
                        <input type="date" wire:model="medical_exam_expired" class="border rounded px-3 py-2">
                    </div>
                </div>

                {{-- Permit --}}
                <div class="space-y-2 border-t pt-4">
                    <h4 class="font-semibold text-gray-700">Work Permit</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="date" wire:model="permit_issued" class="border rounded px-3 py-2">
                        <input type="date" wire:model="permit_expired" class="border rounded px-3 py-2">
                    </div>
                </div>

                {{-- Declaration --}}
                <div class="space-y-2 border-t pt-4">
                    <h4 class="font-semibold text-gray-700">Driver Declaration</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="date" wire:model="declaration_issued" class="border rounded px-3 py-2">
                        <input type="date" wire:model="declaration_expired" class="border rounded px-3 py-2">
                    </div>
                </div>
            </section>

            {{-- ======================= 5️⃣ STATUS ======================= --}}
            <section class="space-y-3">
                <h3 class="text-xl font-semibold border-b pb-2">⚙️ Status & Activity</h3>
                <div class="grid grid-cols-2 gap-4 items-center">
                    <select wire:model="status" class="border rounded px-3 py-2">
                        <option value="on_work">🟢 On Work</option>
                        <option value="on_vacation">🌴 On Vacation</option>
                        <option value="fired">🔴 Fired</option>
                    </select>

                    <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300">
                        <span>Active</span>
                    </label>
                </div>
            </section>

            {{-- ======================= 6️⃣ PHOTOS ======================= --}}
            <section class="space-y-4">
                <h3 class="text-xl font-semibold border-b pb-2">📸 Driver Photos</h3>
                <div class="grid grid-cols-3 gap-6">
                    {{-- Driver photo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Driver Photo</label>
                        <input type="file" wire:model="photo" class="w-full border rounded p-2">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @elseif ($driver->photo)
                            <img src="{{ Storage::url($driver->photo) }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @endif
                    </div>

                    {{-- License photo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">License Photo</label>
                        <input type="file" wire:model="license_photo" class="w-full border rounded p-2">
                        @if ($license_photo)
                            <img src="{{ $license_photo->temporaryUrl() }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @elseif ($driver->license_photo)
                            <img src="{{ Storage::url($driver->license_photo) }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @endif
                    </div>

                    {{-- Medical photo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Medical Certificate</label>
                        <input type="file" wire:model="medical_certificate_photo" class="w-full border rounded p-2">
                        @if ($medical_certificate_photo)
                            <img src="{{ $medical_certificate_photo->temporaryUrl() }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @elseif ($driver->medical_certificate_photo)
                            <img src="{{ Storage::url($driver->medical_certificate_photo) }}" class="mt-2 rounded shadow w-32 h-32 object-cover">
                        @endif
                    </div>
                </div>
            </section>

            {{-- ======================= 7️⃣ ACTIONS ======================= --}}
            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('drivers.index') }}"
                   class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">Cancel</a>

                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    💾 Update Driver
                </button>
            </div>

        </div>
    </form>
</div>
