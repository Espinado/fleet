<div class="p-6 max-w-5xl mx-auto">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 relative">
        {{-- Лоадер при сохранении --}}
        <div wire:loading.flex wire:target="save"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
            <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <h2 class="text-2xl font-semibold mb-6">➕ Create New Trip</h2>

        <form wire:submit.prevent="save" class="space-y-6">
            {{-- Company --}}
            <div>
                <label class="block font-medium">Expeditor Company *</label>
                <select wire:model="company_id" class="w-full border rounded p-2">
                    <option value="">Select company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                    @endforeach
                </select>
                @error('company_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Driver & Vehicle --}}
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium">Driver *</label>
                    <select wire:model="driver_id" class="w-full border rounded p-2">
                        <option value="">Select driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                        @endforeach
                    </select>
                    @error('driver_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium">Truck *</label>
                    <select wire:model="truck_id" class="w-full border rounded p-2">
                        <option value="">Select truck</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">
                                {{ $truck->brand }} {{ $truck->model }} — {{ $truck->plate_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('truck_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium">Trailer</label>
                    <input type="text" wire:model="trailer_id" class="w-full border rounded p-2" placeholder="Optional">
                    @error('trailer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Client --}}
            <div>
                <label class="block font-medium">Client *</label>
                <select wire:model="client_id" class="w-full border rounded p-2">
                    <option value="">Select client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                    @endforeach
                </select>
                @error('client_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- From / To --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold mb-2">From:</h4>
                    <x-trip-location :countryId="'origin_country_id'" :cityId="'origin_city_id'" :address="'origin_address'" />
                </div>

                <div>
                    <h4 class="font-semibold mb-2">To:</h4>
                    <x-trip-location :countryId="'destination_country_id'" :cityId="'destination_city_id'" :address="'destination_address'" />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button"
                        class="px-4 py-2 border rounded bg-gray-100 hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition flex items-center">
                    💾 Save Trip
                </button>
            </div>
        </form>
    </div>
</div>
