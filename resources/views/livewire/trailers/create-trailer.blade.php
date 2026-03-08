<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6 bg-gray-50 min-h-screen">

    {{-- ✅ Paziņojumi --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl">
            <strong>{{ __('app.trailer.create.validation') }}</strong>
            <ul class="list-disc ml-5 mt-1 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- 🧾 Forma --}}
    <form wire:submit.prevent="save"
          class="bg-white shadow-md rounded-2xl p-4 sm:p-6 space-y-10 relative"
          x-data="{ fileUploading: false, cancelTimeout: null }"
          x-on:livewire-upload-start="fileUploading = true; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-finish="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-error="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-cancel="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }">

        @include('components.upload-loading-overlay', ['targets' => 'save,tech_passport_photo'])

        {{-- Спиннер сразу при клике «Выбрать файл» --}}
        <div x-show="fileUploading"
             x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm"
             aria-live="polite">
            @include('components.upload-loading-spinner-box')
        </div>

        {{-- Virsraksts --}}
        <header>
            <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">➕ {{ __('app.trailer.create.title') }}</h2>
            <p class="text-gray-600 text-sm sm:text-base">{{ __('app.trailer.create.subtitle') }}</p>
        </header>


        {{-- ========================================================= --}}
        {{-- 1️⃣ PĀRSKATS --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📘 {{ __('app.trailers.col_brand') }}</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.trailers.col_brand') }} *</label>
                    <input type="text" wire:model.blur="brand"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('brand') border-red-500 @enderror">
                    @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.trailers.col_plate') }} *</label>
                    <input type="text" wire:model.blur="plate"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('plate') border-red-500 @enderror">
                    @error('plate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.year') }} *</label>
                    <input type="number" wire:model.blur="year"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('year') border-red-500 @enderror">
                    @error('year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Puspiekabes tips *</label>
                    <select wire:model="type_id"
                            class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('type_id') border-red-500 @enderror">
                        @foreach(config('trailer-types.types') as $id => $key)
                            <option value="{{ $id }}">
                                {{ config("trailer-types.icons.$key") }} {{ config("trailer-types.labels.$key", $key) }}
                            </option>
                        @endforeach
                    </select>
                    @error('type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        Noklusējums: {{ config('trailer-types.labels.' . (config('trailer-types.types.1') ?? 'cargo')) }}
                    </p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">VIN</label>
                    <input type="text" wire:model.blur="vin"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('vin') border-red-500 @enderror">
                    @error('vin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.company') }} *</label>
                    <select wire:model="company_id"
                            class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('company_id') border-red-500 @enderror">
                        <option value="">{{ __('app.driver.create.company_choose') }}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 2️⃣ TEHNISKĀ APSKATE --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📝 {{ __('app.truck.show.inspection_title') }}</h3>

            <div class="grid sm:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.issued') }} *</label>
                    <input type="date" wire:model.lazy="inspection_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('inspection_issued') border-red-500 @enderror">
                    @error('inspection_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.expires') }} *</label>
                    <input type="date" wire:model.lazy="inspection_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('inspection_expired') border-red-500 @enderror">
                    @error('inspection_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 3️⃣ APDROŠINĀŠANA --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">💼 Apdrošināšana</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.ins_company') }} *</label>
                    <input type="text" wire:model.blur="insurance_company"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('insurance_company') border-red-500 @enderror">
                    @error('insurance_company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.ins_number') }} *</label>
                    <input type="text" wire:model.blur="insurance_number"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('insurance_number') border-red-500 @enderror">
                    @error('insurance_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.issued') }} *</label>
                    <input type="date" wire:model.lazy="insurance_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('insurance_issued') border-red-500 @enderror">
                    @error('insurance_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.expires') }} *</label>
                    <input type="date" wire:model.lazy="insurance_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('insurance_expired') border-red-500 @enderror">
                    @error('insurance_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 4️⃣ TIR --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">🚛 TIR</h3>

            <div class="grid sm:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">TIR izsniegts</label>
                    <input type="date" wire:model.lazy="tir_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tir_issued') border-red-500 @enderror">
                    @error('tir_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">TIR der līdz</label>
                    <input type="date" wire:model.lazy="tir_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tir_expired') border-red-500 @enderror">
                    @error('tir_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 5️⃣ TEHNISKĀ PASE --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📗 {{ __('app.truck.show.tech_passport_title') }}</h3>

            <div>
                <label class="block mb-1 font-medium">{{ __('app.truck.show.tech_number') }} *</label>
                <input type="text" wire:model.blur="tech_passport_nr"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tech_passport_nr') border-red-500 @enderror">
                @error('tech_passport_nr') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.issued') }} *</label>
                    <input type="date" wire:model.lazy="tech_passport_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tech_passport_issued') border-red-500 @enderror">
                    @error('tech_passport_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.expires') }} *</label>
                    <input type="date" wire:model.lazy="tech_passport_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tech_passport_expired') border-red-500 @enderror">
                    @error('tech_passport_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Foto --}}
            <div>
                <label class="block mb-1 font-medium">{{ __('app.truck.show.tech_passport_title') }} foto</label>

                <input type="file" wire:model.live="tech_passport_photo" accept="image/*,application/pdf"
                       class="w-full border rounded-lg px-4 py-3 text-sm @error('tech_passport_photo') border-red-500 @enderror"
                       x-on:click="fileUploading = true; if(cancelTimeout) clearTimeout(cancelTimeout); cancelTimeout = setTimeout(() => { fileUploading = false; cancelTimeout = null }, 15000)">

                @error('tech_passport_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                @if($tech_passport_photo)
                    @if(strtolower($tech_passport_photo->getClientOriginalExtension() ?? '') === 'pdf')
                        <p class="mt-3 text-sm text-gray-600">📄 PDF</p>
                    @else
                        <img src="{{ $tech_passport_photo->temporaryUrl() }}"
                             class="mt-3 w-full h-48 object-contain rounded-xl shadow">
                    @endif
                @endif
            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- 6️⃣ DARBĪBAS — FIKSĒTA APAKŠĒJĀ JOSLA --}}
        {{-- ========================================================= --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4
                    flex justify-end gap-3 z-50">

            <a href="{{ route('trailers.index') }}"
               class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm transition active:scale-95">
                {{ __('app.trailer.create.cancel') }}
            </a>

            <button type="submit"
                    class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 active:scale-95 text-sm transition flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">

                <span wire:loading.remove wire:target="save">💾 {{ __('app.trailer.create.save') }}</span>

                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                    {{ __('app.trailer.create.saving') }}
                </span>

            </button>

        </div>

        {{-- Spacer to prevent content overlap --}}
        <div class="h-24"></div>

    </form>
</div>
