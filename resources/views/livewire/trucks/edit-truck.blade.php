<div class="max-w-4xl mx-auto py-8 px-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Truck</h1>

    @if (session()->has('success'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-6">

        {{-- Brand --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Brand *</label>
            <input type="text" wire:model.defer="brand"
                   class="w-full border rounded-lg px-3 py-2 @error('brand') border-red-500 @enderror">
            @error('brand') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
        </div>

        {{-- Model --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Model *</label>
            <input type="text" wire:model.defer="model"
                   class="w-full border rounded-lg px-3 py-2 @error('model') border-red-500 @enderror">
            @error('model') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
        </div>

        {{-- Plate --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Plate *</label>
            <input type="text" wire:model.defer="plate"
                   class="w-full border rounded-lg px-3 py-2 @error('plate') border-red-500 @enderror">
            @error('plate') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
        </div>

        {{-- Year --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Year *</label>
            <input type="number" wire:model.defer="year"
                   class="w-full border rounded-lg px-3 py-2 @error('year') border-red-500 @enderror">
            @error('year') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
        </div>

        {{-- Inspection --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Inspection Issued *</label>
                <input type="date" wire:model="inspection_issued"
                       class="w-full border rounded-lg px-3 py-2 @error('inspection_issued') border-red-500 @enderror">
                @error('inspection_issued') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Inspection Expired *</label>
                <input type="date" wire:model="inspection_expired"
                       class="w-full border rounded-lg px-3 py-2 @error('inspection_expired') border-red-500 @enderror">
                @error('inspection_expired') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Insurance --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Insurance Company *</label>
                <input type="text" wire:model.defer="insurance_company"
                       class="w-full border rounded-lg px-3 py-2 @error('insurance_company') border-red-500 @enderror">
                @error('insurance_company') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Insurance Number *</label>
                <input type="text" wire:model.defer="insurance_number"
                       class="w-full border rounded-lg px-3 py-2 @error('insurance_number') border-red-500 @enderror">
                @error('insurance_number') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Insurance Issued *</label>
                <input type="date" wire:model="insurance_issued"
                       class="w-full border rounded-lg px-3 py-2 @error('insurance_issued') border-red-500 @enderror">
                @error('insurance_issued') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Insurance Expired *</label>
                <input type="date" wire:model="insurance_expired"
                       class="w-full border rounded-lg px-3 py-2 @error('insurance_expired') border-red-500 @enderror">
                @error('insurance_expired') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- VIN --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">VIN *</label>
            <input type="text" wire:model.defer="vin"
                   class="w-full border rounded-lg px-3 py-2 @error('vin') border-red-500 @enderror">
            @error('vin') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
        </div>

        {{-- Tech passport --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Tech Passport # *</label>
                <input type="text" wire:model.defer="tech_passport_nr"
                       class="w-full border rounded-lg px-3 py-2 @error('tech_passport_nr') border-red-500 @enderror">
                @error('tech_passport_nr') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Issued *</label>
                <input type="date" wire:model="tech_passport_issued"
                       class="w-full border rounded-lg px-3 py-2 @error('tech_passport_issued') border-red-500 @enderror">
                @error('tech_passport_issued') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Expired *</label>
                <input type="date" wire:model="tech_passport_expired"
                       class="w-full border rounded-lg px-3 py-2 @error('tech_passport_expired') border-red-500 @enderror">
                @error('tech_passport_expired') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror
            </div>
        </div>
          <div>
    <label>Company</label>
    <select wire:model="company" class="w-full border rounded p-2">
        <option value="">Select company</option>
        @foreach(config('companies') as $id => $company)
            <option value="{{ $id }}">{{ $company['name'] }}</option>
        @endforeach
    </select>
    @error('company') <span class="text-red-500">{{ $message }}</span> @enderror
</div>

        {{-- Photo --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Tech Passport Photo</label>
            <input type="file" wire:model="tech_passport_photo" accept="image/*,application/pdf" capture="environment"
                   class="w-full border rounded-lg px-3 py-2 @error('tech_passport_photo') border-red-500 @enderror">
            @error('tech_passport_photo') <p class="text-red-500 text-sm mt-1" data-error>{{ $message }}</p> @enderror

            <div class="mt-4">
                @if ($tech_passport_photo)
                    <p class="text-gray-700 mb-2">Preview (new):</p>
                    <img src="{{ $tech_passport_photo->temporaryUrl() }}" class="h-40 rounded shadow">
                @elseif ($existing_photo)
                    <p class="text-gray-700 mb-2">Current Photo:</p>
                    <img src="{{ asset('storage/'.$existing_photo) }}" class="h-40 rounded shadow">
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('trucks.show', $truck->id) }}"
               class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
