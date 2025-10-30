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

    {{-- ❗️ Общий блок ошибок (если нужно показать все сразу) --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded mb-6">
            <strong>Validation errors:</strong>
            <ul class="list-disc ml-5 mt-1 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- 🧾 Форма Livewire --}}
    <form wire:submit.prevent="save">
        <div class="bg-white shadow rounded-lg p-8 relative space-y-10">

            {{-- 🔄 ГЛОБАЛЬНЫЙ ЛОАДЕР при сохранении --}}
            <div wire:loading.flex
                 wire:target="save, photo, license_photo, medical_certificate_photo"
                 class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
                <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
            </div>

            {{-- 🧾 Заголовок --}}
            <div>
                <h2 class="text-3xl font-bold mb-2">➕ Add New Driver</h2>
                <p class="text-gray-600">Fill out the driver's personal, contact, and document information below.</p>
            </div>

            {{-- ======================= 1️⃣ COMPANY ======================= --}}
            <section class="space-y-4">
                <h3 class="text-xl font-semibold border-b pb-2">🧭 Company Information</h3>
                <div>
                    <label class="block font-medium mb-1">Expeditor Company *</label>
                    <select wire:model="company" class="w-full border rounded px-3 py-2">
                        <option value="">Select company</option>
                        @foreach($companies as $id => $company)
                            <option value="{{ $id }}">{{ $company['name'] }}</option>
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
                        <label class="block mb-1">First Name *</label>
                        <input type="text" wire:model="first_name" class="w-full border rounded px-3 py-2">
                        @error('first_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1">Last Name *</label>
                        <input type="text" wire:model="last_name" class="w-full border rounded px-3 py-2">
                        @error('last_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1">Personal Code *</label>
                        <input type="text" wire:model="pers_code" class="w-full border rounded px-3 py-2">
                        @error('pers_code') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1">Citizenship *</label>
                        <select wire:model="citizenship_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Country</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                        @error('citizenship_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1">Phone *</label>
                        <input type="text" wire:model="phone" class="w-full border rounded px-3 py-2">
                        @error('phone') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-1">Email *</label>
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
                    <div class="grid grid-cols-2 gap-4 items-center">
                        <div class="relative">
                            <select wire:model.live="declared_country_id" class="border rounded px-3 py-2 w-full">
                                <option value="">Select Country</option>
                                @foreach($countries as $id => $country)
                                    <option value="{{ $id }}">{{ $country['name'] }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="declared_country_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-blue-400 border-t-transparent rounded-full"></div>
                            </div>
                            @error('declared_country_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="relative">
                            <select wire:model="declared_city_id" class="border rounded px-3 py-2 w-full">
                                <option value="">Select City</option>
                                @foreach($declaredCities as $id => $city)
                                    <option value="{{ $id }}">{{ $city['name'] }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="declared_city_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-green-400 border-t-transparent rounded-full"></div>
                            </div>
                            @error('declared_city_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-4">
                        <div class="col-span-2">
                            <input type="text" wire:model="declared_street" placeholder="Street" class="border rounded px-3 py-2 w-full">
                            @error('declared_street') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="text" wire:model="declared_building" placeholder="Building" class="border rounded px-3 py-2 w-full">
                            @error('declared_building') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="text" wire:model="declared_room" placeholder="Room" class="border rounded px-3 py-2 w-full">
                            @error('declared_room') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <input type="text" wire:model="declared_postcode" placeholder="Post code" class="border rounded px-3 py-2 w-1/2">
                        @error('declared_postcode') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Actual --}}
                <div class="space-y-4 border-t pt-4">
                    <h4 class="font-semibold text-gray-700">Actual Address</h4>
                    <div class="grid grid-cols-2 gap-4 items-center">
                        <div class="relative">
                            <select wire:model.live="actual_country_id" class="border rounded px-3 py-2 w-full">
                                <option value="">Select Country</option>
                                @foreach($countries as $id => $country)
                                    <option value="{{ $id }}">{{ $country['name'] }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="actual_country_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-blue-400 border-t-transparent rounded-full"></div>
                            </div>
                            @error('actual_country_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="relative">
                            <select wire:model="actual_city_id" class="border rounded px-3 py-2 w-full">
                                <option value="">Select City</option>
                                @foreach($actualCities as $id => $city)
                                    <option value="{{ $id }}">{{ $city['name'] }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="actual_city_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-green-400 border-t-transparent rounded-full"></div>
                            </div>
                            @error('actual_city_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <input type="text" wire:model="actual_street" placeholder="Street" class="border rounded px-3 py-2 w-full">
                            @error('actual_street') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="text" wire:model="actual_building" placeholder="Building" class="border rounded px-3 py-2 w-full">
                            @error('actual_building') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="text" wire:model="actual_room" placeholder="Room" class="border rounded px-3 py-2 w-full">
                            @error('actual_room') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>


            {{-- ======================= 4️⃣ DOCUMENTS ======================= --}}
            <section class="space-y-6">
                <h3 class="text-xl font-semibold border-b pb-2">📑 Driver Documents</h3>

                @foreach([
                    ['license_number','License Number'],
                    ['license_issued','License Issued'],
                    ['license_end','License Expires'],
                    ['code95_issued','Code 95 Issued'],
                    ['code95_end','Code 95 Expires'],
                    ['medical_issued','Medical Issued'],
                    ['medical_expired','Medical Expires'],
                    ['medical_exam_passed','Medical Exam Passed'],
                    ['medical_exam_expired','Medical Exam Expires'],
                    ['permit_issued','Permit Issued'],
                    ['permit_expired','Permit Expires'],
                    ['declaration_issued','Declaration Issued'],
                    ['declaration_expired','Declaration Expires'],
                ] as $field)
                    <div>
                        <input type="{{ str_contains($field[0],'number') ? 'text' : 'date' }}"
                               wire:model="{{ $field[0] }}"
                               placeholder="{{ $field[1] }}"
                               class="border rounded px-3 py-2 w-full">
                        @error($field[0]) <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </section>


            {{-- ======================= 5️⃣ PHOTOS ======================= --}}
            <section class="space-y-4">
                <h3 class="text-xl font-semibold border-b pb-2">📸 Driver Photos</h3>
                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <label>Driver Photo</label>
                        <input type="file" wire:model="photo" class="w-full border rounded p-2">
                        @error('photo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="mt-2 w-32 h-32 object-cover rounded shadow">
                        @endif
                    </div>
                    <div>
                        <label>License Photo</label>
                        <input type="file" wire:model="license_photo" class="w-full border rounded p-2">
                        @error('license_photo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        @if ($license_photo)
                            <img src="{{ $license_photo->temporaryUrl() }}" class="mt-2 w-32 h-32 object-cover rounded shadow">
                        @endif
                    </div>
                    <div>
                        <label>Medical Certificate</label>
                        <input type="file" wire:model="medical_certificate_photo" class="w-full border rounded p-2">
                        @error('medical_certificate_photo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        @if ($medical_certificate_photo)
                            <img src="{{ $medical_certificate_photo->temporaryUrl() }}" class="mt-2 w-32 h-32 object-cover rounded shadow">
                        @endif
                    </div>
                </div>
            </section>


            {{-- ======================= 6️⃣ ACTIONS ======================= --}}
            <div class="flex flex-col items-end gap-2 pt-6 border-t">
                <div class="flex gap-3">
                    <a href="{{ route('drivers.index') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        💾 Save Driver
                    </button>
                </div>
                <div wire:loading wire:target="save" class="text-blue-500 text-sm">
                    Saving driver data, please wait...
                </div>
            </div>

        </div>
    </form>
</div>
