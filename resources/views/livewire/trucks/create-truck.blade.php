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

    {{-- ❗️Ошибки --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl shadow-sm">
            <strong>Validation errors:</strong>
            <ul class="list-disc ml-5 mt-1 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 🧾 Форма --}}
    <form wire:submit.prevent="save"
          class="bg-white shadow-md rounded-2xl p-4 sm:p-6 space-y-10 relative">

        {{-- 🔄 Лоадер --}}
        <div wire:loading.flex wire:target="save, tech_passport_photo"
             class="absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-20 rounded-2xl">
            <div class="animate-spin h-12 w-12 border-4 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p class="text-blue-600 text-sm">Saving truck...</p>
        </div>

        {{-- Заголовок --}}
        <header>
            <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">🚚 Add New Truck</h2>
            <p class="text-gray-600 text-sm sm:text-base">Fill in all required fields carefully.</p>
        </header>

        {{-- ========================================================= --}}
        {{-- 1️⃣ GENERAL INFO --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📘 General Information</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Brand --}}
                <div>
                    <label class="block mb-1 font-medium">Brand *</label>
                    <input type="text" wire:model.defer="brand"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('brand') border-red-500 @enderror">
                    @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Model --}}
                <div>
                    <label class="block mb-1 font-medium">Model *</label>
                    <input type="text" wire:model.defer="model"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('model') border-red-500 @enderror">
                    @error('model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                {{-- Plate --}}
                <div>
                    <label class="block mb-1 font-medium">Plate *</label>
                    <input type="text" wire:model.defer="plate"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('plate') border-red-500 @enderror">
                    @error('plate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Year --}}
                <div>
                    <label class="block mb-1 font-medium">Year *</label>
                    <input type="number" wire:model.defer="year"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('year') border-red-500 @enderror">
                    @error('year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- VIN --}}
                <div>
                    <label class="block mb-1 font-medium">VIN *</label>
                    <input type="text" wire:model.defer="vin"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('vin') border-red-500 @enderror">
                    @error('vin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 2️⃣ INSPECTION --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📝 Inspection</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Inspection issued --}}
                <div>
                    <label class="block mb-1 font-medium">Inspection Issued *</label>
                    <input type="date" wire:model.live="inspection_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('inspection_issued') border-red-500 @enderror">
                    @error('inspection_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Inspection expired --}}
                <div>
                    <label class="block mb-1 font-medium">Inspection Expired *</label>
                    <input type="date" wire:model.live="inspection_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('inspection_expired') border-red-500 @enderror">
                    @error('inspection_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 3️⃣ INSURANCE --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">💼 Insurance</h3>

            {{-- Company --}}
            <div>
                <label class="block mb-1 font-medium">Insurance Company *</label>
                <input type="text" wire:model.defer="insurance_company"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_company') border-red-500 @enderror">
                @error('insurance_company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Policy Number --}}
            <div>
                <label class="block mb-1 font-medium">Insurance Number *</label>
                <input type="text" wire:model.defer="insurance_number"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_number') border-red-500 @enderror">
                @error('insurance_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Insurance issued --}}
                <div>
                    <label class="block mb-1 font-medium">Issued *</label>
                    <input type="date" wire:model.live="insurance_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_issued') border-red-500 @enderror">
                    @error('insurance_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Insurance expired --}}
                <div>
                    <label class="block mb-1 font-medium">Expired *</label>
                    <input type="date" wire:model.live="insurance_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_expired') border-red-500 @enderror">
                    @error('insurance_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 4️⃣ TECH PASSPORT --}}
        {{-- ========================================================= --}}
        <section class="space-y-4">
            <h3 class="text-xl font-semibold border-b pb-2">📗 Tech Passport</h3>

            {{-- Tech NR --}}
            <div>
                <label class="block mb-1 font-medium">Tech Passport Nr *</label>
                <input type="text" wire:model.defer="tech_passport_nr"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_nr') border-red-500 @enderror">
                @error('tech_passport_nr') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Tech passport issued --}}
                <div>
                    <label class="block mb-1 font-medium">Issued *</label>
                    <input type="date" wire:model.live="tech_passport_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_issued') border-red-500 @enderror">
                    @error('tech_passport_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Tech passport expired --}}
                <div>
                    <label class="block mb-1 font-medium">Expired *</label>
                    <input type="date" wire:model.live="tech_passport_expired"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_expired') border-red-500 @enderror">
                    @error('tech_passport_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Company --}}
            <div>
                <label class="block mb-1 font-medium">Company *</label>
                <select wire:model="company"
                        class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('company') border-red-500 @enderror">
                    <option value="">Select company</option>
                    @foreach(config('companies') as $id => $comp)
                        <option value="{{ $id }}">{{ $comp['name'] }}</option>
                    @endforeach
                </select>
                @error('company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Passport photo --}}
            <div>
                <label class="block mb-1 font-medium">Tech Passport Photo</label>

                <input type="file" wire:model="tech_passport_photo" accept="image/*,application/pdf" capture="environment"
                       class="w-full border rounded-lg px-4 py-3 text-sm">

                @error('tech_passport_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                @if ($tech_passport_photo)
                    <img src="{{ $tech_passport_photo->temporaryUrl() }}"
                         class="mt-3 h-48 w-full object-cover rounded-xl shadow">
                @endif
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 6️⃣ ACTION BUTTONS --}}
        {{-- ========================================================= --}}
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t sticky bottom-0 bg-white pb-4">

            <a href="{{ route('trucks.index') }}"
               class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm transition text-center active:scale-95">
                Cancel
            </a>

            <button type="submit"
                    class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 active:scale-95 text-sm transition flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">💾 Save Truck</span>

                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                    Saving...
                </span>
            </button>

        </div>

    </form>

</div>
