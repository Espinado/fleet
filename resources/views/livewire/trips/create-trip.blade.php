<div class="p-6 max-w-6xl mx-auto space-y-10 relative">

    {{-- ✅ Уведомление --}}
    @if ($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
            {{ $successMessage }}
        </div>
    @endif

    {{-- 🔄 Лоадер --}}
    <div wire:loading.flex wire:target="save, addCargo, removeCargo, addCargoItem, removeCargoItem"
         class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
        <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
    </div>

    <h2 class="text-2xl font-bold">🚛 Create New Trip with Multiple Clients</h2>

    <form wire:submit.prevent="save" class="space-y-10">

        {{-- 1️⃣ Expeditor --}}
        <section class="border-b pb-6 space-y-4">
            <h3 class="text-lg font-semibold">Expeditor Company</h3>

            <x-select label="Select Expeditor *" model="expeditor_id" :options="$companies" live />

            @if($expeditorData)
                <div class="mt-2 bg-gray-50 border border-gray-200 rounded p-4 text-sm">
                    <p><b>Name:</b> {{ $expeditorData['name'] ?? '-' }}</p>
                    <p><b>Reg. Nr:</b> {{ $expeditorData['reg_nr'] ?? '-' }}</p>
                    <p><b>Address:</b> {{ $expeditorData['address'] ?? '-' }}, {{ $expeditorData['city'] ?? '' }}</p>
                    <p><b>Country:</b> {{ $expeditorData['country'] ?? '-' }}</p>
                    <p><b>Email:</b> {{ $expeditorData['email'] ?? '-' }}</p>
                    <p><b>Phone:</b> {{ $expeditorData['phone'] ?? '-' }}</p>
                </div>
            @endif
        </section>

        {{-- 2️⃣ Transport --}}
        <section class="border-b pb-6 space-y-4">
            <h3 class="text-lg font-semibold">Transport Details</h3>
            <div class="grid grid-cols-3 gap-6">
                <x-select label="Driver *" model="driver_id" :options="$drivers" />
                <x-select label="Truck *" model="truck_id" :options="$trucks" />
                <x-select label="Trailer" model="trailer_id" :options="$trailers" />
            </div>
        </section>

        {{-- 3️⃣ Cargo sections --}}
        <section class="space-y-10">
            <h3 class="text-lg font-semibold">Client Shipments</h3>

            @foreach ($cargos as $index => $cargo)
                <div class="border rounded-lg p-5 bg-white shadow-sm relative"
                     wire:key="cargo-{{ $index }}">
                    <div class="absolute top-2 right-2">
                        <button type="button" wire:click="removeCargo({{ $index }})"
                                class="text-red-500 hover:text-red-700 text-sm">✖</button>
                    </div>

                    <h4 class="font-semibold text-blue-700 mb-3">
                        📦 Cargo #{{ (int) $index + 1 }}
                    </h4>

                    {{-- 👤 Customer --}}
                    <div class="mb-6">
                        <x-select
                            label="Select Customer *"
                            model="cargos.{{ $index }}.customer_id"
                            :options="$customers"
                            live
                        />

                        @if(!empty($cargo['customerData']))
                            <div class="mt-2 bg-blue-50 border border-blue-200 rounded p-3 text-xs space-y-1">
                                <p><b>🏢 {{ $cargo['customerData']['company_name'] ?? '-' }}</b></p>
                                <p>📧 {{ $cargo['customerData']['email'] ?? '-' }}</p>
                                <p>📞 {{ $cargo['customerData']['phone'] ?? '-' }}</p>
                                <p>📍 {{ $cargo['customerData']['fiz_address'] ?? '-' }},
                                    {{ $cargo['customerData']['fiz_city'] ?? '' }},
                                    {{ $cargo['customerData']['fiz_country'] ?? '' }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- 🧾 Shipper / Consignee --}}
                    <div class="grid grid-cols-2 gap-6">
                        {{-- 📤 Shipper --}}
                        <div>
                            <x-select
                                label="Select Shipper *"
                                model="cargos.{{ $index }}.shipper_id"
                                :options="$clients"
                                live
                            />

                            @if(!empty($cargo['shipperData']))
                                <div class="mt-2 bg-blue-50 border border-blue-200 rounded p-3 text-xs space-y-1">
                                    <p><b>🏢 {{ $cargo['shipperData']['company_name'] ?? '-' }}</b></p>
                                    <p>📧 {{ $cargo['shipperData']['email'] ?? '-' }}</p>
                                    <p>📞 {{ $cargo['shipperData']['phone'] ?? '-' }}</p>
                                    <p>📍 {{ $cargo['shipperData']['fiz_address'] ?? '-' }},
                                        {{ $cargo['shipperData']['fiz_city'] ?? '' }},
                                        {{ $cargo['shipperData']['fiz_country'] ?? '' }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- 📥 Consignee --}}
                        <div>
                            <x-select
                                label="Select Consignee *"
                                model="cargos.{{ $index }}.consignee_id"
                                :options="$clients"
                                live
                            />

                            @if(!empty($cargo['consigneeData']))
                                <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 text-xs space-y-1">
                                    <p><b>🏢 {{ $cargo['consigneeData']['company_name'] ?? '-' }}</b></p>
                                    <p>📧 {{ $cargo['consigneeData']['email'] ?? '-' }}</p>
                                    <p>📞 {{ $cargo['consigneeData']['phone'] ?? '-' }}</p>
                                    <p>📍 {{ $cargo['consigneeData']['fiz_address'] ?? '-' }},
                                        {{ $cargo['consigneeData']['fiz_city'] ?? '' }},
                                        {{ $cargo['consigneeData']['fiz_country'] ?? '' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 🗺 Route --}}
                    <div class="mt-5 grid grid-cols-2 gap-6">
                        {{-- 📍 Loading --}}
                        <div>
                            <h5 class="font-medium text-gray-700 mb-1">📍 Loading</h5>

                            <x-select
                                label="Country *"
                                model="cargos.{{ $index }}.loading_country_id"
                                :options="$countries"
                                live
                                :key="'loading-country-'.$index"
                            />

                            <label class="block text-sm font-medium mb-1 mt-2">City *</label>
                            <select wire:model.live="cargos.{{ $index }}.loading_city_id"
                                    :key="'loading-city-'.$index.'-'.($cargo['loading_country_id'] ?? 'none')"
                                    class="border rounded p-2 w-full">
                                <option value="">— Select city —</option>
                                @foreach(($cargo['loadingCities'] ?? []) as $cid => $cname)
                                    <option value="{{ $cid }}">{{ $cname }}</option>
                                @endforeach
                            </select>

                            <x-input label="Address *" model="cargos.{{ $index }}.loading_address" placeholder="Street, building..." />
                            <x-input type="date" label="Date *" model="cargos.{{ $index }}.loading_date" />
                        </div>

                        {{-- 🏁 Unloading --}}
                        <div>
                            <h5 class="font-medium text-gray-700 mb-1">🏁 Unloading</h5>

                            <x-select
                                label="Country *"
                                model="cargos.{{ $index }}.unloading_country_id"
                                :options="$countries"
                                live
                                :key="'unloading-country-'.$index"
                            />

                            <label class="block text-sm font-medium mb-1 mt-2">City *</label>
                            <select wire:model.live="cargos.{{ $index }}.unloading_city_id"
                                    :key="'unloading-city-'.$index.'-'.($cargo['unloading_country_id'] ?? 'none')"
                                    class="border rounded p-2 w-full">
                                <option value="">— Select city —</option>
                                @foreach(($cargo['unloadingCities'] ?? []) as $cid => $cname)
                                    <option value="{{ $cid }}">{{ $cname }}</option>
                                @endforeach
                            </select>

                            <x-input label="Address *" model="cargos.{{ $index }}.unloading_address" placeholder="Street, building..." />
                            <x-input type="date" label="Date *" model="cargos.{{ $index }}.unloading_date" />
                        </div>
                    </div>

                    {{-- 📦 Cargo Items --}}
                    @foreach ($cargo['items'] as $itemIndex => $item)
                        <div class="mt-5 border border-gray-200 rounded-lg p-4 bg-gray-50 relative"
                             wire:key="cargo-item-{{ $index }}-{{ $itemIndex }}">
                            <div class="absolute top-2 right-2">
                                @if($itemIndex > 0)
                                    <button type="button"
                                            wire:click="removeCargoItem({{ $index }}, {{ $itemIndex }})"
                                            class="text-red-500 hover:text-red-700 text-xs">✖</button>
                                @endif
                            </div>

                            <h5 class="font-semibold text-gray-700 mb-3">🧱 Item #{{ $itemIndex + 1 }}</h5>

                            <div class="grid grid-cols-2 gap-6">
                                <x-textarea label="Description *" model="cargos.{{ $index }}.items.{{ $itemIndex }}.description" rows="2" />
                                <x-input type="number" label="Packages" model="cargos.{{ $index }}.items.{{ $itemIndex }}.packages" min="1" />
                                <x-input type="number" label="Paletes" model="cargos.{{ $index }}.items.{{ $itemIndex }}.cargo_paletes" min="1" />
                                <x-input type="number" label="Gross Weight (kg)" model="cargos.{{ $index }}.items.{{ $itemIndex }}.weight" step="0.01" />
                                <x-input type="number" label="Netto Weight (kg)" model="cargos.{{ $index }}.items.{{ $itemIndex }}.cargo_netto_weight" step="0.01" />
                                <x-input type="number" label="Volume (m³)" model="cargos.{{ $index }}.items.{{ $itemIndex }}.volume" step="0.01" />
                                <x-input type="number" label="Tonnes" model="cargos.{{ $index }}.items.{{ $itemIndex }}.cargo_tonnes" step="0.01" />
                                <x-input type="number" label="Price (€)" model="cargos.{{ $index }}.items.{{ $itemIndex }}.price" step="0.01" />
                                <x-textarea label="Remarks" model="cargos.{{ $index }}.items.{{ $itemIndex }}.remarks" rows="2" />
                                <x-select 
                                    label="Tax (%)" 
                                    model="cargos.{{ $index }}.items.{{ $itemIndex }}.tax_percent" 
                                    :options="[0 => '0%', 10 => '10%', 21 => '21%']" 
                                />
                            </div>
                        </div>
                    @endforeach

                    {{-- ➕ Add new item --}}
                    <div class="pt-2">
                        <button type="button"
                                wire:click="addCargoItem({{ $index }})"
                                class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                            ➕ Add Item
                        </button>
                    </div>

                    {{-- 🧮 Client Totals --}}
                    <div class="mt-4 bg-gray-100 border border-gray-300 rounded p-3 text-sm">
                        <p><b>Client Total Weight:</b> {{ number_format((float)($cargo['cargo_weight'] ?? 0), 2) }} kg</p>
                        <p><b>Client Total Netto Weight:</b> {{ number_format((float)($cargo['cargo_netto_weight'] ?? 0), 2) }} kg</p>
                        <p><b>Client Total Volume:</b> {{ number_format((float)($cargo['cargo_volume'] ?? 0), 2) }} m³</p>
                        <p><b>Client Total Price:</b>
                            <span class="text-green-700">{{ number_format((float)($cargo['price'] ?? 0), 2) }}</span> EUR
                        </p>
                    </div>

                    {{-- 💶 Payment --}}
                    <div class="mt-5 grid grid-cols-3 gap-6">
                        <x-input type="date" label="Payment Due Date" model="cargos.{{ $index }}.payment_terms" />
                        <x-select label="Payer Type" model="cargos.{{ $index }}.payer_type_id" :options="$payerTypes" />
                    </div>
                </div>
            @endforeach

            {{-- ➕ Add Cargo --}}
            <div class="flex justify-between items-center pt-4 border-t">
                <button type="button" wire:click="addCargo"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                    ➕ Add Another Cargo
                </button>

                {{-- 💰 Totals for all clients --}}
                @php
                    $grandWeight = collect($cargos)->sum(fn($c) => (float)($c['cargo_weight'] ?? 0));
                    $grandNettoWeight = collect($cargos)->sum(fn($c) => (float)($c['cargo_netto_weight'] ?? 0));
                    $grandVolume = collect($cargos)->sum(fn($c) => (float)($c['cargo_volume'] ?? 0));
                    $grandPrice  = collect($cargos)->sum(fn($c) => (float)($c['price'] ?? 0));
                @endphp

                <div class="text-right space-y-1">
                    <p class="text-gray-700"><b>Total Weight:</b> {{ number_format((float)$grandWeight, 2) }} kg</p>
                    <p class="text-gray-700"><b>Total Netto Weight:</b> {{ number_format((float)$grandNettoWeight, 2) }} kg</p>
                    <p class="text-gray-700"><b>Total Volume:</b> {{ number_format((float)$grandVolume, 2) }} m³</p>
                    <p class="text-lg font-semibold text-gray-800">
                        Total Price: <span class="text-green-700">{{ number_format((float)$grandPrice, 2) }}</span> EUR
                    </p>
                </div>
            </div>
        </section>

        {{-- 4️⃣ Status --}}
        <section class="border-t pt-6">
            <h3 class="text-lg font-semibold">Trip Status</h3>
            <x-select
                label="Trip Status"
                model="status"
                :options="['planned' => 'Planned', 'in_progress' => 'In Progress', 'completed' => 'Completed']"
            />
        </section>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3 pt-6">
            <button type="button" onclick="window.history.back()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded transition">
                Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded flex items-center gap-2 transition">
                💾 Save Trip
            </button>
        </div>
    </form>
</div>
