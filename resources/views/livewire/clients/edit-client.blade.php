<div class="p-6 max-w-4xl mx-auto">
    {{-- ✅ Уведомление --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-6">✏️ Edit Client</h2>

        {{-- 🌍 Лоадер --}}
        <div wire:loading.flex wire:target="save, jur_country_id, fiz_country_id"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20 rounded-lg">
            <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6 relative">
            {{-- Те же поля, что и в форме создания --}}
            <div>
                <label class="block text-sm font-medium mb-1">Company Name *</label>
                <input type="text" wire:model="company_name" class="w-full border rounded px-3 py-2">
                @error('company_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Reg. Nr</label>
                    <input type="text" wire:model="reg_nr" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Representative</label>
                    <input type="text" wire:model="representative" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            {{-- Контакты --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" wire:model="email" class="w-full border rounded px-3 py-2">
                    @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" wire:model="phone" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            {{-- Юридический адрес --}}
            <div class="border-t pt-4 space-y-4">
                <h3 class="text-lg font-semibold mb-2">Legal (Jur.) Address</h3>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Country --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Country *</label>
                        <select wire:model.live="jur_country_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Country</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                        @error('jur_country_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">City *</label>
                        <select wire:model.live="jur_city_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select City</option>
                            @foreach($jurCities as $id => $city)
                                <option value="{{ $id }}">{{ $city['name'] }}</option>
                            @endforeach
                        </select>
                        @error('jur_city_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="jur_address" placeholder="Street, house, apt"
                           class="border rounded px-3 py-2">
                    <input type="text" wire:model="jur_post_code" placeholder="Post code"
                           class="border rounded px-3 py-2">
                </div>
            </div>

            {{-- Фактический адрес --}}
            <div class="border-t pt-4 space-y-4">
                <h3 class="text-lg font-semibold mb-2">Physical (Fiz.) Address</h3>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Country --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Country</label>
                        <select wire:model.live="fiz_country_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Country</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">City</label>
                        <select wire:model="fiz_city_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select City</option>
                            @foreach($fizCities as $id => $city)
                                <option value="{{ $id }}">{{ $city['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="fiz_address" placeholder="Street, house, apt"
                           class="border rounded px-3 py-2">
                    <input type="text" wire:model="fiz_post_code" placeholder="Post code"
                           class="border rounded px-3 py-2">
                </div>
            </div>

            {{-- Банковские данные --}}
            <div class="border-t pt-4">
                <h3 class="text-lg font-semibold mb-3">Bank Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="bank_name" placeholder="Bank name" class="border rounded px-3 py-2">
                    <input type="text" wire:model="swift" placeholder="SWIFT code" class="border rounded px-3 py-2">
                </div>
            </div>


            {{-- Кнопки --}}
            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('clients.index') }}"
                   class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    💾 Update
                </button>
            </div>
        </form>
    </div>
</div>
