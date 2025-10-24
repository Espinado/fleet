<div class="p-6 max-w-5xl mx-auto">

    {{-- ✅ Flash сообщение --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 relative">
        {{-- Лоадер всей формы при сохранении --}}
        <div wire:loading.flex wire:target="save"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
            <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <h2 class="text-2xl font-semibold mb-6">➕ Create New Trip</h2>

        <form wire:submit.prevent="save" class="space-y-6">

            {{-- === Компания (экспедитор) === --}}
            <div>
                <label class="block text-sm font-medium mb-1">Expeditor company *</label>
                <select wire:model.live="expeditorId" class="w-full border rounded px-3 py-2">
                    <option value="">Select company</option>
                    @foreach(config('companies') as $id => $c)
                        <option value="{{ $id }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
                @error('expeditorId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- === Реквизиты компании === --}}
            @if($expeditorId)
                @php($c = config('companies.' . $expeditorId))
                <div class="bg-gray-50 border rounded-lg p-6 text-gray-800">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Expeditor Company Details</h3>
                    <div class="grid grid-cols-2 gap-y-2 gap-x-6 text-sm">
                        <p><span class="font-semibold">Reg. Nr:</span> {{ $c['reg_nr'] ?? '-' }}</p>
                        <p><span class="font-semibold">Email:</span> {{ $c['email'] ?? '-' }}</p>
                        <p><span class="font-semibold">Phone:</span> {{ $c['phone'] ?? '-' }}</p>
                        <p><span class="font-semibold">Post Code:</span> {{ $c['post_code'] ?? '-' }}</p>
                        <p class="col-span-2">
                            <span class="font-semibold">Address:</span>
                            {{ $c['address'] ?? '-' }}, {{ $c['city'] ?? '' }}, {{ $c['country'] ?? '' }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- === Водитель / Тягач / Прицеп === --}}
            <div class="grid md:grid-cols-3 gap-4 relative">
                {{-- Loader при загрузке зависимых списков --}}
                <div wire:loading.flex wire:target="expeditorId"
                     class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-10 rounded">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-800"></div>
                </div>

                {{-- Driver --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Driver *</label>
                    <select wire:model="driverId" class="w-full border rounded px-3 py-2" @disabled(!$expeditorId)>
                        <option value="">Select driver</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->first_name }} {{ $d->last_name }}</option>
                        @endforeach
                    </select>
                    @error('driverId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Truck --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Truck *</label>
                    <select wire:model="truckId" class="w-full border rounded px-3 py-2" @disabled(!$expeditorId)>
                        <option value="">Select truck</option>
                        @foreach($trucks as $t)
                            <option value="{{ $t->id }}">{{ $t->brand }} {{ $t->model }} {{ $t->plate }}</option>
                        @endforeach
                    </select>
                    @error('truckId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Trailer --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Trailer</label>
                    <select wire:model="trailerId" class="w-full border rounded px-3 py-2" @disabled(!$expeditorId)>
                        <option value="">Select trailer</option>
                        @foreach($trailers as $tr)
                            <option value="{{ $tr->id }}">{{ $tr->brand }} {{ $tr->plate }}</option>
                        @endforeach
                    </select>
                    @error('trailerId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- === Клиент === --}}
            <div class="relative">
                <label class="block text-sm font-medium mb-1">Client *</label>
                <select wire:model.live="clientId" class="w-full border rounded px-3 py-2">
                    <option value="">Select client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                    @endforeach
                </select>
                <div wire:loading.flex wire:target="clientId"
                     class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-10 rounded">
                    <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
                </div>
                @error('clientId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror

                @if($selectedClient)
                    <div class="bg-gray-50 border rounded-lg p-6 mt-4 text-gray-800 transition-all duration-300">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Client Details</h3>
                        <div class="grid grid-cols-2 gap-y-2 gap-x-6 text-sm">
                            <p><span class="font-semibold">Reg. Nr:</span> {{ $selectedClient->reg_nr ?? '-' }}</p>
                            <p><span class="font-semibold">Email:</span> {{ $selectedClient->email ?? '-' }}</p>
                            <p><span class="font-semibold">Phone:</span> {{ $selectedClient->phone ?? '-' }}</p>
                            <p><span class="font-semibold">Representative:</span> {{ $selectedClient->representative ?? '-' }}</p>
                            <p class="col-span-2">
                                <span class="font-semibold">Address:</span>
                                {{ $selectedClient->jur_address ?? '-' }},
                                {{ $selectedClient->jur_city ?? '' }},
                                {{ $selectedClient->jur_country ?? '' }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- === Маршрут === --}}
         {{-- === Маршрут === --}}
<div>
    <h3 class="text-lg font-semibold border-b pb-1 mt-6">Route</h3>

    <div class="space-y-8"> {{-- ✅ вертикальный стек вместо grid --}}
        {{-- FROM --}}
        <div>
            <h4 class="font-semibold mb-2">From:</h4>

            {{-- Страна --}}
            <div class="relative mb-3">
                <label class="block text-sm font-medium mb-1">Country *</label>
                <select wire:model.live="origin_country_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>

                <div wire:loading wire:target="origin_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('origin_country_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Город --}}
            <div class="relative mb-3">
                <label class="block text-sm font-medium mb-1">City *</label>
                <select wire:model.live="origin_city_id"
                        class="w-full border rounded px-3 py-2"
                        @disabled(empty($origin_country_id))>
                    <option value="">Select city</option>
                    @if(!empty($origin_country_id))
                        @foreach(getCitiesByCountryId($origin_country_id) as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    @endif
                </select>

                <div wire:loading wire:target="origin_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('origin_city_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Адрес --}}
            <div>
                <label class="block text-sm font-medium mb-1">Address</label>
                <input type="text" wire:model="origin_address"
                       placeholder="Enter origin address"
                       class="w-full border rounded px-3 py-2">
                @error('origin_address') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- TO --}}
        <div>
            <h4 class="font-semibold mb-2">To:</h4>

            {{-- Страна --}}
            <div class="relative mb-3">
                <label class="block text-sm font-medium mb-1">Country *</label>
                <select wire:model.live="destination_country_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select country</option>
                    @foreach(config('countries') as $id => $country)
                        <option value="{{ $id }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>

                <div wire:loading wire:target="destination_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('destination_country_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Город --}}
            <div class="relative mb-3">
                <label class="block text-sm font-medium mb-1">City *</label>
                <select wire:model.live="destination_city_id"
                        class="w-full border rounded px-3 py-2"
                        @disabled(empty($destination_country_id))>
                    <option value="">Select city</option>
                    @if(!empty($destination_country_id))
                        @foreach(getCitiesByCountryId($destination_country_id) as $id => $city)
                            <option value="{{ $id }}">{{ $city['name'] }}</option>
                        @endforeach
                    @endif
                </select>

                <div wire:loading wire:target="destination_country_id"
                     class="absolute right-3 top-9">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                </div>

                @error('destination_city_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Адрес --}}
            <div>
                <label class="block text-sm font-medium mb-1">Address</label>
                <input type="text" wire:model="destination_address"
                       placeholder="Enter destination address"
                       class="w-full border rounded px-3 py-2">
                @error('destination_address') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
</div>


            {{-- === Даты === --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Start date</label>
                    <input type="date" wire:model="start_date" class="w-full border rounded px-3 py-2">
                    @error('start_date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">End date</label>
                    <input type="date" wire:model="end_date" class="w-full border rounded px-3 py-2">
                    @error('end_date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- === Груз и цена === --}}
            <div>
                <label class="block text-sm font-medium mb-1">Cargo</label>
                <input type="text" wire:model="cargo" class="w-full border rounded px-3 py-2">
                @error('cargo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Price</label>
                    <input type="number" step="0.01" wire:model="price" class="w-full border rounded px-3 py-2">
                    @error('price') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Currency</label>
                    <input type="text" wire:model="currency" class="w-full border rounded px-3 py-2">
                    @error('currency') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- === Кнопки === --}}
            <div class="flex justify-end gap-3 pt-6">
                <a href="{{ route('trips.index') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save Trip
                </button>
            </div>

        </form>
    </div>
</div>
