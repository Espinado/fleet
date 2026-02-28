<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6 bg-gray-50 min-h-screen">

    {{-- ✅ Notifications --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl">
            <strong>Validation errors:</strong>
            <ul class="list-disc ml-5 mt-1 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- 🧾 Form --}}
    <form wire:submit.prevent="save"
          class="bg-white shadow-md rounded-2xl p-4 sm:p-6 space-y-10 relative">

        {{-- 🔄 Global loader --}}
        <div wire:loading.flex wire:target="save, tech_passport_photo"
             class="absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-20 rounded-2xl">
            <div class="animate-spin h-12 w-12 border-4 border-blue-500 border-t-transparent rounded-full mb-3"></div>
            <p class="text-blue-600 text-sm">Saving trailer...</p>
        </div>

        {{-- HEADER --}}
        <header>
            <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">➕ Add New Trailer</h2>
            <p class="text-gray-600 text-sm sm:text-base">Fill in all required fields carefully.</p>
        </header>


        {{-- ========================================================= --}}
        {{-- 1️⃣ GENERAL INFO --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📘 General Information</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">Brand *</label>
                    <input type="text" wire:model.defer="brand"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('brand') border-red-500 @enderror">
                    @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Plate *</label>
                    <input type="text" wire:model.defer="plate"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('plate') border-red-500 @enderror">
                    @error('plate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-1 font-medium">Year *</label>
                    <input type="number" wire:model.defer="year"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('year') border-red-500 @enderror">
                    @error('year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
    <label class="block mb-1 font-medium">Trailer Type *</label>

    <select wire:model="type_id"
            class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500
                   @error('type_id') border-red-500 @enderror">
        @foreach(config('trailer-types.types') as $id => $key)
            <option value="{{ $id }}">
                {{ config("trailer-types.icons.$key") }} {{ config("trailer-types.labels.$key", $key) }}
            </option>
        @endforeach
    </select>

    @error('type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

    <p class="text-xs text-gray-500 mt-1">
        Default: {{ config('trailer-types.labels.' . (config('trailer-types.types.1') ?? 'cargo')) }}
    </p>
</div>

                <div>
                    <label class="block mb-1 font-medium">VIN</label>
                    <input type="text" wire:model.defer="vin"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('vin') border-red-500 @enderror">
                    @error('vin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Company *</label>
                    <select wire:model="company_id"
                            class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('company') border-red-500 @enderror">
                        <option value="">Select company</option>
                        @foreach(config('companies') as $id => $comp)
                            <option value="{{ $id }}">{{ $comp['name'] }}</option>
                        @endforeach
                    </select>
                    @error('company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 2️⃣ INSPECTION --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📝 Inspection</h3>

            <div class="grid sm:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">Inspection Issued *</label>
                    <input type="date" wire:model.lazy="inspection_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('inspection_issued') border-red-500 @enderror">
                    @error('inspection_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Inspection Expired *</label>
                    <input type="date" wire:model.lazy="inspection_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('inspection_expired') border-red-500 @enderror">
                    @error('inspection_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 3️⃣ INSURANCE --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">💼 Insurance</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">Insurance Company *</label>
                    <input type="text" wire:model.defer="insurance_company"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('insurance_company') border-red-500 @enderror">
                    @error('insurance_company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Insurance Number *</label>
                    <input type="text" wire:model.defer="insurance_number"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('insurance_number') border-red-500 @enderror">
                    @error('insurance_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">Issued *</label>
                    <input type="date" wire:model.lazy="insurance_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('insurance_issued') border-red-500 @enderror">
                    @error('insurance_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Expired *</label>
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
                    <label class="block mb-1 font-medium">TIR Issued</label>
                    <input type="date" wire:model.lazy="tir_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tir_issued') border-red-500 @enderror">
                    @error('tir_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">TIR Expired</label>
                    <input type="date" wire:model.lazy="tir_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tir_expired') border-red-500 @enderror">
                    @error('tir_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 5️⃣ TECH PASSPORT --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📗 Tech Passport</h3>

            <div>
                <label class="block mb-1 font-medium">Tech Passport Nr *</label>
                <input type="text" wire:model.defer="tech_passport_nr"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tech_passport_nr') border-red-500 @enderror">
                @error('tech_passport_nr') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">Issued *</label>
                    <input type="date" wire:model.lazy="tech_passport_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tech_passport_issued') border-red-500 @enderror">
                    @error('tech_passport_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Expired *</label>
                    <input type="date" wire:model.lazy="tech_passport_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-blue-500 @error('tech_passport_expired') border-red-500 @enderror">
                    @error('tech_passport_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Photo --}}
            <div>
                <label class="block mb-1 font-medium">Tech Passport Photo</label>

                <input type="file" wire:model="tech_passport_photo" accept="image/*,application/pdf"
                       class="w-full border rounded-lg px-4 py-3 text-sm @error('tech_passport_photo') border-red-500 @enderror">

                @error('tech_passport_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                @if($tech_passport_photo)
                    <img src="{{ $tech_passport_photo->temporaryUrl() }}"
                         class="mt-3 w-full h-48 object-contain rounded-xl shadow">
                @endif
            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- 6️⃣ ACTION BUTTONS — FIXED BOTTOM BAR --}}
        {{-- ========================================================= --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4
                    flex justify-end gap-3 z-50">

            <a href="{{ route('trailers.index') }}"
               class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm transition active:scale-95">
                Cancel
            </a>

            <button type="submit"
                    class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 active:scale-95 text-sm transition flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">

                <span wire:loading.remove wire:target="save">💾 Save Trailer</span>

                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                    Saving...
                </span>

            </button>

        </div>

        {{-- Spacer to prevent content overlap --}}
        <div class="h-24"></div>

    </form>
</div>
