<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6 bg-gray-50 min-h-screen">

    {{-- ✅ Notifications --}}
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

    {{-- ❗️ Validation errors --}}
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


    {{-- 🧾 Form --}}
    <form wire:submit.prevent="save"
      class="bg-white shadow-md rounded-2xl p-4 sm:p-6 space-y-10 relative">

    {{-- 🔄 Global loader (fullscreen) --}}
    <div wire:loading.flex wire:target="save, tech_passport_photo"
         class="fixed inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-[200]">
        <div class="animate-spin h-14 w-14 border-4 border-blue-500 border-t-transparent rounded-full mb-4"></div>
        <p class="text-blue-700 text-base font-medium">Saving changes...</p>
    </div>

        {{-- HEADER --}}
        <header>
            <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">✏️ Edit Truck</h2>
            <p class="text-gray-600 text-sm sm:text-base">Update truck information below.</p>
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
{{-- 🪪 LICENSE (optional) --}}
{{-- ========================================================= --}}
<section class="space-y-4">
    <h3 class="text-xl font-semibold border-b pb-2">🪪 License</h3>

    <div class="grid sm:grid-cols-3 gap-4">
        {{-- License number --}}
        <div class="sm:col-span-1">
            <label class="block mb-1 font-medium">License Number</label>
            <input type="text" wire:model.defer="license_number"
                   placeholder="e.g. LV-123456"
                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('license_number') border-red-500 @enderror">
            @error('license_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- License issued --}}
        <div>
            <label class="block mb-1 font-medium">Issued</label>
            <input type="date" wire:model.defer="license_issued"
                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('license_issued') border-red-500 @enderror">
            @error('license_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- License expired --}}
        <div>
            <label class="block mb-1 font-medium">Expired</label>
            <input type="date" wire:model.defer="license_expired"
                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('license_expired') border-red-500 @enderror">
            @error('license_expired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Mini hint --}}
    <div class="text-sm text-gray-600 bg-gray-50 border rounded-xl p-3">
        <div class="flex items-start gap-2">
            <span class="mt-0.5">💡</span>
            <div>Optional fields. If dates are set, <span class="font-medium">Expired</span> should be after <span class="font-medium">Issued</span>.</div>
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
        <input type="date" wire:model.defer="inspection_issued"
               class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('inspection_issued') border-red-500 @enderror">
        @error('inspection_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block mb-1 font-medium">Inspection Expired *</label>
        <input type="date" wire:model.defer="inspection_expired"
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

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">Insurance Company *</label>
                    <input type="text" wire:model.defer="insurance_company"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_company') border-red-500 @enderror">
                    @error('insurance_company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium">Insurance Number *</label>
                    <input type="text" wire:model.defer="insurance_number"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_number') border-red-500 @enderror">
                    @error('insurance_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">Issued *</label>
                    <input type="date" wire:model.live="insurance_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('insurance_issued') border-red-500 @enderror">
                    @error('insurance_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

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

            {{-- Number --}}
            <div>
                <label class="block mb-1 font-medium">Tech Passport Nr *</label>
                <input type="text" wire:model.defer="tech_passport_nr"
                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_nr') border-red-500 @enderror">
                @error('tech_passport_nr') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">Issued *</label>
                    <input type="date" wire:model.live="tech_passport_issued"
                           class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('tech_passport_issued') border-red-500 @enderror">
                    @error('tech_passport_issued') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

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

            {{-- Photo --}}
            <div>
                <label class="block mb-1 font-medium">Tech Passport Photo</label>

                <input type="file" wire:model="tech_passport_photo" accept="image/*,application/pdf" capture="environment"
                       class="w-full border rounded-lg px-4 py-3 text-sm @error('tech_passport_photo') border-red-500 @enderror">

                @error('tech_passport_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                <div class="mt-4">
                    @if($tech_passport_photo)
                        <p class="text-gray-700 mb-2 text-sm">Preview (new):</p>
                        <img src="{{ $tech_passport_photo->temporaryUrl() }}" class="h-40 rounded-xl shadow">
                    @elseif ($existing_photo)
                        <p class="text-gray-700 mb-2 text-sm">Current Photo:</p>
                        <img src="{{ asset('storage/'.$existing_photo) }}" class="h-40 rounded-xl shadow">
                    @endif
                </div>
            </div>
        </section>


        {{-- ========================================================= --}}
        {{-- 5️⃣ ACTIONS — FIXED BOTTOM BAR --}}
        {{-- ========================================================= --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4
                    flex justify-end gap-3 z-50">

            <a href="{{ route('trucks.show', $truck->id) }}"
               class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm transition active:scale-95">
                Cancel
            </a>

            <button type="submit"
                    class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 text-sm transition flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">

                <span wire:loading.remove wire:target="save">💾 Save Changes</span>

                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                    Saving...
                </span>

            </button>

        </div>

        {{-- Prevent form overlap --}}
        <div class="h-24"></div>

    </form>
</div>

<script>
    window.addEventListener('scroll-top', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>