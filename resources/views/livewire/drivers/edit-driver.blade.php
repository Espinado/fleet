@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="min-h-screen bg-gray-50 pb-safe">
    {{-- PWA: sticky top bar (mobile) --}}
    <div class="sticky top-0 z-20 bg-white/95 border-b border-gray-200 backdrop-blur md:bg-transparent md:border-0 md:static">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3 md:py-0 md:px-0 md:mb-4">
            <a href="{{ route('drivers.index') }}"
               class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 md:w-auto md:h-auto md:rounded-lg md:px-3 md:py-2 md:text-sm">
                <span class="md:hidden">←</span>
                <span class="hidden md:inline">← {{ __('app.driver.create.back') }}</span>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 truncate flex-1 text-center md:text-left md:flex-none md:text-2xl">
                ✏️ {{ __('app.driver.edit.title') }}
            </h1>
            <div class="w-10 md:w-auto md:invisible md:h-0"></div>
        </div>
    </div>

    <div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6">

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

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl shadow-sm">
            <strong>{{ __('app.driver.edit.validation') }}</strong>
            <ul class="list-disc ml-5 mt-1 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit.prevent="save"
          class="bg-white shadow-md rounded-2xl p-4 sm:p-6 space-y-6 md:space-y-10 relative"
          x-data="{ fileUploading: false, cancelTimeout: null }"
          x-on:livewire-upload-start="fileUploading = true; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-finish="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-error="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-cancel="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }">

        @include('components.upload-loading-overlay', ['targets' => 'save,photo,license_photo,medical_certificate_photo'])

        {{-- Спиннер сразу при клике «Выбрать файл» --}}
        <div x-show="fileUploading"
             x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm"
             aria-live="polite">
            @include('components.upload-loading-spinner-box')
        </div>

        <header class="hidden md:block">
            <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">✏️ {{ __('app.driver.edit.title') }}</h2>
            <p class="text-gray-600 text-sm sm:text-base">{{ __('app.driver.edit.subtitle') }}</p>
        </header>

        {{-- 1. Kompānija --}}
        <section x-data="{ open: true }" class="border border-gray-200 rounded-xl overflow-hidden md:border-0 md:rounded-none">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left md:bg-transparent md:py-0 md:pb-2 md:border-b md:border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">🧭 {{ __('app.driver.create.company') }}</h3>
                <span class="text-gray-500 md:hidden" x-text="open ? '▼' : '▶'"></span>
            </button>
            <div x-show="open" x-collapse class="px-0 pt-4 md:pt-4 md:!block space-y-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('app.driver.create.company') }}</label>
                    <select wire:model="company_id"
                            class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        <option value="">{{ __('app.driver.create.company_choose') }}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- 2. Personīgā informācija --}}
        <section x-data="{ open: true }" class="border border-gray-200 rounded-xl overflow-hidden md:border-0 md:rounded-none">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left md:bg-transparent md:py-0 md:pb-2 md:border-b md:border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">👤 {{ __('app.driver.create.personal') }}</h3>
                <span class="text-gray-500 md:hidden" x-text="open ? '▼' : '▶'"></span>
            </button>
            <div x-show="open" x-collapse class="px-0 pt-4 md:pt-4 md:!block space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1">{{ __('app.driver.create.first_name') }}</label>
                        <input type="text" wire:model="first_name"
                               class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1">{{ __('app.driver.create.last_name') }}</label>
                        <input type="text" wire:model="last_name"
                               class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1">{{ __('app.driver.create.pers_code') }}</label>
                        <input type="text" wire:model="pers_code"
                               class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        @error('pers_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1">{{ __('app.driver.create.citizenship') }}</label>
                        <select wire:model="citizenship_id"
                                class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                            <option value="">{{ __('app.driver.create.country_choose') }}</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ is_array($country) ? $country['name'] : $country }}</option>
                            @endforeach
                        </select>
                        @error('citizenship_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1">{{ __('app.driver.create.phone') }}</label>
                        <input type="tel" wire:model="phone"
                               class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block mb-1">{{ __('app.driver.create.email') }}</label>
                    <input type="email" wire:model="email" inputmode="email"
                           class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- 3. Adreses --}}
        <section x-data="{ open: false }" class="border border-gray-200 rounded-xl overflow-hidden md:border-0 md:rounded-none">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left md:bg-transparent md:py-0 md:pb-2 md:border-b md:border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📍 {{ __('app.driver.create.addresses') }}</h3>
                <span class="text-gray-500 md:hidden" x-text="open ? '▼' : '▶'"></span>
            </button>
            <div x-show="open" x-collapse class="px-0 pt-4 md:pt-4 md:!block space-y-8">
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-700">{{ __('app.driver.create.declared') }}</h4>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="relative">
                            <select wire:model.live="declared_country_id"
                                    class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                                <option value="">{{ __('app.driver.create.country_choose') }}</option>
                                @foreach($countries as $id => $country)
                                    <option value="{{ $id }}">{{ is_array($country) ? $country['name'] : $country }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="declared_country_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-blue-400 border-t-transparent rounded-full"></div>
                            </div>
                        </div>
                        <div class="relative">
                            <select wire:model="declared_city_id"
                                    class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                                <option value="">{{ __('app.driver.create.city_choose') }}</option>
                                @foreach($declaredCities as $id => $city)
                                    <option value="{{ $id }}">{{ is_array($city) ? $city['name'] : $city }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="declared_city_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-green-400 border-t-transparent rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-4 gap-4">
                        <input type="text" wire:model="declared_street" placeholder="{{ __('app.driver.create.street') }}"
                               class="border rounded-xl px-4 py-3.5 col-span-2 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        <input type="text" wire:model="declared_building" placeholder="{{ __('app.driver.create.building') }}"
                               class="border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        <input type="text" wire:model="declared_room" placeholder="{{ __('app.driver.create.room') }}"
                               class="border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                    </div>
                    <input type="text" wire:model="declared_postcode" placeholder="{{ __('app.driver.create.postcode') }}"
                           class="border rounded-xl px-4 py-3.5 w-full sm:w-1/2 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                </div>
                <div class="space-y-4 border-t pt-4">
                    <h4 class="font-semibold text-gray-700">{{ __('app.driver.create.actual') }}</h4>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="relative">
                            <select wire:model.live="actual_country_id"
                                    class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                                <option value="">{{ __('app.driver.create.country_choose') }}</option>
                                @foreach($countries as $id => $country)
                                    <option value="{{ $id }}">{{ is_array($country) ? $country['name'] : $country }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="actual_country_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-blue-400 border-t-transparent rounded-full"></div>
                            </div>
                        </div>
                        <div class="relative">
                            <select wire:model="actual_city_id"
                                    class="w-full border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                                <option value="">{{ __('app.driver.create.city_choose') }}</option>
                                @foreach($actualCities as $id => $city)
                                    <option value="{{ $id }}">{{ is_array($city) ? $city['name'] : $city }}</option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="actual_city_id" class="absolute right-3 top-3">
                                <div class="animate-spin h-4 w-4 border-2 border-green-400 border-t-transparent rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-4 gap-4">
                        <input type="text" wire:model="actual_street" placeholder="{{ __('app.driver.create.street') }}"
                               class="border rounded-xl px-4 py-3.5 col-span-2 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        <input type="text" wire:model="actual_building" placeholder="{{ __('app.driver.create.building') }}"
                               class="border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                        <input type="text" wire:model="actual_room" placeholder="{{ __('app.driver.create.room') }}"
                               class="border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                    </div>
                    <input type="text" wire:model="actual_postcode" placeholder="{{ __('app.driver.create.postcode') }}"
                           class="border rounded-xl px-4 py-3.5 w-full sm:w-1/2 text-base focus:ring-2 focus:ring-blue-500 min-h-[48px]">
                </div>
            </div>
        </section>

        {{-- 4. Dokumenti --}}
        <section x-data="{ open: false }" class="border border-gray-200 rounded-xl overflow-hidden md:border-0 md:rounded-none">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left md:bg-transparent md:py-0 md:pb-2 md:border-b md:border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📑 {{ __('app.driver.create.docs') }}</h3>
                <span class="text-gray-500 md:hidden" x-text="open ? '▼' : '▶'"></span>
            </button>
            <div x-show="open" x-collapse class="px-0 pt-4 md:pt-4 md:!block space-y-6">
                @foreach([
                    [__('app.driver.docs.license'), ['license_number','license_issued','license_end']],
                    [__('app.driver.docs.code95'), ['code95_issued','code95_end']],
                    [__('app.driver.docs.permit'), ['permit_issued','permit_expired']],
                    [__('app.driver.docs.med_csdD'), ['medical_issued','medical_expired']],
                    [__('app.driver.docs.med_ovp'), ['medical_exam_passed','medical_exam_expired']],
                    [__('app.driver.docs.declaration'), ['declaration_issued','declaration_expired']]
                ] as [$title, $fields])
                    <div class="border-t pt-4 first:border-t-0 first:pt-0">
                        <h4 class="font-semibold text-gray-700 mb-2">{{ $title }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($fields as $field)
                                @php $isDate = Str::contains($field, ['issued','expired','end','passed']); @endphp
                                <div>
                                    <input type="{{ $isDate ? 'date' : 'text' }}"
                                           wire:model="{{ $field }}"
                                           class="border rounded-xl px-4 py-3.5 text-base focus:ring-2 focus:ring-blue-500 w-full min-h-[48px]">
                                    @error($field)
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 5. Fotogrāfijas --}}
        <section x-data="{ open: false }" class="border border-gray-200 rounded-xl overflow-hidden md:border-0 md:rounded-none">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left md:bg-transparent md:py-0 md:pb-2 md:border-b md:border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📸 {{ __('app.driver.create.photos') }}</h3>
                <span class="text-gray-500 md:hidden" x-text="open ? '▼' : '▶'"></span>
            </button>
            <div x-show="open" x-collapse class="px-0 pt-4 md:pt-4 md:!block space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach([
                        ['photo', __('app.driver.create.photo_driver')],
                        ['license_photo', __('app.driver.create.photo_license')],
                        ['medical_certificate_photo', __('app.driver.create.photo_med')],
                    ] as [$field, $label])
                        @php
                            $fileValue = $field === 'photo' ? $photo : ($field === 'license_photo' ? $license_photo : $medical_certificate_photo);
                            $existingPath = $driver->$field ?? null;
                            $isPdf = $fileValue ? (strtolower($fileValue->getClientOriginalExtension() ?? '') === 'pdf') : (($existingPath && str_ends_with(strtolower($existingPath), '.pdf')));
                        @endphp
                        <div x-data="{ previewUrl: null, isPdf: false }">
                            <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                            <input type="file" wire:model.live="{{ $field }}" accept="image/*,application/pdf"
                                   class="w-full border rounded-xl p-3 text-base min-h-[48px] file:mr-2 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-sm"
                                   x-on:click="fileUploading = true; if(cancelTimeout) clearTimeout(cancelTimeout); cancelTimeout = setTimeout(() => { fileUploading = false; cancelTimeout = null }, 15000)"
                                   x-on:change="const f = $event.target.files[0]; previewUrl = null; isPdf = false; if (f) { isPdf = (f.type === 'application/pdf' || (f.name || '').toLowerCase().endsWith('.pdf')); if (!isPdf) { const r = new FileReader(); r.onload = () => { previewUrl = r.result }; r.readAsDataURL(f) } }">
                            {{-- Мгновенное превью (FileReader) — сразу при выборе файла --}}
                            <template x-if="previewUrl">
                                <img :src="previewUrl" class="mt-2 w-full h-40 sm:h-48 object-cover rounded-xl shadow" alt="">
                            </template>
                            <template x-if="!previewUrl && isPdf">
                                <p class="mt-2 text-sm text-gray-600">📄 PDF</p>
                            </template>
                            @if($fileValue && !$isPdf)
                                <img src="{{ $fileValue->temporaryUrl() }}"
                                     class="mt-2 w-full h-40 sm:h-48 object-cover rounded-xl shadow" alt=""
                                     x-show="!previewUrl">
                            @endif
                            @if($fileValue && $isPdf)
                                <p class="mt-2 text-sm text-gray-600" x-show="!previewUrl">📄 PDF</p>
                            @endif
                            @if(!$fileValue && $existingPath && Storage::disk('public')->exists($existingPath))
                                @if($isPdf)
                                    <a href="{{ asset('storage/'.$existingPath) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm text-blue-600" x-show="!previewUrl">📄 PDF</a>
                                @else
                                    <img src="{{ asset('storage/'.$existingPath) }}"
                                         class="mt-2 w-full h-40 sm:h-48 object-cover rounded-xl shadow" alt=""
                                         x-show="!previewUrl">
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 6. Darbības --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t sticky bottom-0 bg-white pb-4 pb-safe">
            <a href="{{ route('drivers.index') }}"
               class="min-h-[48px] flex items-center justify-center px-4 py-3 bg-gray-200 rounded-xl hover:bg-gray-300 text-base transition text-center active:scale-[0.98]">
                {{ __('app.driver.edit.cancel') }}
            </a>
            <button type="submit"
                    class="min-h-[48px] px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 active:scale-[0.98] text-base font-medium transition flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">💾 {{ __('app.driver.edit.save') }}</span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                    {{ __('app.driver.edit.saving') }}
                </span>
            </button>
        </div>
    </form>
    </div>
</div>
