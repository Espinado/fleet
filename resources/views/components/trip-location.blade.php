<div class="space-y-3">
    {{-- Country --}}
    <div>
        <label class="block font-medium">Country *</label>
        <select wire:model="{{ $countryId }}" class="w-full border rounded p-2">
            <option value="">Select country</option>
            @foreach(config('countries') as $id => $country)
                <option value="{{ $id }}">{{ $country['name'] }}</option>
            @endforeach
        </select>
        @error($countryId) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- City --}}
    <div>
        <label class="block font-medium">City *</label>
        @php
            $country = $this->{$countryId} ?? null;
            $cities = $country ? (config('cities.' . $country) ?? []) : [];
        @endphp

        <select wire:model="{{ $cityId }}" class="w-full border rounded p-2" @disabled(empty($cities))>
            <option value="">Select city</option>
            @foreach($cities as $id => $city)
                <option value="{{ $id }}">{{ $city }}</option>
            @endforeach
        </select>
        @error($cityId) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Address --}}
    <div>
        <label class="block font-medium">Address</label>
        <input type="text" wire:model="{{ $address }}" class="w-full border rounded p-2" placeholder="Street, building...">
        @error($address) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
