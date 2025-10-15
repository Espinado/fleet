<div class="p-8 bg-gray-100 min-h-screen flex justify-center">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl p-8">

        <h1 class="text-3xl font-bold mb-6">Edit Trailer</h1>

        @if(session()->has('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="update" class="space-y-6">

            {{-- Основная информация --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium">Brand</label>
                    <input type="text" wire:model.defer="brand" autofocus class="border rounded px-3 py-2 w-full">
                    @error('brand') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium">Plate</label>
                    <input type="text" wire:model.defer="plate" class="border rounded px-3 py-2 w-full">
                    @error('plate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium">Year</label>
                    <input type="number" wire:model.defer="year" class="border rounded px-3 py-2 w-full">
                    @error('year') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Inspection --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>Inspection Issued</label>
                    <input type="date" wire:model.defer="inspection_issued" class="border rounded px-3 py-2 w-full">
                    @error('inspection_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Inspection Expired</label>
                    <input type="date" wire:model.defer="inspection_expired" class="border rounded px-3 py-2 w-full">
                    @error('inspection_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Insurance --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>Insurance Company</label>
                    <input type="text" wire:model.defer="insurance_company" class="border rounded px-3 py-2 w-full">
                    @error('insurance_company') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Insurance #</label>
                    <input type="text" wire:model.defer="insurance_number" class="border rounded px-3 py-2 w-full">
                    @error('insurance_number') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Insurance Issued</label>
                    <input type="date" wire:model.defer="insurance_issued" class="border rounded px-3 py-2 w-full">
                    @error('insurance_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Insurance Expired</label>
                    <input type="date" wire:model.defer="insurance_expired" class="border rounded px-3 py-2 w-full">
                    @error('insurance_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- TIR --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>TIR Issued</label>
                    <input type="date" wire:model.defer="tir_issued" class="border rounded px-3 py-2 w-full">
                    @error('tir_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>TIR Expired</label>
                    <input type="date" wire:model.defer="tir_expired" class="border rounded px-3 py-2 w-full">
                    @error('tir_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- VIN --}}
            <div>
                <label>VIN</label>
                <input type="text" wire:model.defer="vin" class="border rounded px-3 py-2 w-full">
                @error('vin') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Tech Passport --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label>Tech Passport #</label>
                    <input type="text" wire:model.defer="tech_passport_nr" class="border rounded px-3 py-2 w-full">
                    @error('tech_passport_nr') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Issued</label>
                    <input type="date" wire:model.defer="tech_passport_issued" class="border rounded px-3 py-2 w-full">
                    @error('tech_passport_issued') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Expired</label>
                    <input type="date" wire:model.defer="tech_passport_expired" class="border rounded px-3 py-2 w-full">
                    @error('tech_passport_expired') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
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
            {{-- Tech Passport Photo --}}
            <div>
                <label>Tech Passport Photo</label>
                <input type="file" wire:model="tech_passport_photo" class="border rounded px-3 py-2 w-full">
                @error('tech_passport_photo') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

                <div class="mt-2">
                    @if($tech_passport_photo)
                        <img src="{{ $tech_passport_photo->temporaryUrl() }}" class="h-48 object-contain rounded">
                    @elseif($current_photo)
                        <img src="{{ asset('storage/' . $current_photo) }}" class="h-48 object-contain rounded">
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('trailers.index') }}" class="px-6 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                    ← Back
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    💾 Update Trailer
                </button>
            </div>

        </form>
    </div>
</div>
