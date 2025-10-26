<div class="max-w-6xl mx-auto p-6 space-y-8">

    {{-- ✅ Уведомление --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 space-y-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            🚛 CMR Trip #{{ $trip->id }}
        </h2>

        {{-- 1️⃣ Expeditor --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">1️⃣ Expeditor Company</h3>
            <div class="text-sm leading-relaxed">
                <p><b>Name:</b> {{ $trip->expeditor_name ?? '—' }}</p>
                <p><b>Reg. Nr:</b> {{ $trip->expeditor_reg_nr ?? '—' }}</p>
                <p>
                    <b>Address:</b>
                    {{ $trip->expeditor_address ?? '—' }},
                    {{ $trip->expeditor_city ?? '—' }}
                </p>
                <p><b>Country:</b> {{ $trip->expeditor_country ?? '—' }}</p>
                <p><b>Email:</b> {{ $trip->expeditor_email ?? '—' }}</p>
                <p><b>Phone:</b> {{ $trip->expeditor_phone ?? '—' }}</p>
            </div>
        </section>

        {{-- 2️⃣ Transport --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">2️⃣ Transport Details</h3>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <p><b>Driver:</b> {{ $trip->driver?->first_name }} {{ $trip->driver?->last_name }}</p>
                <p><b>Truck:</b> {{ $trip->truck?->plate }} — {{ $trip->truck?->brand }} {{ $trip->truck?->model }}</p>
                <p><b>Trailer:</b> {{ $trip->trailer?->plate ?? '—' }}</p>
            </div>
        </section>

        {{-- 3️⃣ Cargo Details --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">3️⃣ Cargo Details</h3>

            @forelse($trip->cargos as $index => $cargo)
                <div class="border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                    <h4 class="font-semibold mb-3 text-blue-600">
                        #{{ $index + 1 }}
                        — {{ $cargo->shipper?->company_name ?? '—' }}
                        → {{ $cargo->consignee?->company_name ?? '—' }}
                    </h4>

                    <div class="grid grid-cols-2 gap-6 text-sm">
                        {{-- 📍 Loading --}}
                        <div>
                            <h5 class="font-semibold mb-1 text-gray-700">📍 Loading</h5>
                            <p><b>Country:</b> {{ getCountryById((int) $cargo->loading_country_id) ?? '—' }}</p>
                            <p>
                                <b>City:</b>
                                {{ getCityById((int) $cargo->loading_city_id, (int) $cargo->loading_country_id) }}
                            </p>
                            <p><b>Address:</b> {{ $cargo->loading_address ?? '—' }}</p>
                            <p>
                                <b>Date:</b>
                                {{ $cargo->loading_date ? \Carbon\Carbon::parse($cargo->loading_date)->format('d.m.Y') : '—' }}
                            </p>
                        </div>

                        {{-- 🏁 Unloading --}}
                        <div>
                            <h5 class="font-semibold mb-1 text-gray-700">🏁 Unloading</h5>
                            <p><b>Country:</b> {{ getCountryById((int) $cargo->unloading_country_id) ?? '—' }}</p>
                            <p>
                                <b>City:</b>
                                {{ getCityById((int) $cargo->unloading_city_id, (int) $cargo->unloading_country_id) }}
                            </p>
                            <p><b>Address:</b> {{ $cargo->unloading_address ?? '—' }}</p>
                            <p>
                                <b>Date:</b>
                                {{ $cargo->unloading_date ? \Carbon\Carbon::parse($cargo->unloading_date)->format('d.m.Y') : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- 📦 Cargo Info --}}
                    <div class="mt-4 grid grid-cols-2 gap-6 text-sm">
                        <div>
                            <h5 class="font-semibold mb-1 text-gray-700">📦 Cargo Info</h5>
                            <p><b>Description:</b> {{ $cargo->cargo_description ?? '—' }}</p>
                            <p><b>Packages:</b> {{ $cargo->cargo_packages ?? '—' }}</p>
                            <p><b>Weight:</b> {{ number_format($cargo->cargo_weight ?? 0, 2) }} kg</p>
                            <p><b>Volume:</b> {{ number_format($cargo->cargo_volume ?? 0, 2) }} m³</p>
                            <p><b>Marks:</b> {{ $cargo->cargo_marks ?? '—' }}</p>
                        </div>

                        <div>
                            <h5 class="font-semibold mb-1 text-gray-700">💶 Payment</h5>
                            <p>
                                <b>Price:</b>
                                {{ $cargo->price ? number_format($cargo->price, 2) : '—' }}
                                {{ $cargo->currency ?? 'EUR' }}
                            </p>
                            <p>
                                <b>Payment Due:</b>
                                {{ $cargo->payment_terms ? \Carbon\Carbon::parse($cargo->payment_terms)->format('d.m.Y') : '—' }}
                            </p>
                            <p>
                                <b>Payer:</b>
                                {{ config('payers.' . ($cargo->payer_type_id ?? 0) . '.label') ?? '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- 📄 Additional --}}
                    <div class="mt-3 text-sm text-gray-700">
                        <p><b>Instructions:</b> {{ $cargo->cargo_instructions ?? '—' }}</p>
                        <p><b>Remarks:</b> {{ $cargo->cargo_remarks ?? '—' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 italic">No cargo records found.</p>
            @endforelse

            {{-- Totals --}}
            <div class="border-t pt-3 text-sm">
                <p><b>Total Cargos:</b> {{ $trip->cargos->count() }}</p>
                <p><b>Total Weight:</b> {{ number_format($trip->cargos->sum('cargo_weight'), 2) }} kg</p>
            </div>
        </section>

        {{-- 4️⃣ Status --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">4️⃣ Trip Status</h3>
            <span class="inline-block px-3 py-1 text-sm rounded {{ $trip->status->color() }}">
                {{ $trip->status->label() }}
            </span>
        </section>

        <div class="pt-6">
            <a href="{{ route('trips.index') }}"
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-gray-800">
                ⬅ Back to Trips
            </a>
        </div>
    </div>
</div>
