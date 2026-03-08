<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6 bg-gray-50 min-h-screen">

    {{-- ✅ Paziņojumi --}}
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

    {{-- ❗️ Kļūdas --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl shadow-sm">
            <strong>{{ __('app.truck.create.validation') }}</strong>
            <ul class="list-disc ml-5 mt-1 text-sm space-y-1">
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
            <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">🚚 {{ __('app.truck.create.title') }}</h2>
            <p class="text-gray-600 text-sm sm:text-base">{{ __('app.truck.create.subtitle') }}</p>
        </header>

        {{-- ========================================================= --}}
        {{-- 1️⃣ PĀRSKATS --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📘 {{ __('app.trucks.col_brand') }}</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Marka --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.trucks.col_brand') }} *</label>
                    <input type="text" wire:model.blur="brand"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('brand') border-red-500 @enderror">
                    @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Modelis --}}
                <div>
                    <label class="block mb-1 font-medium">Modelis *</label>
                    <input type="text" wire:model.blur="model"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('model') border-red-500 @enderror">
                    @error('model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                {{-- Numurzīme --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.trucks.col_plate') }} *</label>
                    <input type="text" wire:model.blur="plate"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('plate') border-red-500 @enderror">
                    @error('plate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Gads --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.year') }} *</label>
                    <input type="number" wire:model.blur="year"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('year') border-red-500 @enderror">
                    @error('year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- VIN --}}
                <div>
                    <label class="block mb-1 font-medium">VIN *</label>
                    <input type="text" wire:model.blur="vin"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('vin') border-red-500 @enderror">
                    @error('vin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
{{-- 1️⃣5️⃣ LICENCE (reģistrācijas dokumenti) --}}
{{-- ========================================================= --}}
<section class="space-y-4">
    <h3 class="text-xl font-semibold border-b pb-2">🪪 Licence</h3>

    <div class="grid sm:grid-cols-3 gap-4">
        {{-- License number --}}
        <div class="sm:col-span-1">
            <label class="block mb-1 font-medium">Licences numurs</label>
            <input type="text" wire:model.blur="license_number"
                   placeholder="piem., LV-123456"
                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('license_number') border-red-500 @enderror">
            @error('license_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-500 mt-1">Neobligāti. Reģistrācijas / licences dokumenta numurs.</p>
        </div>

        {{-- License issued --}}
        <div>
            <label class="block mb-1 font-medium">{{ __('app.truck.show.issued') }}</label>
            <input type="date" wire:model.live="license_issued"
                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('license_issued') border-red-500 @enderror">
            @error('license_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- License expired --}}
        <div>
            <label class="block mb-1 font-medium">{{ __('app.truck.show.expires') }}</label>
            <input type="date" wire:model.live="license_expired"
                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('license_expired') border-red-500 @enderror">
            @error('license_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- small note / validation hint --}}
    <div class="text-sm text-gray-600 bg-gray-50 border rounded-xl p-3">
        <div class="flex items-start gap-2">
            <span class="mt-0.5">💡</span>
            <div>
                Ja aizpildāt datumus, pārliecinieties, ka <span class="font-medium">{{ __('app.truck.show.expires') }}</span> ir pēc <span class="font-medium">{{ __('app.truck.show.issued') }}</span>.
                Visi lauki ir neobligāti.
            </div>
        </div>
    </div>
</section>


        {{-- ========================================================= --}}
        {{-- 2️⃣ TEHNISKĀ APSKATE --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📝 {{ __('app.truck.show.inspection_title') }}</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- TA izsniegts --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.issued') }} *</label>
                    <input type="date" wire:model.live="inspection_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('inspection_issued') border-red-500 @enderror">
                    @error('inspection_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- TA derīga līdz --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.expires') }} *</label>
                    <input type="date" wire:model.live="inspection_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('inspection_expired') border-red-500 @enderror">
                    @error('inspection_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 3️⃣ APDROŠINĀŠANA --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">💼 Apdrošināšana</h3>

            {{-- Apdrošinātājs --}}
            <div>
                <label class="block mb-1 font-medium">{{ __('app.truck.show.ins_company') }} *</label>
                <input type="text" wire:model.blur="insurance_company"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_company') border-red-500 @enderror">
                @error('insurance_company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Polises numurs --}}
            <div>
                <label class="block mb-1 font-medium">{{ __('app.truck.show.ins_number') }} *</label>
                <input type="text" wire:model.blur="insurance_number"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_number') border-red-500 @enderror">
                @error('insurance_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Apdrošināšana spēkā no --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.issued') }} *</label>
                    <input type="date" wire:model.live="insurance_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_issued') border-red-500 @enderror">
                    @error('insurance_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Apdrošināšana derīga līdz --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.expires') }} *</label>
                    <input type="date" wire:model.live="insurance_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_expired') border-red-500 @enderror">
                    @error('insurance_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 4️⃣ TEHNISKĀ PASE --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📗 {{ __('app.truck.show.tech_passport_title') }}</h3>

            {{-- Tehniskās pases numurs --}}
            <div>
                <label class="block mb-1 font-medium">{{ __('app.truck.show.tech_number') }} *</label>
                <input type="text" wire:model.blur="tech_passport_nr"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_nr') border-red-500 @enderror">
                @error('tech_passport_nr') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Tehniskā pase izsniegta --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.issued') }} *</label>
                    <input type="date" wire:model.live="tech_passport_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_issued') border-red-500 @enderror">
                    @error('tech_passport_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Tehniskā pase derīga līdz --}}
                <div>
                    <label class="block mb-1 font-medium">{{ __('app.truck.show.expires') }} *</label>
                    <input type="date" wire:model.live="tech_passport_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_expired') border-red-500 @enderror">
                    @error('tech_passport_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Kompānija --}}
            <div>
                <label class="block mb-1 font-medium">{{ __('app.truck.show.company') }} *</label>
                <select wire:model="company_id"
                        class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('company_id') border-red-500 @enderror">
                    <option value="">{{ __('app.driver.create.company_choose') }}</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tehniskās pases foto --}}
            <div>
                <label class="block mb-1 font-medium">{{ __('app.truck.show.tech_passport_title') }} foto</label>

                <input type="file" wire:model.live="tech_passport_photo" accept="image/*,application/pdf" capture="environment"
                       class="w-full border rounded-lg px-4 py-3 text-sm"
                       x-on:click="fileUploading = true; if(cancelTimeout) clearTimeout(cancelTimeout); cancelTimeout = setTimeout(() => { fileUploading = false; cancelTimeout = null }, 15000)">

                @error('tech_passport_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                @if ($tech_passport_photo)
                    @if(strtolower($tech_passport_photo->getClientOriginalExtension() ?? '') === 'pdf')
                        <p class="mt-3 text-sm text-gray-600">📄 PDF</p>
                    @else
                        <img src="{{ $tech_passport_photo->temporaryUrl() }}"
                             class="mt-3 h-48 w-full object-cover rounded-xl shadow">
                    @endif
                @endif
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 6️⃣ DARBĪBAS --}}
        {{-- ========================================================= --}}
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t sticky bottom-0 bg-white pb-4">

            <a href="{{ route('trucks.index') }}"
               class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm transition text-center active:scale-95">
                {{ __('app.truck.create.cancel') }}
            </a>

            <button type="submit"
                    class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 active:scale-95 text-sm transition flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">💾 {{ __('app.truck.create.save') }}</span>

                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                    {{ __('app.truck.create.saving') }}
                </span>
            </button>

        </div>

    </form>

</div>
