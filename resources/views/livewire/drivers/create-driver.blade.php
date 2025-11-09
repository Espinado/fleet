@php
    use Illuminate\Support\Str;
@endphp

<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6 bg-gray-50 min-h-screen">

    {{-- ✅ Уведомления --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- ❗️ Общие ошибки --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl shadow-sm">
            <strong>Validation errors:</strong>
            <ul class="list-disc ml-5 mt-1 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 🧾 Форма --}}
    <form wire:submit.prevent="save"
          class="bg-white shadow-md rounded-2xl p-4 sm:p-6 space-y-10 relative">

        {{-- 🔄 Глобальный лоадер --}}
        <div wire:loading.flex wire:target="save, photo, license_photo, medical_certificate_photo"
             class="absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-20 rounded-2xl">
            <div class="animate-spin h-12 w-12 border-4 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p class="text-blue-600 text-sm">Saving driver...</p>
        </div>

        {{-- Заголовок --}}
        <header>
            <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">➕ Add New Driver</h2>
            <p class="text-gray-600 text-sm sm:text-base">Fill in all required fields carefully.</p>
        </header>

        {{-- ======================= 1️⃣ COMPANY ======================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">🧭 Company</h3>
            <div>
                <label class="block font-medium mb-1">Expeditor Company *</label>
                <select wire:model="company"
                        class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Company</option>
                    @foreach($companies as $id => $comp)
                        <option value="{{ $id }}">{{ is_array($comp) ? $comp['name'] : $comp }}</option>
                    @endforeach
                </select>
                @error('company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- ======================= 2️⃣ PERSONAL INFO ======================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">👤 Personal Information</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1">First Name *</label>
                    <input type="text" wire:model="first_name"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1">Last Name *</label>
                    <input type="text" wire:model="last_name"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-1">Personal Code *</label>
                    <input type="text" wire:model="pers_code"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    @error('pers_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1">Citizenship *</label>
                    <select wire:model="citizenship_id"
                            class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Country</option>
                        @foreach($countries as $id => $country)
                            <option value="{{ $id }}">{{ is_array($country) ? $country['name'] : $country }}</option>
                        @endforeach
                    </select>
                    @error('citizenship_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1">Phone *</label>
                    <input type="text" wire:model="phone"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block mb-1">Email *</label>
                <input type="email" wire:model="email"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- ======================= 3️⃣ ADDRESSES ======================= --}}
        <section class="space-y-8">
            <h3 class="text-xl font-semibold border-b pb-2">📍 Addresses</h3>

            {{-- Declared --}}
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-700">Declared Address</h4>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="relative">
                        <select wire:model.live="declared_country_id"
                                class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Country</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ is_array($country) ? $country['name'] : $country }}</option>
                            @endforeach
                        </select>
                        <div wire:loading wire:target="declared_country_id"
                             class="absolute right-3 top-3">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-400 border-t-transparent rounded-full"></div>
                        </div>
                    </div>

                    <div class="relative">
                        <select wire:model="declared_city_id"
                                class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select City</option>
                            @foreach($declaredCities as $id => $city)
                                <option value="{{ $id }}">{{ is_array($city) ? $city['name'] : $city }}</option>
                            @endforeach
                        </select>
                        <div wire:loading wire:target="declared_city_id"
                             class="absolute right-3 top-3">
                            <div class="animate-spin h-4 w-4 border-2 border-green-400 border-t-transparent rounded-full"></div>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-4 gap-4">
                    <input type="text" wire:model="declared_street" placeholder="Street"
                           class="border rounded-lg px-4 py-3 col-span-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <input type="text" wire:model="declared_building" placeholder="Building"
                           class="border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    <input type="text" wire:model="declared_room" placeholder="Room"
                           class="border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <input type="text" wire:model="declared_postcode" placeholder="Post code"
                       class="border rounded-lg px-4 py-3 w-full sm:w-1/2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Actual --}}
            <div class="space-y-4 border-t pt-4">
                <h4 class="font-semibold text-gray-700">Actual Address</h4>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="relative">
                        <select wire:model.live="actual_country_id"
                                class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Country</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ is_array($country) ? $country['name'] : $country }}</option>
                            @endforeach
                        </select>
                        <div wire:loading wire:target="actual_country_id"
                             class="absolute right-3 top-3">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-400 border-t-transparent rounded-full"></div>
                        </div>
                    </div>

                    <div class="relative">
                        <select wire:model="actual_city_id"
                                class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select City</option>
                            @foreach($actualCities as $id => $city)
                                <option value="{{ $id }}">{{ is_array($city) ? $city['name'] : $city }}</option>
                            @endforeach
                        </select>
                        <div wire:loading wire:target="actual_city_id"
                             class="absolute right-3 top-3">
                            <div class="animate-spin h-4 w-4 border-2 border-green-400 border-t-transparent rounded-full"></div>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-4 gap-4">
                    <input type="text" wire:model="actual_street" placeholder="Street"
                           class="border rounded-lg px-4 py-3 col-span-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <input type="text" wire:model="actual_building" placeholder="Building"
                           class="border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    <input type="text" wire:model="actual_room" placeholder="Room"
                           class="border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <input type="text" wire:model="actual_postcode" placeholder="Post code"
                       class="border rounded-lg px-4 py-3 w-full sm:w-1/2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </section>

        {{-- ======================= 4️⃣ DOCUMENTS ======================= --}}
      <section class="space-y-6">
    <h3 class="text-xl font-semibold border-b pb-2">📑 Driver Documents</h3>

    @foreach([
        ['Driver License', ['license_number','license_issued','license_end']],
        ['Code 95 Certificate', ['code95_issued','code95_end']],
        ['Work Permit', ['permit_issued','permit_expired']],
        ['Medical Certificates CSDD', ['medical_issued','medical_expired']],
        ['Medical Certificates OVP', ['medical_exam_passed','medical_exam_expired']],
        ['Driver Declaration', ['declaration_issued','declaration_expired']]
    ] as [$title, $fields])
        <div class="border-t pt-4">
            <h4 class="font-semibold text-gray-700 mb-2">{{ $title }}</h4>
            <div class="grid sm:grid-cols-{{ count($fields) }} gap-4">
                @foreach($fields as $field)
                    @php
                        $isDate = Str::contains($field, ['issued', 'expired', 'end', 'passed']);
                    @endphp
                    <div>
                        <input type="{{ $isDate ? 'date' : 'text' }}"
                               wire:model="{{ $field }}"
                               class="border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 w-full">
                        @error($field)
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</section>


        {{-- ======================= 5️⃣ PHOTOS ======================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📸 Driver Photos</h3>
            <div class="grid sm:grid-cols-3 gap-6">
                @foreach([
                    ['photo','Driver Photo'],
                    ['license_photo','License Photo'],
                    ['medical_certificate_photo','Medical Certificate'],
                ] as [$field,$label])
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                        <input type="file" wire:model="{{ $field }}" accept="image/*,application/pdf" 
                               class="w-full border rounded-lg p-2 text-sm">
                        @if ($$field)
                            <img src="{{ $$field->temporaryUrl() }}"
                                 class="mt-2 w-full h-48 object-cover rounded-lg shadow">
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ======================= 6️⃣ ACTIONS ======================= --}}
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t sticky bottom-0 bg-white pb-4">
            <a href="{{ route('drivers.index') }}"
               class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm transition text-center active:scale-95">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 text-sm transition flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">💾 Save Driver</span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                    Saving...
                </span>
            </button>
        </div>

    </form>
</div>
