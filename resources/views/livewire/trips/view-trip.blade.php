{{-- resources/views/livewire/trips/view-trip.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-6 space-y-10" wire:ignore.self>

    {{-- ✅ Notifications --}}
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

    {{-- 🧭 Основной блок рейса --}}
    <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 space-y-8 transition-colors">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-4">
            🚛 CMR Trip #{{ $trip->id }}
        </h2>

        {{-- 1️⃣ Expeditor --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">1️⃣ Expeditor Company</h3>
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
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">2️⃣ Transport Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <p><b>Driver:</b> {{ $trip->driver?->first_name }} {{ $trip->driver?->last_name }}</p>
                <p><b>Truck:</b> {{ $trip->truck?->plate }} — {{ $trip->truck?->brand }} {{ $trip->truck?->model }}</p>
                <p><b>Trailer:</b> {{ $trip->trailer?->brand ?? '—' }} {{ $trip->trailer?->plate ?? '—' }}</p>
            </div>
        </section>

        {{-- 3️⃣ Cargo --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">3️⃣ Cargo Details</h3>

            @php
                $grouped = $trip->cargos->groupBy(fn($c) => $c->shipper_id . '-' . $c->consignee_id);
            @endphp

            @forelse($grouped as $pair => $group)
                @php
                    $first = $group->first();
                    $customer  = $first->customer?->company_name ?? '—';
                    $shipper   = $first->shipper?->company_name ?? '—';
                    $consignee = $first->consignee?->company_name ?? '—';
                    $payer = match ($first->payer_type_id) {
                        1 => $customer,
                        2 => $shipper,
                        3 => $consignee,
                        default => '—',
                    };

                    // 💡 Универсальная функция подсчёта
                    $sumItemOrCargo = function($group, $itemField, $cargoField) {
                        return $group->reduce(function($carry, $cargo) use ($itemField, $cargoField) {
                            $items = $cargo->items ?? collect();
                            if ($items->isNotEmpty()) {
                                $itemSum = $items->sum(fn($it) => (float) ($it->{$itemField} ?? 0));
                                if ($itemSum > 0) return $carry + $itemSum;
                            }
                            return $carry + (float) ($cargo->{$cargoField} ?? 0);
                        }, 0.0);
                    };

                    $totalBrutto   = $sumItemOrCargo($group, 'weight',             'cargo_weight');
                    $totalNetto    = $sumItemOrCargo($group, 'cargo_netto_weight', 'cargo_netto_weight');
                    $totalPrice    = $sumItemOrCargo($group, 'price',              'price');
                    $totalWithTax  = $sumItemOrCargo($group, 'price_with_tax',     'price_with_tax');
                    $currency      = $group->first()->currency ?? 'EUR';
                @endphp

                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5 mb-6 bg-gray-50 dark:bg-gray-800 shadow-sm">

                    {{-- 🔹 Parties --}}
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm mb-3">
                        <div class="bg-white dark:bg-gray-900 border border-blue-100 rounded p-2">
                            <p class="font-semibold text-blue-700 dark:text-blue-400">🧾 Customer</p>
                            <p>{{ $customer }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 border border-orange-100 rounded p-2">
                            <p class="font-semibold text-orange-700 dark:text-orange-400">📦 Shipper</p>
                            <p>{{ $shipper }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 border border-green-100 rounded p-2">
                            <p class="font-semibold text-green-700 dark:text-green-400">🏠 Consignee</p>
                            <p>{{ $consignee }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 border border-purple-100 rounded p-2">
                            <p class="font-semibold text-purple-700 dark:text-purple-400">💰 Payer</p>
                            <p>{{ $payer }}</p>
                        </div>
                    </div>

                    {{-- 🔹 Action buttons --}}
                    <div class="flex flex-wrap justify-end gap-3 mt-3">
                        {{-- === CMR === --}}
                        @if ($first->cmr_file)
                            <a href="{{ asset('storage/' . $first->cmr_file) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded">
                                👁 View CMR
                            </a>
                        @else
                            <button wire:click="generateCmr({{ $first->id }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded">
                                <span wire:loading.remove wire:target="generateCmr({{ $first->id }})">📄 Generate CMR</span>
                                <span wire:loading wire:target="generateCmr({{ $first->id }})" class="animate-pulse">⏳...</span>
                            </button>
                        @endif

                        {{-- === ORDER === --}}
                        @if (!empty($first->order_file) && Storage::disk('public')->exists($first->order_file))
                            <a href="{{ asset('storage/' . $first->order_file) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                                📑 View Order
                            </a>
                        @else
                            <button wire:click="generateOrder({{ $first->id }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-indigo-500 hover:bg-indigo-600 text-white rounded">
                                📝 Generate Order
                            </button>
                        @endif

                        {{-- === INVOICE === --}}
                        @if (!empty($first->inv_file) && Storage::disk('public')->exists($first->inv_file))
                            <a href="{{ asset('storage/' . $first->inv_file) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-amber-600 hover:bg-amber-700 text-white rounded">
                                🧾 View Invoice
                            </a>
                        @else
                            <button wire:click="generateInvoice({{ $first->id }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white rounded">
                                🧾 Generate Invoice
                            </button>
                        @endif
                    </div>

                    {{-- 📦 Cargo Table --}}
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full text-sm border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                                <tr>
                                    <th class="p-2 border">#</th>
                                    <th class="p-2 border text-left">Description</th>
                                    <th class="p-2 border text-right">Brutto (kg)</th>
                                    <th class="p-2 border text-right">Netto (kg)</th>
                                    <th class="p-2 border text-right">Price</th>
                                    <th class="p-2 border text-right">Price w/Tax</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group as $i => $cargo)
                                    @php $items = $cargo->items ?? collect(); @endphp
                                    @forelse($items as $item)
                                        <tr class="bg-white dark:bg-gray-900">
                                            <td class="p-2 border">{{ $i + 1 }}</td>
                                            <td class="p-2 border">
                                                {{ $item->description ?? '—' }}
                                                @if($item->packages)
                                                    <span class="text-gray-500">({{ $item->packages }})</span>
                                                @endif
                                            </td>
                                            <td class="p-2 border text-right">{{ number_format($item->weight ?? $cargo->cargo_weight ?? 0, 2, '.', ' ') }}</td>
                                            <td class="p-2 border text-right">{{ number_format($item->cargo_netto_weight ?? $cargo->cargo_netto_weight ?? 0, 2, '.', ' ') }}</td>
                                            <td class="p-2 border text-right">{{ number_format($item->price ?? $cargo->price ?? 0, 2, '.', ' ') }} {{ $cargo->currency ?? 'EUR' }}</td>
                                            <td class="p-2 border text-right">{{ number_format($item->price_with_tax ?? $cargo->price_with_tax ?? 0, 2, '.', ' ') }} {{ $cargo->currency ?? 'EUR' }}</td>
                                        </tr>
                                    @empty
                                        <tr class="bg-white dark:bg-gray-900 italic text-gray-500">
                                            <td class="p-2 border text-center" colspan="6">No items for this cargo</td>
                                        </tr>
                                    @endforelse
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-800 font-semibold">
                                <tr>
                                    <td colspan="2" class="p-2 border text-right">Total:</td>
                                    <td class="p-2 border text-right">{{ number_format($totalBrutto, 2, '.', ' ') }}</td>
                                    <td class="p-2 border text-right">{{ number_format($totalNetto, 2, '.', ' ') }}</td>
                                    <td class="p-2 border text-right">{{ number_format($totalPrice, 2, '.', ' ') }} {{ $currency }}</td>
                                    <td class="p-2 border text-right">{{ number_format($totalWithTax, 2, '.', ' ') }} {{ $currency }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 italic">No cargos found.</p>
            @endforelse
        </section>
    </div>

    {{-- 📁 Документы рейса --}}
    <livewire:trips.trip-documents-section :trip="$trip" />

    {{-- 💶 Расходы рейса --}}
    <livewire:trips.trip-expenses-section :trip="$trip" />

    {{-- 🔙 Назад --}}
    <div class="pt-6">
        <a href="{{ route('trips.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-800 dark:bg-gray-800 dark:text-gray-100">
            ⬅ Back to Trips
        </a>
    </div>
</div>

{{-- ✅ Toast notifications --}}
@push('scripts')
<script>
const toast = (text, color='bg-gray-800') => {
    const t = document.createElement('div');
    t.textContent = text;
    t.className = `${color} fixed bottom-20 right-4 text-white text-sm px-4 py-2 rounded shadow z-50 transition`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
};

Livewire.on('cmrGenerated', data => {
    if (data.url) window.open(data.url, '_blank');
    toast('✅ CMR successfully generated!', 'bg-green-600');
});
Livewire.on('orderGenerated', data => {
    if (data.url) window.open(data.url, '_blank');
    toast('✅ Order successfully generated!', 'bg-indigo-600');
});
Livewire.on('invoiceGenerated', data => {
    if (data.url) window.open(data.url, '_blank');
    toast('✅ Invoice successfully generated!', 'bg-amber-600');
});
</script>
@endpush
