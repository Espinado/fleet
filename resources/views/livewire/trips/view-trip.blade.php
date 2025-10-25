<div class="max-w-5xl mx-auto bg-white shadow rounded-lg p-8 space-y-8">

    {{-- Header --}}
    <div class="flex justify-between items-center border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">📄 CMR — International Consignment Note</h2>
        <span class="text-gray-500">№ {{ $trip->id }}</span>
    </div>

    {{-- EXPEDITOR --}}
    <section>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">1️⃣ Sender / Expeditor</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <p><b>Company:</b> {{ $trip->expeditor_name }}</p>
            <p><b>Reg. Nr:</b> {{ $trip->expeditor_reg_nr }}</p>
            <p><b>Address:</b> {{ $trip->expeditor_address }}, {{ $trip->expeditor_city }}</p>
            <p><b>Country:</b> {{ $trip->expeditor_country }}</p>
            <p><b>Email:</b> {{ $trip->expeditor_email }}</p>
            <p><b>Phone:</b> {{ $trip->expeditor_phone }}</p>
        </div>
    </section>

    {{-- SHIPPER / CONSIGNEE --}}
    <section>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">2️⃣ Parties</h3>
        <div class="grid grid-cols-2 gap-6 border p-4 rounded-lg">
            <div>
                <h4 class="font-semibold text-blue-600">📤 Shipper (Sender)</h4>
                @if($trip->shipper)
                    <p><b>Company:</b> {{ $trip->shipper->company_name }}</p>
                    <p><b>Address:</b> {{ $trip->shipper->fiz_address }}, {{ $trip->shipper->fiz_city }}, {{ $trip->shipper->fiz_country }}</p>
                    <p><b>Email:</b> {{ $trip->shipper->email }}</p>
                    <p><b>Phone:</b> {{ $trip->shipper->phone }}</p>
                @endif
            </div>
            <div>
                <h4 class="font-semibold text-green-600">📥 Consignee (Receiver)</h4>
                @if($trip->consignee)
                    <p><b>Company:</b> {{ $trip->consignee->company_name }}</p>
                    <p><b>Address:</b> {{ $trip->consignee->fiz_address }}, {{ $trip->consignee->fiz_city }}, {{ $trip->consignee->fiz_country }}</p>
                    <p><b>Email:</b> {{ $trip->consignee->email }}</p>
                    <p><b>Phone:</b> {{ $trip->consignee->phone }}</p>
                @endif
            </div>
        </div>
    </section>

    {{-- ROUTE --}}
    <section>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">3️⃣ Route & Dates</h3>
        <div class="grid grid-cols-2 gap-6 text-sm">
            <div class="bg-blue-50 p-3 rounded">
                <p><b>📍 Loading:</b></p>
                <p>{{ getCountryById($trip->origin_country_id)['name'] ?? '-' }}, {{ getCityNameByCountryId($trip->origin_country_id, $trip->origin_city_id) }}</p>
                <p>{{ $trip->origin_address }}</p>
                <p><b>Date:</b> {{ $trip->start_date?->format('d.m.Y') ?? '-' }}</p>
            </div>
            <div class="bg-green-50 p-3 rounded">
                <p><b>🏁 Unloading:</b></p>
                <p>{{ getCountryById($trip->destination_country_id)['name'] ?? '-' }}, {{ getCityNameByCountryId($trip->destination_country_id, $trip->destination_city_id) }}</p>
                <p>{{ $trip->destination_address }}</p>
                <p><b>Date:</b> {{ $trip->end_date?->format('d.m.Y') ?? '-' }}</p>
            </div>
        </div>
    </section>

    {{-- TRANSPORT --}}
    <section>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">4️⃣ Transport</h3>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <p><b>Driver:</b> {{ $trip->driver?->first_name }} {{ $trip->driver?->last_name }}</p>
            <p><b>Truck:</b> {{ $trip->truck?->brand }} {{ $trip->truck?->model }} ({{ $trip->truck?->plate }})</p>
            <p><b>Trailer:</b> {{ $trip->trailer?->brand }} ({{ $trip->trailer?->plate }})</p>
        </div>
    </section>

    {{-- CARGO --}}
    <section>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">5️⃣ Cargo Details</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <p><b>Description:</b> {{ $trip->cargo_description }}</p>
            <p><b>Packages:</b> {{ $trip->cargo_packages }}</p>
            <p><b>Weight:</b> {{ $trip->cargo_weight }} kg</p>
            <p><b>Volume:</b> {{ $trip->cargo_volume }} m³</p>
            <p><b>Marks:</b> {{ $trip->cargo_marks }}</p>
            <p><b>Instructions:</b> {{ $trip->cargo_instructions }}</p>
            <p><b>Remarks:</b> {{ $trip->cargo_remarks }}</p>
        </div>
    </section>

    {{-- PAYMENT --}}
    <section>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">6️⃣ Payment & Status</h3>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <p><b>Price:</b> {{ $trip->price }} {{ $trip->currency }}</p>
            <p><b>Payment Due:</b> {{ $trip->payment_terms?->format('d.m.Y') ?? '-' }}</p>
         <p><b>Status:</b> {{ $trip->status_label }}</p>
        </div>
    </section>

    {{-- BUTTON --}}
    <div class="flex justify-end pt-6 border-t">
        <a href="{{ route('trips.index') }}"
           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-gray-800 transition">← Back</a>
    </div>

</div>
