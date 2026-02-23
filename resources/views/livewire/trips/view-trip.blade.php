{{-- resources/views/livewire/trips/view-trip.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-6 space-y-8">

    {{-- ========================= --}}
    {{-- PHP PREP (единый)         --}}
    {{-- ========================= --}}
    @php
        // steps
        $steps = $trip->steps()->orderBy('order')->orderBy('id')->get();
        $loadingSteps   = $steps->where('type', 'loading');
        $unloadingSteps = $steps->where('type', 'unloading');

        $startStep = $loadingSteps->first() ?? $steps->first();
        $endStep   = $unloadingSteps->last() ?? $steps->last();

        $startDate = optional($startStep)->date;
        $endDate   = optional($endStep)->date;

        // totals
        $allItems    = $trip->cargos->flatMap->items;
        $totalGross  = $allItems->sum(fn($i) => (float)($i->gross_weight ?? 0));
        $totalVolume = $allItems->sum(fn($i) => (float)($i->volume ?? 0));
        $totalPrice  = $trip->cargos->sum('price_with_tax');

        // status labels/colors
        if (is_object($trip->status) && method_exists($trip->status, 'label')) {
            $statusLabel = $trip->status->label();
            $statusColor = $trip->status->color();

            $mobileStatusLabel = $statusLabel;
        } else {
            $statusLabel = is_string($trip->status) ? ucfirst($trip->status) : '—';
            $statusColor = 'bg-gray-100 text-gray-800';

            $mobileStatusLabel = $statusLabel;
        }

        // trailer type key by your config (id => key)
        $trailerTypeKey = $trip->trailer?->type_id
            ? (config('trailer-types.types')[(int)$trip->trailer->type_id] ?? null)
            : null;

        $isContainerTrailer = ($trailerTypeKey === 'container');

        // container data
        $cont = trim((string)($trip->cont_nr ?? ''));
        $seal = trim((string)($trip->seal_nr ?? ''));

        // показываем, если прицеп контейнерный и хотя бы одно поле заполнено
        $hasContInfo = $isContainerTrailer && ($cont !== '' || $seal !== '');
    @endphp


    {{-- ========================= --}}
    {{-- 📱 MOBILE PWA TOP BAR     --}}
    {{-- ========================= --}}
    <div
        class="md:hidden sticky top-0 z-30 -mx-4 -mt-4 mb-4 bg-gray-900 text-white px-4 py-3 shadow-lg flex items-center justify-between">

        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('trips.index') }}"
               class="flex items-center justify-center w-8 h-8 rounded-full bg-white/10 active:bg-white/20">
                ←
            </a>

            <div class="flex flex-col min-w-0">
                <span class="text-xs uppercase tracking-wide text-gray-300">Trip</span>

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

        <span class="text-[10px] px-2 py-1 rounded-full bg-white/10 shrink-0">
            {{ $mobileStatusLabel }}
        </span>
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

                {{-- ✅ CONTAINER / SEAL on desktop too --}}
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

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 text-xs sm:text-sm">
            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Total cargos</div>
                <div class="font-semibold">{{ $trip->cargos->count() }}</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Gross weight</div>
                <div class="font-semibold">{{ number_format($totalGross, 0) }} kg</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Volume</div>
                <div class="font-semibold">{{ number_format($totalVolume, 1) }} m³</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">Total price</div>
                <div class="font-semibold">€{{ number_format($totalPrice, 2) }}</div>
            </div>
        </div>
    </div>


    {{-- ===================================================================== --}}
    {{-- 🚚 TRIP ROUTE EDITOR — АККОРДЕОН                                      --}}
    {{-- ===================================================================== --}}
    <div x-data="{ openRoute: false }"
         class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6">

        <button
            @click="openRoute = !openRoute"
            class="w-full flex items-center justify-between mb-3 px-3 py-2 bg-gray-100 rounded-lg text-sm font-semibold">
            🛣️ Маршрут рейса (управление, Drag & Drop)
            <span x-text="openRoute ? '▲' : '▼'" class="text-xs"></span>
        </button>

        <div x-show="openRoute" x-collapse class="mt-4">
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
                <button @click="openClient = !openClient"
                        class="w-full flex items-center justify-between p-4 text-left">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $customer->company_name }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ getCountryById($customer->jur_country_id) }},
                            {{ getCityNameByCountryId($customer->jur_country_id, $customer->jur_city_id) }}
                        </p>
                    </div>
                    <div class="text-gray-400 transition-transform" :class="{ 'rotate-180': openClient }">▼</div>
                </button>

                {{-- CLIENT BODY --}}
                <div x-show="openClient" x-collapse class="p-4 space-y-4">

                    @foreach ($customerCargos as $cargo)

                        <div x-data="{ openCargo: false }"
                             class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg shadow border border-gray-300 dark:border-gray-700">

                            {{-- CARGO HEADER --}}
                            <button @click="openCargo = !openCargo"
                                    class="w-full flex items-center justify-between text-left">
                                <div>
                                    <p class="font-semibold">📦 Krava #{{ $cargo->id }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $cargo->shipper->company_name }} →
                                        {{ $cargo->consignee->company_name }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $cargo->items->sum('packages') }} pkgs •
                                        {{ number_format($cargo->items->sum('gross_weight'), 0) }} kg
                                    </p>
                                </div>
                                <div class="text-gray-400 transition-transform" :class="{ 'rotate-180': openCargo }">▼</div>
                            </button>

                            {{-- CARGO BODY --}}
                            <div x-show="openCargo" x-collapse class="mt-3 space-y-4">

                                {{-- ROUTE ACCORDION --}}
                                <div x-data="{ openRoute: false }">
                                    <button @click="openRoute = !openRoute"
                                            class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold">
                                        🗺 Maršruta punkti
                                        <span :class="{ 'rotate-180': openRoute }">▼</span>
                                    </button>

                                    <div x-show="openRoute" x-collapse class="mt-3 space-y-3">
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
                                    <button @click="openItems = !openItems"
                                            class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold">
                                        📦 Preču vienības
                                        <span :class="{ 'rotate-180': openItems }">▼</span>
                                    </button>

                                    <div x-show="openItems" x-collapse class="mt-2 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg space-y-2">
                                        @foreach ($cargo->items as $item)
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium">{{ $item->description }}</p>
                                                    <p class="text-xs text-gray-400">
                                                        {{ $item->packages }} pkgs •
                                                        {{ $item->pallets }} pallets •
                                                        {{ number_format($item->gross_weight, 0) }} kg
                                                    </p>
                                                </div>
                                                <div class="font-semibold">
                                                    €{{ number_format($item->price_with_tax, 2) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- DOCS ACCORDION --}}
                                <div x-data="{ openDocs: false }">
                                    <button @click="openDocs = !openDocs"
                                            class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold">
                                        📄 Dokumenti
                                        <span :class="{ 'rotate-180': openDocs }">▼</span>
                                    </button>

                                    <div x-show="openDocs" x-collapse class="mt-3 grid grid-cols-3 gap-2 text-xs">

                                        {{-- CMR --}}
                                        <div x-data="{ loading:false }">
                                            @if ($cargo->cmr_file)
                                                <a href="{{ asset('storage/'.$cargo->cmr_file) }}"
                                                   target="_blank"
                                                   class="block px-3 py-2 bg-blue-200 text-blue-800 rounded-lg text-center font-semibold">
                                                    👁 CMR
                                                </a>
                                            @else
                                                <button @click="loading=true;$wire.generateCmr({{ $cargo->id }});"
                                                        :disabled="loading"
                                                        class="w-full px-3 py-2 bg-blue-100 text-blue-700 rounded-lg font-semibold flex items-center justify-center disabled:opacity-60">
                                                    <span x-show="!loading">📘 Generate CMR</span>
                                                    <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-blue-700 border-t-transparent rounded-full"></span>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- ORDER --}}
                                        <div x-data="{ loading:false }">
                                            @if ($cargo->order_file)
                                                <a href="{{ asset('storage/'.$cargo->order_file) }}"
                                                   target="_blank"
                                                   class="block px-3 py-2 bg-indigo-200 text-indigo-800 rounded-lg text-center font-semibold">
                                                    👁 Order
                                                </a>
                                            @else
                                                <button @click="loading=true;$wire.generateOrder({{ $cargo->id }});"
                                                        :disabled="loading"
                                                        class="w-full px-3 py-2 bg-indigo-100 text-indigo-700 rounded-lg font-semibold flex items-center justify-center disabled:opacity-60">
                                                    <span x-show="!loading">📄 Generate Order</span>
                                                    <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-indigo-700 border-t-transparent rounded-full"></span>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- INVOICE --}}
                                        <div x-data="{ loading:false }">
                                            @if ($cargo->inv_file)
                                                <a href="{{ asset('storage/'.$cargo->inv_file) }}"
                                                   target="_blank"
                                                   class="block px-3 py-2 bg-amber-200 text-amber-800 rounded-lg text-center font-semibold">
                                                    👁 Invoice
                                                </a>
                                            @else
                                                <button @click="loading=true;$wire.generateInvoice({{ $cargo->id }});"
                                                        :disabled="loading"
                                                        class="w-full px-3 py-2 bg-amber-100 text-amber-700 rounded-lg font-semibold flex items-center justify-center disabled:opacity-60">
                                                    <span x-show="!loading">💶 Generate Invoice</span>
                                                    <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-amber-700 border-t-transparent rounded-full"></span>
                                                </button>
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

        <button @click="openTripDocs = !openTripDocs"
                class="w-full flex items-center justify-between text-left">
            <h2 class="text-lg font-semibold">📄 Dokumenti par reisu</h2>
            <div class="text-gray-400 transition-transform" :class="{ 'rotate-180': openTripDocs }">▼</div>
        </button>

        <div x-show="openTripDocs" x-collapse class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <livewire:trips.trip-documents-section :trip="$trip" />
        </div>
    </div>


    {{-- ============================ --}}
    {{-- 💶 EXPENSES                 --}}
    {{-- ============================ --}}
    <div x-data="{ openTripExpenses: false }"
         class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-4">

        <button @click="openTripExpenses = !openTripExpenses"
                class="w-full flex items-center justify-between text-left">
            <h2 class="text-lg font-semibold">💶 Izdevumi par reisu</h2>
            <div class="text-gray-400 transition-transform" :class="{ 'rotate-180': openTripExpenses }">▼</div>
        </button>

        <div x-show="openTripExpenses" x-collapse class="pt-4 border-t border-gray-200 dark:border-gray-700">
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
