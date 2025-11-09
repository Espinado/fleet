<div class="p-4 sm:p-6 max-w-6xl mx-auto space-y-10 relative bg-gray-50 min-h-screen">

    {{-- ✅ Уведомление --}}
    @if ($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg shadow-sm">
            {{ $successMessage }}
        </div>
    @endif

    {{-- 🔄 Лоадер --}}
    <div wire:loading.flex wire:target="save, addCargo, removeCargo, addCargoItem, removeCargoItem"
         class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 text-center">🚛 Create New Trip with Multiple Clients</h2>

    <form wire:submit.prevent="save" class="space-y-10">

        {{-- 1️⃣ Expeditor --}}
        <section class="border-b pb-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">1️⃣ Expeditor Company</h3>

            <x-select label="Select Expeditor *" model="expeditor_id" :options="$companies" live />
            @error('expeditor_id')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror

            @if($expeditorData)
                <div class="mt-2 bg-gray-50 border border-gray-200 rounded p-4 text-sm leading-relaxed">
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
            <h3 class="text-lg font-semibold text-gray-800">2️⃣ Transport Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <x-select label="Driver *" model="driver_id" :options="$drivers" />
                    @error('driver_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <x-select label="Truck *" model="truck_id" :options="$trucks" />
                    @error('truck_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <x-select label="Trailer" model="trailer_id" :options="$trailers" />
                    @error('trailer_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- 3️⃣ Cargo sections --}}
        <section class="space-y-10">
            <h3 class="text-lg font-semibold text-gray-800">3️⃣ Client Shipments</h3>

            @foreach ($cargos as $index => $cargo)
                <div class="border rounded-xl p-5 bg-white shadow-sm relative"
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
                        <x-select label="Select Customer *"
                                  model="cargos.{{ $index }}.customer_id"
                                  :options="$customers"
                                  live />
                        @error('cargos.' . $index . '.customer_id')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 🧾 Shipper / Consignee --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-select label="Select Shipper *"
                                      model="cargos.{{ $index }}.shipper_id"
                                      :options="$clients"
                                      live />
                            @error('cargos.' . $index . '.shipper_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-select label="Select Consignee *"
                                      model="cargos.{{ $index }}.consignee_id"
                                      :options="$clients"
                                      live />
                            @error('cargos.' . $index . '.consignee_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 🗺 Route --}}
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <h5 class="font-medium text-gray-700 mb-1">📍 Loading</h5>

                            <x-select label="Country *"
                                      model="cargos.{{ $index }}.loading_country_id"
                                      :options="$countries" live />
                            @error('cargos.' . $index . '.loading_country_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <label class="block text-sm font-medium mb-1 mt-2">City *</label>
                            <select wire:model.live="cargos.{{ $index }}.loading_city_id"
                                    class="border rounded p-2 w-full">
                                <option value="">— Select city —</option>
                                @foreach(($cargo['loadingCities'] ?? []) as $cid => $cname)
                                    <option value="{{ $cid }}">{{ $cname }}</option>
                                @endforeach
                            </select>
                            @error('cargos.' . $index . '.loading_city_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <x-input label="Address *" model="cargos.{{ $index }}.loading_address" placeholder="Street, building..." />
                            @error('cargos.' . $index . '.loading_address')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <x-input type="date" label="Date *" model="cargos.{{ $index }}.loading_date" />
                            @error('cargos.' . $index . '.loading_date')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <h5 class="font-medium text-gray-700 mb-1">🏁 Unloading</h5>

                            <x-select label="Country *"
                                      model="cargos.{{ $index }}.unloading_country_id"
                                      :options="$countries" live />
                            @error('cargos.' . $index . '.unloading_country_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <label class="block text-sm font-medium mb-1 mt-2">City *</label>
                            <select wire:model.live="cargos.{{ $index }}.unloading_city_id"
                                    class="border rounded p-2 w-full">
                                <option value="">— Select city —</option>
                                @foreach(($cargo['unloadingCities'] ?? []) as $cid => $cname)
                                    <option value="{{ $cid }}">{{ $cname }}</option>
                                @endforeach
                            </select>
                            @error('cargos.' . $index . '.unloading_city_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <x-input label="Address *" model="cargos.{{ $index }}.unloading_address" placeholder="Street, building..." />
                            @error('cargos.' . $index . '.unloading_address')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <x-input type="date" label="Date *" model="cargos.{{ $index }}.unloading_date" />
                            @error('cargos.' . $index . '.unloading_date')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
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

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <x-textarea label="Description *" model="cargos.{{ $index }}.items.{{ $itemIndex }}.description" rows="2" />
                                    @error('cargos.' . $index . '.items.' . $itemIndex . '.description')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <x-input type="number" label="Packages" model="cargos.{{ $index }}.items.{{ $itemIndex }}.packages" min="1" />
                                    @error('cargos.' . $index . '.items.' . $itemIndex . '.packages')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <x-input type="number" label="Gross Weight (kg)" model="cargos.{{ $index }}.items.{{ $itemIndex }}.weight" step="0.01" />
                                    @error('cargos.' . $index . '.items.' . $itemIndex . '.weight')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <x-input type="number" label="Price (€)" model="cargos.{{ $index }}.items.{{ $itemIndex }}.price_with_tax" step="0.01" />
                                    @error('cargos.' . $index . '.items.' . $itemIndex . '.price_with_tax')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-3">
                        <button type="button" wire:click="addCargoItem({{ $index }})"
                                class="px-3 py-1.5 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                            ➕ Add Item
                        </button>
                    </div>

                    {{-- 💶 Payment --}}
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input type="date" label="Payment Due Date" model="cargos.{{ $index }}.payment_terms" />
                            @error('cargos.' . $index . '.payment_terms')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-select label="Payer Type" model="cargos.{{ $index }}.payer_type_id" :options="$payerTypes" />
                            @error('cargos.' . $index . '.payer_type_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-select label="Tax (%)" model="cargos.{{ $index }}.tax_percent"
                                      :options="[0 => '0%', 10 => '10%', 21 => '21%']" />
                            @error('cargos.' . $index . '.tax_percent')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="flex justify-between items-center pt-4 border-t">
                <button type="button" wire:click="addCargo"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    ➕ Add Another Cargo
                </button>
            </div>
        </section>

        {{-- 4️⃣ Status --}}
        <section class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800">4️⃣ Trip Status</h3>
            <x-select label="Trip Status"
                      model="status"
                      :options="['planned' => 'Planned', 'in_progress' => 'In Progress', 'completed' => 'Completed']" />
            @error('status') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </section>

        {{-- 🟢 Fixed Save Button for PWA --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t p-4 flex justify-end gap-3 z-40 sm:static sm:bg-transparent sm:border-0">
            <button type="button" onclick="window.history.back()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition">
                Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center gap-2 transition">
                💾 Save Trip
            </button>
        </div>
    </form>
</div>

{{-- ⚡ Scroll to first error --}}
<script>
    window.addEventListener('scrollToError', () => {
        const firstError = document.querySelector('.text-red-600');
        if (firstError) {
            const fieldContainer = firstError.closest('div');
            fieldContainer?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            fieldContainer?.classList.add('ring-2', 'ring-red-400', 'ring-offset-2');
            setTimeout(() => fieldContainer?.classList.remove('ring-2', 'ring-red-400', 'ring-offset-2'), 2000);
        }
    });
</script>
