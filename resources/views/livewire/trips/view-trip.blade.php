{{-- resources/views/livewire/trips/view-trip.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-6 space-y-8">

    {{-- ========================= --}}
    {{-- PHP PREP (единый)         --}}
    {{-- ========================= --}}
    @php
        // steps (ordered)
        $steps = $trip->steps()->orderBy('order')->orderBy('id')->get();
        $loadingSteps   = $steps->where('type', 'loading');
        $unloadingSteps = $steps->where('type', 'unloading');

        $startStep = $loadingSteps->first() ?? $steps->first();
        $endStep   = $unloadingSteps->last() ?? $steps->last();

        $startDate = optional($startStep)->date;
        $endDate   = optional($endStep)->date;

        // status labels/colors (enum-friendly)
        if (is_object($trip->status) && method_exists($trip->status, 'label')) {
            $statusLabel = $trip->status->label();
            $statusColor = $trip->status->color();
            $mobileStatusLabel = $statusLabel;
        } else {
            $statusLabel = is_string($trip->status) ? ucfirst($trip->status) : '—';
            $statusColor = 'bg-gray-100 text-gray-800';
            $mobileStatusLabel = $statusLabel;
        }

        // trailer type key by config (id => key)
        $trailerTypeKey = $trip->trailer?->type_id
            ? (config('trailer-types.types')[(int)$trip->trailer->type_id] ?? null)
            : null;

        $isContainerTrailer = ($trailerTypeKey === 'container');

        // container data
        $cont = trim((string)($trip->cont_nr ?? ''));
        $seal = trim((string)($trip->seal_nr ?? ''));

        // show only if trailer is container and at least one filled
        $hasContInfo = $isContainerTrailer && ($cont !== '' || $seal !== '');

        // =========================
        // TOTALS (ALL CARGOS + ALL ITEMS)
        // =========================
        $allCargos = $trip->cargos;
        $allItems  = $allCargos->flatMap->items;

        $totalCargosCount     = $allCargos->count();
        $totalItemLines       = $allItems->count();
        $totalUniqueItemNames = $allItems
            ->pluck('description')
            ->filter(fn($v) => trim((string)$v) !== '')
            ->map(fn($v) => mb_strtolower(trim((string)$v)))
            ->unique()
            ->count();

        // physical totals
        $totalPackages = $allItems->sum(fn($i) => (int)($i->packages ?? 0));
        $totalPallets  = $allItems->sum(fn($i) => (int)($i->pallets ?? 0));
        $totalUnits    = $allItems->sum(fn($i) => (int)($i->units ?? 0));

        $totalNet      = $allItems->sum(fn($i) => (float)($i->net_weight ?? 0));
        $totalGross    = $allItems->sum(fn($i) => (float)($i->gross_weight ?? 0));
        $totalTonnes   = $allItems->sum(fn($i) => (float)($i->tonnes ?? 0));

        $totalVolume   = $allItems->sum(fn($i) => (float)($i->volume ?? 0));
        $totalLm       = $allItems->sum(fn($i) => (float)($i->loading_meters ?? 0));

        // money: freight (cargo-level)
        $totalFreightNoVat   = $allCargos->sum(fn($c) => (float)($c->price ?? 0));
        $totalFreightVat     = $allCargos->sum(fn($c) => (float)($c->total_tax_amount ?? 0));
        $totalFreightWithVat = $allCargos->sum(fn($c) => (float)($c->price_with_tax ?? 0));

        // supplier invoice (cargo-level)
        $totalSupplierInvoice = $allCargos->sum(fn($c) => (float)($c->supplier_invoice_amount ?? 0));

        // goods value (item-level)
        $totalGoodsNoVat   = $allItems->sum(fn($i) => (float)($i->price ?? 0));
        $totalGoodsVat     = $allItems->sum(fn($i) => (float)($i->tax_amount ?? 0));
        $totalGoodsWithVat = $allItems->sum(fn($i) => (float)($i->price_with_tax ?? 0));
    @endphp


    {{-- ========================= --}}
    {{-- 📱 MOBILE PWA TOP BAR     --}}
    {{-- ========================= --}}
    <div class="md:hidden sticky top-0 z-30 -mx-4 -mt-4 mb-4 bg-gray-900 text-white px-4 py-3 shadow-lg">
        <div class="flex items-center justify-between gap-3">

            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('trips.index') }}"
                   class="flex items-center justify-center w-9 h-9 rounded-full bg-white/10 active:bg-white/20">
                    ←
                </a>

                <div class="flex flex-col min-w-0">
                    <span class="text-[10px] uppercase tracking-wide text-gray-300">Trip</span>

                    <span class="text-base font-semibold leading-tight truncate">
                        CMR #{{ $trip->id }}
                    </span>

                    @if($hasContInfo)
                        <span class="text-[11px] text-gray-300 leading-tight truncate">
                            <span class="mr-2">📦 {{ $cont !== '' ? $cont : '—' }}</span>
                            <span>🔒 {{ $seal !== '' ? $seal : '—' }}</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('trips.edit', $trip->id) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-[12px] font-semibold bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white">
                    ✏️
                </a>

                <span class="text-[10px] px-2 py-1 rounded-full bg-white/10">
                    {{ $mobileStatusLabel }}
                </span>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-gray-200">
            <div class="rounded-xl bg-white/10 px-3 py-2">
                <div class="text-gray-300">Freight (with VAT)</div>
                <div class="font-semibold text-white">
                    €{{ number_format($totalFreightWithVat, 2, '.', ' ') }}
                </div>
            </div>

            <div class="rounded-xl bg-white/10 px-3 py-2">
                <div class="text-gray-300">Gross / Volume</div>
                <div class="font-semibold text-white">
                    {{ number_format($totalGross, 0, '.', ' ') }} kg • {{ number_format($totalVolume, 2, '.', ' ') }} m³
                </div>
            </div>
        </div>
    </div>


    {{-- ========================= --}}
    {{-- NOTIFICATIONS             --}}
    {{-- ========================= --}}
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


    {{-- ===================================================================== --}}
    {{-- 🖥 DESKTOP HEADER                                                     --}}
    {{-- ===================================================================== --}}
    <div class="hidden md:block bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-4">

        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-3">
                    🚛 CMR Trip #{{ $trip->id }}
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Start / Stop:
                    <span class="font-medium text-gray-700 dark:text-gray-200">
                        {{ $startDate?->format('d.m.Y') ?? '—' }} →
                        {{ $endDate?->format('d.m.Y') ?? '—' }}
                    </span>
                </p>

                @if($hasContInfo)
                    <p class="text-sm text-gray-500 mt-1">
                        Container / Seal:
                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            📦 {{ $cont !== '' ? $cont : '—' }}
                            <span class="mx-2 text-gray-300 dark:text-gray-700">|</span>
                            🔒 {{ $seal !== '' ? $seal : '—' }}
                        </span>
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.edit', $trip->id) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white">
                    ✏️ Edit
                </a>

                <a href="{{ route('trips.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm bg-gray-100 hover:bg-gray-200 text-gray-700">
                    ⬅ Back to Trips
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mt-4 text-xs sm:text-sm">

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Cargos</div>
                <div class="font-semibold">{{ $totalCargosCount }}</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Items (lines)</div>
                <div class="font-semibold">{{ $totalItemLines }}</div>
                <div class="text-[11px] text-gray-400">
                    unique: {{ $totalUniqueItemNames }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Packages / Pallets</div>
                <div class="font-semibold">
                    {{ number_format($totalPackages, 0, '.', ' ') }} / {{ number_format($totalPallets, 0, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    units: {{ number_format($totalUnits, 0, '.', ' ') }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Net / Gross</div>
                <div class="font-semibold">
                    {{ number_format($totalNet, 0, '.', ' ') }} / {{ number_format($totalGross, 0, '.', ' ') }} kg
                </div>
                <div class="text-[11px] text-gray-400">
                    tonnes: {{ number_format($totalTonnes, 2, '.', ' ') }} t
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Volume / LM</div>
                <div class="font-semibold">
                    {{ number_format($totalVolume, 2, '.', ' ') }} m³
                </div>
                <div class="text-[11px] text-gray-400">
                    LM: {{ number_format($totalLm, 2, '.', ' ') }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Freight (with VAT)</div>
                <div class="font-semibold">
                    €{{ number_format($totalFreightWithVat, 2, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    no VAT: €{{ number_format($totalFreightNoVat, 2, '.', ' ') }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Supplier invoice</div>
                <div class="font-semibold">
                    €{{ number_format($totalSupplierInvoice, 2, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    (sum of cargos)
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800 lg:col-span-2">
                <div class="text-gray-500">Goods value (items, with VAT)</div>
                <div class="font-semibold">
                    €{{ number_format($totalGoodsWithVat, 2, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    no VAT: €{{ number_format($totalGoodsNoVat, 2, '.', ' ') }} • VAT: €{{ number_format($totalGoodsVat, 2, '.', ' ') }}
                </div>
            </div>

        </div>
    </div>


    {{-- ===================================================================== --}}
    {{-- 🚚 TRIP ROUTE EDITOR — АККОРДЕОН                                      --}}
    {{-- ===================================================================== --}}
    <div x-data="{ openRoute: false }"
         class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6">

        <button
            type="button"
            @click="openRoute = !openRoute"
            class="w-full flex items-center justify-between mb-3 px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm font-semibold text-gray-800 dark:text-gray-100">
            🛣️ Маршрут рейса (управление, Drag & Drop)
            <span x-text="openRoute ? '▲' : '▼'" class="text-xs"></span>
        </button>

        <div x-cloak x-show="openRoute" x-collapse class="mt-4">
            <livewire:trips.trip-route-editor :tripId="$trip->id" />
        </div>
    </div>


    {{-- ===================================================================== --}}
    {{-- 📦 CARGO GROUPS                                                      --}}
    {{-- ===================================================================== --}}
    <div class="space-y-6">

        @foreach ($trip->cargos->groupBy('customer_id') as $customerId => $customerCargos)
            @php $customer = $customerCargos->first()->customer; @endphp

            <div x-data="{ openClient: false }"
                 class="bg-white dark:bg-gray-900 shadow rounded-xl border border-gray-200 dark:border-gray-700">

                {{-- CLIENT HEADER --}}
                <button type="button" @click="openClient = !openClient"
                        class="w-full flex items-center justify-between p-4 text-left">
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold truncate">{{ $customer->company_name }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ getCountryById($customer->jur_country_id) }},
                            {{ getCityNameByCountryId($customer->jur_country_id, $customer->jur_city_id) }}
                        </p>
                    </div>
                    <div class="text-gray-400 transition-transform" :class="{ 'rotate-180': openClient }">▼</div>
                </button>

                {{-- CLIENT BODY --}}
                <div x-cloak x-show="openClient" x-collapse class="p-4 space-y-4">

                    @foreach ($customerCargos as $cargo)

                        @php
                            $cargoItems = $cargo->items;

                            $cPackages = $cargoItems->sum(fn($i) => (int)($i->packages ?? 0));
                            $cPallets  = $cargoItems->sum(fn($i) => (int)($i->pallets ?? 0));
                            $cUnits    = $cargoItems->sum(fn($i) => (int)($i->units ?? 0));

                            $cNet      = $cargoItems->sum(fn($i) => (float)($i->net_weight ?? 0));
                            $cGross    = $cargoItems->sum(fn($i) => (float)($i->gross_weight ?? 0));
                            $cVolume   = $cargoItems->sum(fn($i) => (float)($i->volume ?? 0));
                            $cLm       = $cargoItems->sum(fn($i) => (float)($i->loading_meters ?? 0));

                            // freight (cargo-level)
                            $cargoFreightWithVat = (float)($cargo->price_with_tax ?? 0);
                            $cargoFreightNoVat   = (float)($cargo->price ?? 0);

                            // supplier invoice
                            $cargoSupplier = (float)($cargo->supplier_invoice_amount ?? 0);

                            // goods value (items)
                            $cargoGoodsWithVat = $cargoItems->sum(fn($i) => (float)($i->price_with_tax ?? 0));
                        @endphp

                        <div x-data="{ openCargo: false }"
                             class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg shadow border border-gray-300 dark:border-gray-700">

                            {{-- CARGO HEADER --}}
                            <button type="button" @click="openCargo = !openCargo"
                                    class="w-full flex items-center justify-between text-left gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold">📦 Krava #{{ $cargo->id }}</p>

                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $cargo->shipper->company_name ?? '—' }} →
                                        {{ $cargo->consignee->company_name ?? '—' }}
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ number_format($cPackages, 0, '.', ' ') }} pkgs •
                                        {{ number_format($cPallets, 0, '.', ' ') }} pallets •
                                        {{ number_format($cUnits, 0, '.', ' ') }} units •
                                        {{ number_format($cGross, 0, '.', ' ') }} kg •
                                        {{ number_format($cVolume, 2, '.', ' ') }} m³
                                        @if($cLm > 0) • {{ number_format($cLm, 2, '.', ' ') }} LM @endif
                                    </p>

                                    <p class="text-xs text-gray-500 mt-1">
                                        Freight:
                                        <span class="font-semibold">€{{ number_format($cargoFreightWithVat, 2, '.', ' ') }}</span>
                                        <span class="text-[11px] text-gray-400">(no VAT: €{{ number_format($cargoFreightNoVat, 2, '.', ' ') }})</span>

                                        <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>

                                        Goods:
                                        <span class="font-semibold">€{{ number_format($cargoGoodsWithVat, 2, '.', ' ') }}</span>

                                        @if($cargoSupplier > 0)
                                            <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>
                                            Supplier inv:
                                            <span class="font-semibold">€{{ number_format($cargoSupplier, 2, '.', ' ') }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="text-gray-400 transition-transform shrink-0" :class="{ 'rotate-180': openCargo }">▼</div>
                            </button>

                            {{-- CARGO BODY --}}
                            <div x-cloak x-show="openCargo" x-collapse class="mt-3 space-y-4">

                                {{-- ROUTE ACCORDION --}}
                                <div x-data="{ openRoute: false }">
                                    <button type="button" @click="openRoute = !openRoute"
                                            class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold">
                                        🗺 Maršruta punkti
                                        <span class="transition-transform" :class="{ 'rotate-180': openRoute }">▼</span>
                                    </button>

                                    <div x-cloak x-show="openRoute" x-collapse class="mt-3 space-y-3">
                                        @foreach ($steps as $step)
                                            @php
                                                $pivot = $step->cargos->firstWhere('id', $cargo->id)?->pivot;
                                            @endphp

                                            @if ($pivot)
                                                <div class="bg-white dark:bg-gray-900 p-3 rounded-lg border shadow-sm space-y-2">

                                                    <div class="flex items-center gap-2 text-sm">
                                                        @if ($pivot->role === 'loading')
                                                            <span class="px-2 py-0.5 text-xs bg-blue-200 text-blue-800 rounded-full">Iekraušana</span>
                                                        @else
                                                            <span class="px-2 py-0.5 text-xs bg-green-200 text-green-800 rounded-full">Izkraušana</span>
                                                        @endif

                                                        <span>
                                                            {{ getCountryById($step->country_id) }},
                                                            {{ getCityNameByCountryId($step->country_id, $step->city_id) }}
                                                        </span>

                                                        <span class="text-xs text-gray-400">
                                                            {{ $step->date?->format('d.m') }} {{ $step->time }}
                                                        </span>
                                                    </div>

                                                    <div class="ml-6">
                                                        <livewire:trips.trip-step-document-uploader
                                                            :step="$step"
                                                            :key="'step-docs-'.$cargo->id.'-'.$step->id"
                                                        />
                                                    </div>

                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                {{-- ITEMS ACCORDION --}}
                                <div x-data="{ openItems: false }">
                                    <button type="button" @click="openItems = !openItems"
                                            class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold">
                                        📦 Preču vienības
                                        <span class="transition-transform" :class="{ 'rotate-180': openItems }">▼</span>
                                    </button>

                                    <div x-cloak x-show="openItems" x-collapse class="mt-2 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg space-y-2">
                                        @foreach ($cargo->items as $item)
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="font-medium truncate">
                                                        {{ $item->description ?: '—' }}
                                                        @if(!empty($item->customs_code))
                                                            <span class="ml-2 text-[11px] text-gray-400">HS: {{ $item->customs_code }}</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-gray-400">
                                                        {{ (int)($item->packages ?? 0) }} pkgs •
                                                        {{ (int)($item->pallets ?? 0) }} pallets •
                                                        {{ number_format((float)($item->gross_weight ?? 0), 0, '.', ' ') }} kg •
                                                        {{ number_format((float)($item->volume ?? 0), 2, '.', ' ') }} m³
                                                    </p>
                                                </div>

                                                <div class="font-semibold shrink-0">
                                                    €{{ number_format((float)($item->price_with_tax ?? 0), 2, '.', ' ') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- DOCS ACCORDION (manual numbers before generate) --}}
                                <div x-data="{ openDocs: false }" class="space-y-2">
                                    <button
                                        type="button"
                                        @click="openDocs = !openDocs"
                                        class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold"
                                    >
                                        📄 Dokumenti
                                        <span class="transition-transform" :class="{ 'rotate-180': openDocs }">▼</span>
                                    </button>

                                    <div x-cloak x-show="openDocs" x-collapse class="mt-3 space-y-3 text-xs">

                                        {{-- CMR --}}
                                        <div
                                            x-data="{
                                                loading: false,
                                                nr: @entangle('cmrNr.' . $cargo->id).defer,
                                                get isValid() { return String(this.nr ?? '').trim().length > 0; },
                                                run() {
                                                    if (this.loading || !this.isValid) return;
                                                    this.loading = true;

                                                    const id = {{ (int)$cargo->id }};
                                                    const val = String(this.nr ?? '').trim();

                                                    Promise.resolve($wire.set(`cmrNr.${id}`, val))
                                                        .then(() => $wire.generateCmr(id))
                                                        .finally(() => this.loading = false);
                                                }
                                            }"
                                            class="bg-white/70 dark:bg-gray-900/60 rounded-lg border border-gray-200 dark:border-gray-700 p-3"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-gray-800 dark:text-gray-100">📘 CMR</div>
                                                    @if(!empty($cargo->cmr_nr))
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                            Nr: <span class="font-medium">{{ $cargo->cmr_nr }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($cargo->cmr_file)
                                                    <a href="{{ asset('storage/'.$cargo->cmr_file) }}" target="_blank"
                                                       class="shrink-0 px-3 py-2 bg-blue-200 text-blue-900 rounded-lg font-semibold">
                                                        👁 Open
                                                    </a>
                                                @endif
                                            </div>

                                            @if (!$cargo->cmr_file)
                                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-center">
                                                    <div>
                                                        <input type="text"
                                                               x-model.trim="nr"
                                                               placeholder="CMR number (required)"
                                                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm"
                                                               @keydown.enter.prevent="run()" />
                                                        @error('cmrNr.'.$cargo->id)
                                                            <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <button type="button"
                                                            @click="run()"
                                                            :disabled="loading || !isValid"
                                                            class="w-full sm:w-auto px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold flex items-center justify-center disabled:opacity-50 disabled:hover:bg-blue-600">
                                                        <span x-show="!loading">Generate</span>
                                                        <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- ORDER --}}
                                        <div
                                            x-data="{
                                                loading: false,
                                                nr: @entangle('orderNr.' . $cargo->id).defer,
                                                get isValid() { return String(this.nr ?? '').trim().length > 0; },
                                                run() {
                                                    if (this.loading || !this.isValid) return;
                                                    this.loading = true;

                                                    const id = {{ (int)$cargo->id }};
                                                    const val = String(this.nr ?? '').trim();

                                                    Promise.resolve($wire.set(`orderNr.${id}`, val))
                                                        .then(() => $wire.generateOrder(id))
                                                        .finally(() => this.loading = false);
                                                }
                                            }"
                                            class="bg-white/70 dark:bg-gray-900/60 rounded-lg border border-gray-200 dark:border-gray-700 p-3"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-gray-800 dark:text-gray-100">📄 Order</div>
                                                    @if(!empty($cargo->order_nr))
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                            Nr: <span class="font-medium">{{ $cargo->order_nr }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($cargo->order_file)
                                                    <a href="{{ asset('storage/'.$cargo->order_file) }}" target="_blank"
                                                       class="shrink-0 px-3 py-2 bg-indigo-200 text-indigo-900 rounded-lg font-semibold">
                                                        👁 Open
                                                    </a>
                                                @endif
                                            </div>

                                            @if (!$cargo->order_file)
                                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-center">
                                                    <div>
                                                        <input type="text"
                                                               x-model.trim="nr"
                                                               placeholder="Order number (required)"
                                                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm"
                                                               @keydown.enter.prevent="run()" />
                                                        @error('orderNr.'.$cargo->id)
                                                            <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <button type="button"
                                                            @click="run()"
                                                            :disabled="loading || !isValid"
                                                            class="w-full sm:w-auto px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold flex items-center justify-center disabled:opacity-50 disabled:hover:bg-indigo-600">
                                                        <span x-show="!loading">Generate</span>
                                                        <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- INVOICE --}}
                                        <div
                                            x-data="{
                                                loading: false,
                                                nr: @entangle('invNr.' . $cargo->id).defer,
                                                get isValid() { return String(this.nr ?? '').trim().length > 0; },
                                                run() {
                                                    if (this.loading || !this.isValid) return;
                                                    this.loading = true;

                                                    const id = {{ (int)$cargo->id }};
                                                    const val = String(this.nr ?? '').trim();

                                                    Promise.resolve($wire.set(`invNr.${id}`, val))
                                                        .then(() => $wire.generateInvoice(id))
                                                        .finally(() => this.loading = false);
                                                }
                                            }"
                                            class="bg-white/70 dark:bg-gray-900/60 rounded-lg border border-gray-200 dark:border-gray-700 p-3"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-gray-800 dark:text-gray-100">💶 Invoice</div>
                                                    @if(!empty($cargo->inv_nr))
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                            Nr: <span class="font-medium">{{ $cargo->inv_nr }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($cargo->inv_file)
                                                    <a href="{{ asset('storage/'.$cargo->inv_file) }}" target="_blank"
                                                       class="shrink-0 px-3 py-2 bg-amber-200 text-amber-900 rounded-lg font-semibold">
                                                        👁 Open
                                                    </a>
                                                @endif
                                            </div>

                                            @if (!$cargo->inv_file)
                                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-center">
                                                    <div>
                                                        <input type="text"
                                                               x-model.trim="nr"
                                                               placeholder="Invoice number (required)"
                                                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm"
                                                               @keydown.enter.prevent="run()" />
                                                        @error('invNr.'.$cargo->id)
                                                            <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <button type="button"
                                                            @click="run()"
                                                            :disabled="loading || !isValid"
                                                            class="w-full sm:w-auto px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold flex items-center justify-center disabled:opacity-50 disabled:hover:bg-amber-600">
                                                        <span x-show="!loading">Generate</span>
                                                        <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                    @endforeach

                </div>
            </div>
        @endforeach
    </div>


    {{-- ============================ --}}
    {{-- 📄 TRIP-WIDE DOCUMENTS      --}}
    {{-- ============================ --}}
    <div x-data="{ openTripDocs: false }"
         class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-4">

        <button type="button" @click="openTripDocs = !openTripDocs"
                class="w-full flex items-center justify-between text-left">
            <h2 class="text-lg font-semibold">📄 Dokumenti par reisu</h2>
            <div class="text-gray-400 transition-transform" :class="{ 'rotate-180': openTripDocs }">▼</div>
        </button>

        <div x-cloak x-show="openTripDocs" x-collapse class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <livewire:trips.trip-documents-section :trip="$trip" />
        </div>
    </div>


    {{-- ============================ --}}
    {{-- 💶 EXPENSES                 --}}
    {{-- ============================ --}}
    <div x-data="{ openTripExpenses: false }"
         class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-4">

        <button type="button" @click="openTripExpenses = !openTripExpenses"
                class="w-full flex items-center justify-between text-left">
            <h2 class="text-lg font-semibold">💶 Izdevumi par reisu</h2>
            <div class="text-gray-400 transition-transform" :class="{ 'rotate-180': openTripExpenses }">▼</div>
        </button>

        <div x-cloak x-show="openTripExpenses" x-collapse class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <livewire:trips.trip-expenses-section :trip="$trip" />
        </div>
    </div>


    {{-- =============================================================== --}}
    {{-- TOAST NOTIFICATIONS                                             --}}
    {{-- =============================================================== --}}
    @push('scripts')
        <script>
            const toast = (text, color = 'bg-gray-900') => {
                const el = document.createElement('div');
                el.textContent = text;
                el.className =
                    `${color} fixed bottom-20 right-4 text-white text-sm px-4 py-2 rounded-lg shadow-lg z-50`;
                document.body.appendChild(el);

                setTimeout(() => el.classList.add('opacity-0', 'transition', 'duration-500'), 2000);
                setTimeout(() => el.remove(), 2600);
            };

            Livewire.on('cmrGenerated', () => toast('CMR generated!', 'bg-green-600'));
            Livewire.on('orderGenerated', () => toast('Order generated!', 'bg-indigo-600'));
            Livewire.on('invoiceGenerated', () => toast('Invoice generated!', 'bg-amber-600'));
            Livewire.on('stepDocumentDeleted', () => toast('Document deleted', 'bg-red-600'));
            Livewire.on('stepDocumentUploaded', () => toast('Document uploaded', 'bg-green-600'));
            Livewire.on('tripDocumentUploaded', () => toast('Trip document uploaded', 'bg-green-600'));
            Livewire.on('tripExpenseAdded', () => toast('Expense saved', 'bg-green-600'));
            Livewire.on('tripExpenseDeleted', () => toast('Expense deleted', 'bg-red-600'));
        </script>
    @endpush

</div>
