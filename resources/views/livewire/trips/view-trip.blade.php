<div class="max-w-6xl mx-auto p-6 space-y-8" wire:ignore.self>
    {{-- ✅ Уведомления --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
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
                <p><b>Address:</b> {{ $trip->expeditor_address ?? '—' }}</p>
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

        {{-- 3️⃣ Cargo Details (по парам компаний) --}}
       <section>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">3️⃣ Cargo Details</h3>

    @php
        $grouped = $trip->cargos->groupBy(fn($c) => $c->shipper_id . '-' . $c->consignee_id);
    @endphp

    @forelse($grouped as $pair => $group)
        @php
            $first     = $group->first();
            $shipper   = $first->shipper?->company_name ?? '—';
            $consignee = $first->consignee?->company_name ?? '—';
            $exists    = !empty($first->cmr_file) && Storage::exists('public/' . $first->cmr_file);
            $url       = $exists ? asset('storage/' . $first->cmr_file) : null;
        @endphp

        <div class="border border-gray-200 rounded-lg p-4 mb-6 bg-gray-50">
            <div class="flex justify-between items-start gap-3">
                <h4 class="font-semibold text-blue-600">
                    {{ $shipper }} → {{ $consignee }}
                   @php
    $fromCountry = getCountryById((int) $first->loading_country_id);
    $fromCity    = getCityById((int) $first->loading_city_id, (int) $first->loading_country_id);

    $toCountry   = getCountryById((int) $first->unloading_country_id);
    $toCity      = getCityById((int) $first->unloading_city_id, (int) $first->unloading_country_id);
@endphp

<h4 class="font-semibold text-blue-600">
    {{ $shipper }} → {{ $consignee }}
    <span class="text-gray-600 text-sm ml-1">
        ({{ $fromCountry ?? '—' }}, {{ $fromCity ?? '—' }} → {{ $toCountry ?? '—' }}, {{ $toCity ?? '—' }})
    </span>

    @if ($exists)
        <span class="ml-2 text-xs px-2 py-0.5 bg-green-100 text-green-800 rounded">Generated</span>
    @endif
</h4>

               <div class="shrink-0">
    @if ($first->cmr_file)
        {{-- ✅ Уже создан — показываем ссылку --}}
        <a href="{{ asset('storage/' . $first->cmr_file) }}" target="_blank"
           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded">
            👁 View CMR
        </a>

        <div class="text-[11px] text-gray-500 mt-1">
            Created: {{ \Carbon\Carbon::parse($first->cmr_created_at)->format('d.m.Y H:i') }}
        </div>
    @else
        {{-- ❌ Не создан — Livewire-кнопка --}}
        <button wire:click="generateCmr({{ $first->id }})"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded">
            <span wire:loading.remove wire:target="generateCmr({{ $first->id }})">📄 Generate CMR</span>
            <span wire:loading wire:target="generateCmr({{ $first->id }})" class="animate-pulse">⏳ Generating...</span>
        </button>
    @endif
</div>
            </div>

            {{-- 📦 Список грузов этой пары --}}
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                @foreach($group as $cargo)
                    <div class="border rounded p-3 bg-white">
                        <p class="font-semibold text-gray-700 mb-1">📦 {{ $cargo->cargo_description ?? '—' }}</p>
                        <p><b>Weight:</b> {{ number_format($cargo->cargo_weight ?? 0, 2, '.', ' ') }} kg</p>
                        <p><b>Price:</b> {{ number_format($cargo->price ?? 0, 2, '.', ' ') }} {{ $cargo->currency ?? 'EUR' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-gray-500 italic">No cargos found.</p>
    @endforelse
</section>

        {{-- Back --}}
        <div class="pt-6">
            <a href="{{ route('trips.index') }}"
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-gray-800">
                ⬅ Back to Trips
            </a>
        </div>
    </div>
</div>
