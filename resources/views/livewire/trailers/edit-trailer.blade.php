<div class="p-8 bg-gray-100 min-h-screen flex justify-center">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl p-8">

        <h1 class="text-3xl font-bold mb-2">{{ __('app.trailer.edit.title') }}</h1>
        <p class="text-gray-600 mb-4">{{ __('app.trailer.edit.subtitle') }}</p>

        @if(session()->has('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-4">
                <strong>{{ __('app.trailer.edit.validation') }}</strong>
                <ul class="list-disc ml-5 mt-1 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="update" class="space-y-6"
            x-data="{ fileUploading: false, cancelTimeout: null }"
            x-on:livewire-upload-start="fileUploading = true; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
            x-on:livewire-upload-finish="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
            x-on:livewire-upload-error="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
            x-on:livewire-upload-cancel="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }">

        @include('components.upload-loading-overlay', ['targets' => 'update,tech_passport_photo'])

        {{-- Спиннер сразу при клике «Выбрать файл» --}}
        <div x-show="fileUploading"
             x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm"
             aria-live="polite">
            @include('components.upload-loading-spinner-box')
        </div>

            {{-- Pamatinformācija --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium">Puspiekabes tips *</label>

                    <select wire:model="type_id"
                            class="border rounded px-3 py-2 w-full @error('type_id') border-red-500 @enderror">
                        @foreach(config('trailer-types.types') as $id => $key)
                            <option value="{{ $id }}">
                                {{ config("trailer-types.icons.$key") }}
                                {{ config("trailer-types.labels.$key", ucfirst($key)) }}
                            </option>
                        @endforeach
                    </select>

                    @error('type_id')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block font-medium">{{ __('app.trailers.col_brand') }} *</label>
                    <input type="text" wire:model.blur="brand" autofocus class="border rounded px-3 py-2 w-full">
                    @error('brand') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium">{{ __('app.trailers.col_plate') }} *</label>
                    <input type="text" wire:model.blur="plate" class="border rounded px-3 py-2 w-full">
                    @error('plate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium">{{ __('app.truck.show.year') }} *</label>
                    <input type="number" wire:model.blur="year" class="border rounded px-3 py-2 w-full">
                    @error('year') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Tehniskā apskate --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('app.truck.show.issued') }}</label>
                    <input type="date" wire:model.blur="inspection_issued" class="border rounded px-3 py-2 w-full">
                    @error('inspection_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>{{ __('app.truck.show.expires') }}</label>
                    <input type="date" wire:model.blur="inspection_expired" class="border rounded px-3 py-2 w-full">
                    @error('inspection_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Apdrošināšana --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('app.truck.show.ins_company') }}</label>
                    <input type="text" wire:model.blur="insurance_company" class="border rounded px-3 py-2 w-full">
                    @error('insurance_company') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>{{ __('app.truck.show.ins_number') }}</label>
                    <input type="text" wire:model.blur="insurance_number" class="border rounded px-3 py-2 w-full">
                    @error('insurance_number') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>{{ __('app.truck.show.issued') }}</label>
                    <input type="date" wire:model.blur="insurance_issued" class="border rounded px-3 py-2 w-full">
                    @error('insurance_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>{{ __('app.truck.show.expires') }}</label>
                    <input type="date" wire:model.blur="insurance_expired" class="border rounded px-3 py-2 w-full">
                    @error('insurance_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- TIR --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>TIR izsniegts</label>
                    <input type="date" wire:model.blur="tir_issued" class="border rounded px-3 py-2 w-full">
                    @error('tir_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>TIR der līdz</label>
                    <input type="date" wire:model.blur="tir_expired" class="border rounded px-3 py-2 w-full">
                    @error('tir_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- VIN --}}
            <div>
                <label>VIN</label>
                <input type="text" wire:model.blur="vin" class="border rounded px-3 py-2 w-full">
                @error('vin') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Tehniskā pase --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label>{{ __('app.truck.show.tech_number') }}</label>
                    <input type="text" wire:model.blur="tech_passport_nr" class="border rounded px-3 py-2 w-full">
                    @error('tech_passport_nr') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>{{ __('app.truck.show.issued') }}</label>
                    <input type="date" wire:model.blur="tech_passport_issued" class="border rounded px-3 py-2 w-full">
                    @error('tech_passport_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>{{ __('app.truck.show.expires') }}</label>
                    <input type="date" wire:model.blur="tech_passport_expired" class="border rounded px-3 py-2 w-full">
                    @error('tech_passport_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Kompānija --}}
            <div>
                <label>{{ __('app.truck.show.company') }}</label>
                <select wire:model="company_id"
                        class="w-full border rounded px-3 py-2 @error('company_id') border-red-500 @enderror">
                    <option value="">{{ __('app.driver.create.company_choose') }}</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Tehniskās pases foto --}}
            <div>
                <label>{{ __('app.truck.show.tech_passport_title') }} foto</label>
                <input type="file" wire:model.live="tech_passport_photo" accept="image/*,application/pdf" class="border rounded px-3 py-2 w-full"
                       x-on:click="fileUploading = true; if(cancelTimeout) clearTimeout(cancelTimeout); cancelTimeout = setTimeout(() => { fileUploading = false; cancelTimeout = null }, 15000)">
                @error('tech_passport_photo') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

                <div class="mt-2">
                    @if($tech_passport_photo)
                        @if(strtolower($tech_passport_photo->getClientOriginalExtension() ?? '') === 'pdf')
                            <p class="text-sm text-gray-600">📄 PDF</p>
                        @else
                            <img src="{{ $tech_passport_photo->temporaryUrl() }}" class="h-48 object-contain rounded">
                        @endif
                    @elseif($current_photo)
                        @if(str_ends_with(strtolower($current_photo), '.pdf'))
                            <a href="{{ asset('storage/' . $current_photo) }}" target="_blank" rel="noopener" class="text-sm text-blue-600">📄 PDF</a>
                        @else
                            <img src="{{ asset('storage/' . $current_photo) }}" class="h-48 object-contain rounded">
                        @endif
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('trailers.index') }}" class="px-6 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                    ← {{ __('app.trailer.edit.cancel') }}
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    💾 {{ __('app.trailer.edit.save') }}
                </button>
            </div>

        </form>
    </div>
</div>
