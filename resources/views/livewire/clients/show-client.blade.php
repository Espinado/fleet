<div class="p-6">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 max-w-5xl mx-auto relative space-y-8">
        {{-- 🔹 Заголовок --}}
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800">
                🏢 {{ $client->company_name }}
            </h2>

            <div class="flex gap-2">
                <a href="{{ route('clients.edit', $client->id) }}"
                   class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                    ✏️ Edit
                </a>
                <a href="{{ route('clients.index') }}"
                   class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                    ← Back
                </a>
            </div>
        </div>

        {{-- 🔸 Информация о компании --}}
        <div class="space-y-4 text-gray-700">
            <div>
                <h3 class="font-semibold text-lg mb-2">Company Info</h3>
                <p><span class="font-medium">Reg. Nr:</span> {{ $client->reg_nr ?? '-' }}</p>
                <p><span class="font-medium">Representative:</span> {{ $client->representative ?? '-' }}</p>
                <p><span class="font-medium">Email:</span> {{ $client->email ?? '-' }}</p>
                <p><span class="font-medium">Phone:</span> {{ $client->phone ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-8">
                {{-- Юр. адрес --}}
                <div>
                    <h3 class="font-semibold text-lg mb-2">Legal Address</h3>
                    <p>{{ $client->jur_address ?? '-' }}</p>
                    <p> {{ getCityById($client->jur_city_id ?? null, $client->jur_country_id ?? null) }} {{ $client->jur_post_code ?? '' }}</p>
                    <p>{{ getCountryById($client->jur_country_id ?? null) }}</p>
                </div>

                {{-- Физ. адрес --}}
                <div>
                    <h3 class="font-semibold text-lg mb-2">Physical Address</h3>
                    <p>{{ $client->fiz_address ?? '-' }}</p>
                    <p> {{ getCityById($client->fiz_city_id ?? null, $client->jur_country_id ?? null) }} {{ $client->fiz_post_code ?? '' }}</p>
                    <p>{{ getCountryById($client->fiz_country_id ?? null) }}</p>
                </div>
            </div>

            {{-- Банк --}}
            <div>
                <h3 class="font-semibold text-lg mb-2">Bank Details</h3>
                <p><span class="font-medium">Bank Name:</span> {{ $client->bank_name ?? '-' }}</p>
                <p><span class="font-medium">SWIFT:</span> {{ $client->swift ?? '-' }}</p>
            </div>
        </div>

        {{-- 📄 CMR документы --}}
        <div class="pt-8 border-t mt-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">📦 CMR Documents</h3>

            @php
                // Грузовые записи, где этот клиент был отправителем или получателем
                $cmrCargos = \App\Models\TripCargo::where('shipper_id', $client->id)
                    ->orWhere('consignee_id', $client->id)
                    ->with('trip')
                    ->orderByDesc('created_at')
                    ->get();
            @endphp

            @if($cmrCargos->isEmpty())
                <p class="text-gray-500 italic">No CMR documents found for this client.</p>
            @else
                <table class="w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr class="text-left text-gray-700">
                            <th class="p-2 border">#</th>
                            <th class="p-2 border">Trip ID</th>
                            <th class="p-2 border">Role</th>
                            <th class="p-2 border">Loading Date</th>
                            <th class="p-2 border">Unloading Date</th>
                            <th class="p-2 border text-center">CMR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cmrCargos as $index => $cargo)
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 border text-gray-600">{{ $index + 1 }}</td>
                                <td class="p-2 border">
                                    <a href="{{ route('trips.show', $cargo->trip_id) }}"
                                       class="text-blue-600 hover:underline">
                                        #{{ $cargo->trip_id }}
                                    </a>
                                </td>
                                <td class="p-2 border">
                                    @if($cargo->shipper_id === $client->id)
                                        <span class="text-green-600 font-semibold">→ Shipper</span>
                                    @elseif($cargo->consignee_id === $client->id)
                                        <span class="text-blue-600 font-semibold">← Consignee</span>
                                    @endif
                                </td>
                                <td class="p-2 border">
                                    {{ $cargo->loading_date ? \Carbon\Carbon::parse($cargo->loading_date)->format('d.m.Y') : '—' }}
                                </td>
                                <td class="p-2 border">
                                    {{ $cargo->unloading_date ? \Carbon\Carbon::parse($cargo->unloading_date)->format('d.m.Y') : '—' }}
                                </td>
                                <td class="p-2 border text-center">
                                    @if($cargo->cmr_file && Storage::disk('public')->exists(str_replace('storage/', '', $cargo->cmr_file)))
                                        @if($cargo->cmr_file)
    <a href="{{ asset('storage/' . ltrim($cargo->cmr_file, '/')) }}"
       target="_blank"
       class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md shadow hover:bg-blue-700 transition">
        📄 View CMR
    </a>

    @if($cargo->cmr_created_at)
        <div class="text-[10px] text-gray-500 mt-1">
            {{ \Carbon\Carbon::parse($cargo->cmr_created_at)->format('d.m.Y H:i') }}
        </div>
    @endif
@endif
                                        
                                    @else
                                        <span class="text-gray-400 text-xs italic">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
