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
    {{-- 3️⃣ Cargo Details (по парам компаний) --}}
<section>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">3️⃣ Cargo Details</h3>

    @php
        $grouped = $trip->cargos->groupBy(fn($c) => $c->shipper_id . '-' . $c->consignee_id);
    @endphp

    @forelse($grouped as $pair => $group)
        @php
            $first      = $group->first();
            $customer   = $first->customer?->company_name ?? '—';
            $shipper    = $first->shipper?->company_name ?? '—';
            $consignee  = $first->consignee?->company_name ?? '—';
            $exists     = !empty($first->cmr_file) && Storage::exists('public/' . $first->cmr_file);
            $url        = $exists ? asset('storage/' . $first->cmr_file) : null;

            $fromCountry = getCountryById((int) $first->loading_country_id);
            $fromCity    = getCityById((int) $first->loading_city_id, (int) $first->loading_country_id);
            $toCountry   = getCountryById((int) $first->unloading_country_id);
            $toCity      = getCityById((int) $first->unloading_city_id, (int) $first->unloading_country_id);
        @endphp

        <div class="border border-gray-200 rounded-lg p-5 mb-6 bg-gray-50 shadow-sm">
            {{-- 🔹 Участники перевозки --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm mb-3">
                <div class="bg-white border border-blue-100 rounded p-2">
                    <p class="font-semibold text-blue-700">🧾 Customer</p>
                    <p>{{ $customer }}</p>
                </div>
                <div class="bg-white border border-orange-100 rounded p-2">
                    <p class="font-semibold text-orange-700">📦 Shipper</p>
                    <p>{{ $shipper }}</p>
                </div>
                <div class="bg-white border border-green-100 rounded p-2">
                    <p class="font-semibold text-green-700">🏠 Consignee</p>
                    <p>{{ $consignee }}</p>
                </div>
            </div>

            {{-- 🔹 Маршрут + кнопки CMR / Order --}}
            <div class="flex justify-between items-start gap-4 flex-wrap">
                {{-- 🔹 Кнопки действий (CMR / Order) --}}
<div class="flex flex-wrap justify-end gap-3 mt-2">

    {{-- === CMR кнопка === --}}
    @if ($first->cmr_file)
        <div class="text-center">
            <a href="{{ asset('storage/' . $first->cmr_file) }}" target="_blank"
               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded">
                👁 View CMR
            </a>
            <div class="text-[11px] text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($first->cmr_created_at)->format('d.m.Y H:i') }}
            </div>
        </div>
    @else
        <div class="text-center">
            <button wire:click="generateCmr({{ $first->id }})"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded">
                <span wire:loading.remove wire:target="generateCmr({{ $first->id }})">📄 Generate CMR</span>
                <span wire:loading wire:target="generateCmr({{ $first->id }})" class="animate-pulse">⏳ Generating...</span>
            </button>
        </div>
    @endif

    {{-- === ORDER кнопка === --}}
    @php
        $orderExists = !empty($first->order_file) && Storage::disk('public')->exists($first->order_file);
    @endphp

    @if ($orderExists)
        <div class="text-center">
            <a href="{{ asset('storage/' . $first->order_file) }}" target="_blank"
               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-purple-600 hover:bg-purple-700 text-white rounded">
                📑 View Order
            </a>
            <div class="text-[11px] text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($first->order_created_at)->format('d.m.Y H:i') }}
            </div>
        </div>
    @else
        <div class="text-center">
            <button wire:click="generateOrder({{ $first->id }})"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                <span wire:loading.remove wire:target="generateOrder({{ $first->id }})">📝 Generate Order</span>
                <span wire:loading wire:target="generateOrder({{ $first->id }})" class="animate-pulse">⏳ Generating...</span>
            </button>
        </div>
    @endif

    {{-- === INVOICE кнопка === --}}
    {{-- === INVOICE кнопка === --}}
@php
    $invoiceExists = !empty($first->inv_file) && Storage::disk('public')->exists($first->inv_file);
@endphp

@if ($invoiceExists)
    <div class="text-center">
        <a href="{{ asset('storage/' . $first->inv_file) }}" target="_blank"
           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-amber-600 hover:bg-amber-700 text-white rounded">
            🧾 View Invoice
        </a>
        <div class="text-[11px] text-gray-500 mt-1">
            {{ \Carbon\Carbon::parse($first->inv_created_at)->format('d.m.Y H:i') }}
        </div>
    </div>
@else
    <div class="text-center">
        <button wire:click="generateInvoice({{ $first->id }})"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white rounded">
            <span wire:loading.remove wire:target="generateInvoice({{ $first->id }})">🧾 Generate Invoice</span>
            <span wire:loading wire:target="generateInvoice({{ $first->id }})" class="animate-pulse">⏳ Generating...</span>
        </button>
    </div>
@endif

</div>



               
            </div>

            {{-- 📦 Список грузов --}}
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                @foreach($group as $cargo)
                    <div class="border rounded p-3 bg-white">
                        <p class="font-semibold text-gray-700 mb-1">📦 {{ $cargo->cargo_description ?? '—' }}</p>
                        <p><b>Weight:</b> {{ number_format($cargo->cargo_weight ?? 0, 2, '.', ' ') }} kg</p>
                        <p><b>Price:</b> {{ number_format($cargo->price ?? 0, 2, '.', ' ') }} {{ $cargo->currency ?? 'EUR' }}</p>
                    </div>
                    <div class="border rounded p-3 bg-white">
                        <p class="font-semibold text-gray-700 mb-1">📦 {{ $cargo->cargo_description ?? '—' }}</p>
                        <p><b>Netto Weight:</b> {{ number_format($cargo->cargo_netto_weight ?? 0, 2, '.', ' ') }} kg</p>
                        <p><b>Price with tax:</b> {{ number_format($cargo->price_with_tax ?? 0, 2, '.', ' ') }} {{ $cargo->currency ?? 'EUR' }}</p>
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
@push('scripts')
<script>
Livewire.on('cmrGenerated', (data) => {
    if (data.url) window.open(data.url, '_blank');
    const t = document.createElement('div');
    t.textContent = '✅ CMR successfully generated!';
    t.className = 'fixed bottom-4 right-4 bg-green-600 text-white text-sm px-4 py-2 rounded shadow';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
});

Livewire.on('orderGenerated', (data) => {
    if (data.url) window.open(data.url, '_blank');
    const t = document.createElement('div');
    t.textContent = '✅ Order successfully generated!';
    t.className = 'fixed bottom-4 right-4 bg-indigo-600 text-white text-sm px-4 py-2 rounded shadow';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
});

// 🧾 INVOICE (точно как ORDER)
Livewire.on('invoiceGenerated', (data) => {
    if (data.url) window.open(data.url, '_blank');

    const toast = document.createElement('div');
    toast.textContent = '✅ Invoice successfully generated!';
    toast.className = 'fixed bottom-4 right-4 bg-amber-600 text-white text-sm px-4 py-2 rounded shadow';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}); // ← вот эта скобка нужна!
</script>

@endpush