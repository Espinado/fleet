<div class="p-6 max-w-5xl mx-auto space-y-8">

    {{-- ✅ Уведомление --}}
    @if ($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ $successMessage }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 space-y-8 relative">

        {{-- 🔄 Лоадер --}}
        <div wire:loading.flex wire:target="save"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-20">
            <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <h2 class="text-2xl font-semibold">📦 Create New CMR Trip</h2>

        <form wire:submit.prevent="save" class="space-y-10">

            {{-- 1️⃣ Expeditor --}}
            <section class="border-b pb-6">
                <h3 class="text-lg font-semibold mb-3">Expeditor Company</h3>
                <x-select label="Company *" model="expeditor_id" :options="$companies" live />

                {{-- Карточка компании --}}
                @if($expeditorData)
                    <div class="mt-3 bg-gray-50 border border-gray-200 rounded p-4 text-sm">
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
            <section class="border-b pb-6">
                <h3 class="text-lg font-semibold mb-3">Transport Details</h3>
                <div class="grid grid-cols-3 gap-6">
                    <x-select label="Driver *" model="driver_id" :options="$drivers" />
                    <x-select label="Truck *" model="truck_id" :options="$trucks" />
                    <x-select label="Trailer" model="trailer_id" :options="$trailers" />
                </div>
            </section>

            {{-- 3️⃣ Shipper & Consignee --}}
            <section class="border-b pb-6 space-y-6">
                <h3 class="text-lg font-semibold mb-3">Shipper & Consignee</h3>

                <div class="grid grid-cols-2 gap-6">
                    {{-- 📤 Shipper --}}
                    <div>
                        <h4 class="font-semibold mb-2 text-blue-600">📤 Shipper (Sender)</h4>
                        <x-select label="Client *" model="shipperId" :options="$clients" live />

                        @if($shipperData)
                            <div class="mt-3 bg-gray-50 border border-blue-200 rounded p-4 text-sm">
                                <p><b>Company:</b> {{ $shipperData['company_name'] ?? '-' }}</p>
                                <p><b>Email:</b> {{ $shipperData['email'] ?? '-' }}</p>
                                <p><b>Phone:</b> {{ $shipperData['phone'] ?? '-' }}</p>
                                <p><b>Address:</b>
                                    {{ $shipperData['fiz_address'] ?? '' }},
                                    {{ $shipperData['fiz_city'] ?? '' }},
                                    {{ $shipperData['fiz_country'] ?? '' }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-4 space-y-3">
                            <x-input type="date" label="Loading Date" model="start_date" />
                            <x-select label="Loading Country *" model="origin_country_id" :options="$countries" live />
                            <x-select label="Loading City *" model="origin_city_id" :options="$originCities" />
                            <x-input label="Loading Address *" model="origin_address" placeholder="Street, building..." />
                        </div>
                    </div>

                    {{-- 📥 Consignee --}}
                    <div>
                        <h4 class="font-semibold mb-2 text-green-600">📥 Consignee (Receiver)</h4>
                        <x-select label="Client *" model="consigneeId" :options="$clients" live />

                        @if($consigneeData)
                            <div class="mt-3 bg-gray-50 border border-green-200 rounded p-4 text-sm">
                                <p><b>Company:</b> {{ $consigneeData['company_name'] ?? '-' }}</p>
                                <p><b>Email:</b> {{ $consigneeData['email'] ?? '-' }}</p>
                                <p><b>Phone:</b> {{ $consigneeData['phone'] ?? '-' }}</p>
                                <p><b>Address:</b>
                                    {{ $consigneeData['fiz_address'] ?? '' }},
                                    {{ $consigneeData['fiz_city'] ?? '' }},
                                    {{ $consigneeData['fiz_country'] ?? '' }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-4 space-y-3">
                            <x-input type="date" label="Unloading Date" model="end_date" />
                            <x-select label="Unloading Country *" model="destination_country_id" :options="$countries" live />
                            <x-select label="Unloading City *" model="destination_city_id" :options="$destinationCities" />
                            <x-input label="Unloading Address *" model="destination_address" placeholder="Street, building..." />
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4️⃣ Cargo --}}
            <section class="border-b pb-6">
                <h3 class="text-lg font-semibold mb-3">Cargo Details</h3>
                <div class="grid grid-cols-2 gap-6">
                    <x-textarea label="Description of Goods *" model="cargo_description" rows="3" />
                    <x-input type="number" label="Number of Packages" model="cargo_packages" min="1" />
                    <x-input type="number" label="Gross Weight (kg)" model="cargo_weight" step="0.01" />
                    <x-input type="number" label="Volume (m³)" model="cargo_volume" step="0.01" />
                </div>
                <x-textarea label="Marks / Instructions" model="cargo_instructions" rows="2" />
                <x-textarea label="Remarks" model="cargo_remarks" rows="2" />
            </section>

            {{-- 5️⃣ Payment & Pricing --}}
            <section class="border-b pb-6">
                <h3 class="text-lg font-semibold mb-3">Payment & Pricing</h3>

                <div class="grid grid-cols-3 gap-6">
                    <x-input type="number" label="Price" model="price" step="0.01" />
                    <x-input label="Currency" model="currency" placeholder="EUR" />
                    <x-input type="date" label="Payment Due Date" model="payment_terms" />
                </div>

                <div class="mt-4">
                    <x-select label="Payer Type" model="payer_type_id" :options="$payerTypes" />
                </div>
            </section>

            {{-- 6️⃣ Status --}}
            <section>
                <h3 class="text-lg font-semibold mb-3">Status</h3>
                <x-select label="Trip Status" model="status"
                          :options="['planned'=>'Planned','in_progress'=>'In Progress','completed'=>'Completed']" />
            </section>

            {{-- Buttons --}}
            <div class="flex justify-end gap-3 pt-6">
                <button type="button" onclick="window.history.back()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded flex items-center gap-2 transition">
                    💾 Save CMR Trip
                </button>
            </div>
        </form>
    </div>
</div>
