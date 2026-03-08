<div class="p-6 max-w-4xl mx-auto">
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 relative">
        <h2 class="text-2xl font-semibold mb-1">✏️ {{ __('app.client.edit.title') }}</h2>
        <p class="text-gray-600 text-sm mb-6">{{ __('app.client.edit.subtitle') }}</p>

        <div wire:loading.flex wire:target="save, jur_country_id, fiz_country_id"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20 rounded-lg">
            <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
            <span class="ml-2 text-blue-600 text-sm">{{ __('app.client.edit.saving') }}</span>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.company_name') }}</label>
                <input type="text" wire:model="company_name" class="w-full border rounded px-3 py-2">
                @error('company_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.reg_nr') }}</label>
                    <input type="text" wire:model="reg_nr" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.representative') }}</label>
                    <input type="text" wire:model="representative" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.email') }}</label>
                    <input type="email" wire:model="email" class="w-full border rounded px-3 py-2">
                    @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.phone') }}</label>
                    <input type="text" wire:model="phone" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="border-t pt-4 space-y-4">
                <h3 class="text-lg font-semibold mb-2">{{ __('app.client.edit.legal_address') }}</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.country') }} *</label>
                        <select wire:model.live="jur_country_id" class="w-full border rounded px-3 py-2">
                            <option value="">{{ __('app.client.edit.country_choose') }}</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ is_array($country) ? ($country['name'] ?? $country) : $country }}</option>
                            @endforeach
                        </select>
                        @error('jur_country_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.city') }} *</label>
                        <select wire:model.live="jur_city_id" class="w-full border rounded px-3 py-2">
                            <option value="">{{ __('app.client.edit.city_choose') }}</option>
                            @foreach($jurCities as $id => $city)
                                <option value="{{ $id }}">{{ is_array($city) ? ($city['name'] ?? $city) : $city }}</option>
                            @endforeach
                        </select>
                        @error('jur_city_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="jur_address" placeholder="{{ __('app.client.edit.street_placeholder') }}"
                           class="border rounded px-3 py-2">
                    <input type="text" wire:model="jur_post_code" placeholder="{{ __('app.client.edit.postcode') }}"
                           class="border rounded px-3 py-2">
                </div>
            </div>

            <div class="border-t pt-4 space-y-4">
                <h3 class="text-lg font-semibold mb-2">{{ __('app.client.edit.physical_address') }}</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.country') }}</label>
                        <select wire:model.live="fiz_country_id" class="w-full border rounded px-3 py-2">
                            <option value="">{{ __('app.client.edit.country_choose') }}</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ is_array($country) ? ($country['name'] ?? $country) : $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('app.client.edit.city') }}</label>
                        <select wire:model="fiz_city_id" class="w-full border rounded px-3 py-2">
                            <option value="">{{ __('app.client.edit.city_choose') }}</option>
                            @foreach($fizCities as $id => $city)
                                <option value="{{ $id }}">{{ is_array($city) ? ($city['name'] ?? $city) : $city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="fiz_address" placeholder="{{ __('app.client.edit.street_placeholder') }}"
                           class="border rounded px-3 py-2">
                    <input type="text" wire:model="fiz_post_code" placeholder="{{ __('app.client.edit.postcode') }}"
                           class="border rounded px-3 py-2">
                </div>
            </div>

            <div class="border-t pt-4">
                <h3 class="text-lg font-semibold mb-3">{{ __('app.client.edit.bank_details') }}</h3>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="bank_name" placeholder="{{ __('app.client.edit.bank_name') }}" class="border rounded px-3 py-2">
                    <input type="text" wire:model="swift" placeholder="{{ __('app.client.edit.swift') }}" class="border rounded px-3 py-2">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('clients.index') }}"
                   class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">{{ __('app.client.edit.cancel') }}</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    💾 {{ __('app.client.edit.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
