{{-- resources/views/livewire/trips/view-trip.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-6 space-y-8" wire:ignore.self>

    {{-- ========================= --}}
    {{-- 📱 MOBILE PWA TOP BAR    --}}
    {{-- ========================= --}}
    <div class="md:hidden sticky top-0 z-30 -mx-4 -mt-4 mb-4 bg-gray-900 text-white px-4 py-3 shadow-lg flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('trips.index') }}"
               class="flex items-center justify-center w-8 h-8 rounded-full bg-white/10 active:bg-white/20">
                ←
            </a>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-gray-300">Trip</span>
                <span class="text-base font-semibold">CMR #{{ $trip->id }}</span>
            </div>
        </div>

        @php
            if (is_object($trip->status) && method_exists($trip->status, 'label')) {
                $mobileStatusLabel = $trip->status->label();
            } else {
                $mobileStatusLabel = is_string($trip->status) ? ucfirst($trip->status) : '—';
            }
        @endphp

        <span class="text-[10px] px-2 py-1 rounded-full bg-white/10">
            {{ $mobileStatusLabel }}
        </span>
    </div>

    {{-- NOTIFICATIONS --}}
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

    @php
        $steps = $trip->steps()->orderBy('order')->orderBy('id')->get();
        $loadingSteps   = $steps->where('type', 'loading');
        $unloadingSteps = $steps->where('type', 'unloading');

        $startStep = $loadingSteps->first() ?? $steps->first();
        $endStep   = $unloadingSteps->last() ?? $steps->last();

        $startDate = optional($startStep)->date;
        $endDate   = optional($endStep)->date;

        $routeLine = $steps
            ->map(fn($s) => $s->country_id ? (config('countries.' . $s->country_id . '.iso') ?? null) : null)
            ->filter()
            ->unique()
            ->implode(' → ');

        $allItems    = $trip->cargos->flatMap->items;
        $totalGross  = $allItems->sum(fn($i) => (float)($i->gross_weight ?? 0));
        $totalNet    = $allItems->sum(fn($i) => (float)($i->net_weight ?? 0));
        $totalVolume = $allItems->sum(fn($i) => (float)($i->volume ?? 0));
        $totalPrice  = $trip->cargos->sum('price_with_tax');

        if (is_object($trip->status) && method_exists($trip->status, 'label')) {
            $statusLabel = $trip->status->label();
            $statusColor = $trip->status->color();
        } else {
            $statusLabel = is_string($trip->status) ? ucfirst($trip->status) : '—';
            $statusColor = 'bg-gray-100 text-gray-800';
        }
    @endphp


    {{-- ===================================================================== --}}
    {{-- 🖥 HEADER (DESKTOP)                                                 --}}
    {{-- ===================================================================== --}}
    <div class="hidden md:block bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-4">

        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-3">
                    🚛 CMR Trip #{{ $trip->id }}
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Start / Stop:
                    <span class="font-medium text-gray-700">
                        {{ $startDate?->format('d.m.Y') ?? '—' }} →
                        {{ $endDate?->format('d.m.Y') ?? '—' }}
                    </span>
                </p>
            </div>

            <a href="{{ route('trips.index') }}"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm bg-gray-100 hover:bg-gray-200 text-gray-700">
                ⬅ Back to Trips
            </a>
        </div>

        @if($routeLine)
            <div class="mt-3 text-sm">
                <span class="font-semibold">Route:</span>
                <span>{!! $routeLine !!}</span>
            </div>
        @endif

        {{-- QUICK SUMMARY --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 text-xs sm:text-sm">
            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Total cargos</div>
                <div class="font-semibold">{{ $trip->cargos->count() }}</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Gross weight</div>
                <div class="font-semibold">{{ number_format($totalGross, 0, '.', ' ') }} kg</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Volume</div>
                <div class="font-semibold">{{ number_format($totalVolume, 1, '.', ' ') }} m³</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Total price (w/VAT)</div>
                <div class="font-semibold">€{{ number_format($totalPrice, 2, '.', ' ') }}</div>
            </div>
        </div>
    </div>




    {{-- ===================================================================== --}}
    {{-- 🧭 TRUE ROUTE EDITOR (ONLY THIS ONE!)                               --}}
    {{-- ===================================================================== --}}
    <livewire:trips.trip-route-editor :trip="$trip" />




    {{-- ===================================================================== --}}
    {{-- EXPEDITOR + TRANSPORT                                               --}}
    {{-- ===================================================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- EXPEDITOR --}}
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6">
            <h3 class="text-lg font-semibold mb-2">1️⃣ Expeditor Company</h3>

            <div class="text-sm space-y-1">
                <p><b>Name:</b> {{ $trip->expeditor_name }}</p>
                <p><b>Reg.nr:</b> {{ $trip->expeditor_reg_nr }}</p>
                <p><b>Address:</b> {{ $trip->expeditor_address }}, {{ $trip->expeditor_city }} {{ $trip->expeditor_post_code }}</p>
                <p><b>Country:</b> {{ $trip->expeditor_country }}</p>
                <p><b>Email:</b> {{ $trip->expeditor_email }}</p>
                <p><b>Phone:</b> {{ $trip->expeditor_phone }}</p>
            </div>

            <div class="mt-3 pt-3 border-t text-sm space-y-1">
                <p><b>Bank:</b> {{ $trip->expeditor_bank }}</p>
                <p><b>IBAN:</b> {{ $trip->expeditor_iban }}</p>
                <p><b>BIC:</b> {{ $trip->expeditor_bic }}</p>
            </div>
        </div>

        {{-- TRANSPORT --}}
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-3">
            <h3 class="text-lg font-semibold mb-2">2️⃣ Transport Details</h3>

            <div class="space-y-3 text-sm">

                <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                    <p class="font-semibold mb-1">👨‍✈️ Driver</p>
                    <p>{{ $trip->driver?->first_name }} {{ $trip->driver?->last_name }}</p>
                    <p class="text-xs text-gray-500">Phone: {{ $trip->driver?->phone }}</p>
                </div>

                <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                    <p class="font-semibold mb-1">🚚 Truck</p>
                    <p>{{ $trip->truck?->plate }}</p>
                    <p class="text-xs text-gray-500">{{ $trip->truck?->brand }} {{ $trip->truck?->model }}</p>
                </div>

                <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                    <p class="font-semibold mb-1">🚛 Trailer</p>
                    <p>{{ $trip->trailer?->plate }}</p>
                    <p class="text-xs text-gray-500">{{ $trip->trailer?->brand }}</p>
                </div>

            </div>
        </div>
    </div>


    {{-- ===================================================================== --}}
    {{-- CARGO GROUPS (unchanged) --}}
    {{-- ===================================================================== --}}

    {{-- ... ТВОЙ КОД ДАЛЬШЕ БЕЗ ИЗМЕНЕНИЙ ... --}}


    {{-- DOCUMENTS --}}
    <livewire:trips.trip-documents-section :trip="$trip" />

    {{-- EXPENSES --}}
    <livewire:trips.trip-expenses-section :trip="$trip" />

    {{-- BACK --}}
    <div class="pt-4 hidden md:block">
        <a href="{{ route('trips.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm">
            ⬅ Back to Trips
        </a>
    </div>

</div>

@push('scripts')
<script>
    const toast = (t, c='bg-gray-800') => {
        const el = document.createElement('div');
        el.textContent = t;
        el.className = `${c} fixed bottom-20 right-4 text-white text-sm px-4 py-2 rounded shadow z-50`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    };
    Livewire.on('cmrGenerated', () => toast('CMR generated!','bg-green-600'));
    Livewire.on('orderGenerated', () => toast('Order generated!','bg-indigo-600'));
    Livewire.on('invoiceGenerated', () => toast('Invoice generated!','bg-amber-600'));
</script>
@endpush
