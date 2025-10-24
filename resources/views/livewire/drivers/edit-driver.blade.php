<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow space-y-6">
    <h2 class="text-xl font-bold">✏️ Edit Driver</h2>

    {{-- Success Message --}}
    @if(session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6 relative">

        {{-- Loader --}}
        <div wire:loading.flex wire:target="save, declared_country_id, actual_country_id"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
            <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        {{-- Personal Information --}}
        <h3 class="font-semibold text-lg border-b pb-1">Personal Information</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>First Name</label>
                <input type="text" wire:model.live="first_name" class="w-full border rounded p-2">
                @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Last Name</label>
                <input type="text" wire:model.live="last_name" class="w-full border rounded p-2">
                @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Personal Code</label>
                <input type="text" wire:model.live="pers_code" class="w-full border rounded p-2">
                @error('pers_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Citizenship</label>
                <select wire:model.live="citizenship" class="w-full border rounded p-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>
                @error('citizenship') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Phone</label>
                <input type="text" wire:model.live="phone" class="w-full border rounded p-2">
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Email</label>
                <input type="email" wire:model.live="email" class="w-full border rounded p-2">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Declared Address --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Declared Address</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="relative">
                <label>Country</label>
                <select wire:model.live="declared_country_id" class="w-full border rounded p-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>

                <div wire:loading wire:target="declared_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('declared_country_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="relative">
                <label>City</label>
                <select wire:model.live="declared_city_id" class="w-full border rounded p-2"
                        @disabled(empty($declared_country_id))>
                    <option value="">Select city</option>
                    @if(!empty($declared_country_id))
                        @foreach(getCitiesByCountryId($declared_country_id) as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    @endif
                </select>

                <div wire:loading wire:target="declared_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('declared_city_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Street</label>
                <input type="text" wire:model.live="declared_street" class="w-full border rounded p-2">
                @error('declared_street') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Building</label>
                <input type="text" wire:model.live="declared_building" class="w-full border rounded p-2">
                @error('declared_building') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Room</label>
                <input type="text" wire:model.live="declared_room" class="w-full border rounded p-2">
                @error('declared_room') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Postcode</label>
                <input type="text" wire:model.live="declared_postcode" class="w-full border rounded p-2">
                @error('declared_postcode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Actual Address --}}
        <h3 class="font-semibold text-lg border-b pb-1 mt-4">Actual Address</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="relative">
                <label>Country</label>
                <select wire:model.live="actual_country_id" class="w-full border rounded p-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>

                <div wire:loading wire:target="actual_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('actual_country_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="relative">
                <label>City</label>
                <select wire:model.live="actual_city_id" class="w-full border rounded p-2"
                        @disabled(empty($actual_country_id))>
                    <option value="">Select city</option>
                    @if(!empty($actual_country_id))
                        @foreach(getCitiesByCountryId($actual_country_id) as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    @endif
                </select>

                <div wire:loading wire:target="actual_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('actual_city_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Street</label>
                <input type="text" wire:model.live="actual_street" class="w-full border rounded p-2">
                @error('actual_street') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Building</label>
                <input type="text" wire:model.live="actual_building" class="w-full border rounded p-2">
                @error('actual_building') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Room</label>
                <input type="text" wire:model.live="actual_room" class="w-full border rounded p-2">
                @error('actual_room') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Status</label>
                <select wire:model.live="status" class="w-full border rounded p-2">
                    @foreach(\App\Enums\DriverStatus::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center gap-2 mt-2">
            <input type="checkbox" wire:model.live="is_active" class="mr-2">
            <span>Active</span>
        </div>

        {{-- License, Code95, Permit, Medical, Declaration --}}
        {{-- ... (оставляем всё как у тебя, только добавлены .live и ошибки везде оформлены одинаково) ... --}}

        {{-- Company --}}
        <div>
            <label>Company</label>
            <select wire:model.live="company" class="w-full border rounded p-2">
                <option value="">Select company</option>
                @foreach(config('companies') as $id => $company)
                    <option value="{{ $id }}">{{ $company['name'] }}</option>
                @endforeach
            </select>
            @error('company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Photos --}}
        {{-- ... без изменений, всё верно ... --}}

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded mt-4 hover:bg-blue-700">
                💾 Save
            </button>

            <button type="button"
                    wire:click="destroy"
                    onclick="confirm('Are you sure you want to delete this driver?') || event.stopImmediatePropagation()"
                    class="px-4 py-2 bg-red-600 text-white rounded mt-4 hover:bg-red-700">
                🗑 Delete
            </button>
        </div>
    </form>
</div>
