{{-- resources/views/livewire/trips/view-trip.blade.php --}}
<div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-8">

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

        // status labels/colors (enum-friendly, translated)
        if (is_object($trip->status) && method_exists($trip->status, 'value')) {
            $statusLabel = __('app.trip.status.' . $trip->status->value);
            $statusColor = $trip->status->color();
            $mobileStatusLabel = $statusLabel;
        } else {
            $statusLabel = is_string($trip->status) ? __('app.trip.status.' . $trip->status) : '—';
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

        // companies / transport
        $carrier    = $trip->carrierCompany;
        $expeditor  = $trip->expeditorCompany;
        $isThirdPartyCarrier = (bool) ($carrier?->is_third_party ?? false);

        $driver  = $trip->driver;
        $truck   = $trip->truck;
        $trailer = $trip->trailer;

        $driverName = $driver
            ? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''))
            : ($isThirdPartyCarrier ? __('app.trip.show.third_party_driver') : '—');

        // For third-party we often store brand/model как "Unknown" — в UI это шум.
        $truckBrand = trim((string)($truck->brand ?? ''));
        $truckModel = trim((string)($truck->model ?? ''));
        $truckPlate = trim((string)($truck->plate ?? ''));

        if ($isThirdPartyCarrier) {
            if (mb_strtolower($truckBrand) === 'unknown') {
                $truckBrand = '';
            }
            if (mb_strtolower($truckModel) === 'unknown') {
                $truckModel = '';
            }
        }

        $truckLabel = $truck
            ? trim(implode(' ', array_filter([$truckBrand, $truckModel, $truckPlate])))
            : ($isThirdPartyCarrier ? __('app.trip.show.third_party_truck') : '—');

        $trailerBrand = trim((string)($trailer->brand ?? ''));
        $trailerPlate = trim((string)($trailer->plate ?? ''));

        if ($isThirdPartyCarrier && mb_strtolower($trailerBrand) === 'unknown') {
            $trailerBrand = '';
        }

        $trailerLabel = $trailer
            ? trim(implode(' ', array_filter([$trailerBrand, $trailerPlate])))
            : ($isThirdPartyCarrier ? __('app.trip.show.third_party_trailer') : '—');

        // scheme (own / third_party / resell) — translated
        $schemeLabel = $trip->scheme_key ? __('app.trip.scheme.' . $trip->scheme_key) : null;
        $schemeBadge = $trip->scheme_badge_class ?? 'bg-gray-100 text-gray-800';

        // third-party fixed payment (from TripExpense created on trip create)
        $thirdPartyFee = null;
        if ($isThirdPartyCarrier) {
            $thirdPartyFee = $trip->expenses()
                ->where('supplier_company_id', $carrier->id)
                ->sum('amount');
            $thirdPartyFee = $thirdPartyFee > 0 ? $thirdPartyFee : null;
        }

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
                    <span class="text-[10px] uppercase tracking-wide text-gray-300">{{ __('app.trip.show.trip_label') }}</span>

                    <span class="text-base font-semibold leading-tight truncate">
                        {{ $isThirdPartyCarrier ? __('app.trip.show.order_trip') : __('app.trip.show.cmr_trip') }} #{{ $trip->id }}
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
                @if($trip->status !== \App\Enums\TripStatus::COMPLETED)
                <a href="{{ route('trips.edit', $trip->id) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-[12px] font-semibold bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white">
                    ✏️
                </a>
                @endif

                <span class="text-[10px] px-2 py-1 rounded-full bg-white/10">
                    {{ $mobileStatusLabel }}
                </span>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-gray-200">
            <div class="rounded-xl bg-white/10 px-3 py-2">
                <div class="text-gray-300">{{ __('app.trip.show.freight_with_vat') }}</div>
                <div class="font-semibold text-white">
                    €{{ number_format($totalFreightWithVat, 2, '.', ' ') }}
                </div>
            </div>

            <div class="rounded-xl bg-white/10 px-3 py-2">
                <div class="text-gray-300">{{ __('app.trip.show.gross_volume') }}</div>
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

    {{-- Заказы в рейсе + добавить заказы --}}
    <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-3 border border-gray-100 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ __('app.trip.show.orders_in_trip') }}</h2>
        @if($trip->transportOrders && $trip->transportOrders->isNotEmpty())
            <ul class="space-y-4">
                @foreach($trip->transportOrders as $order)
                    @php
                        $loadingSteps = $order->steps->where('type', 'loading')->sortBy('order')->values();
                        $unloadingSteps = $order->steps->where('type', 'unloading')->sortBy('order')->values();
                        $cargos = $order->cargos;
                    @endphp
                    <li class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30 overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-white/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                            <a href="{{ route('orders.show', $order) }}" wire:navigate
                               class="inline-flex items-center gap-2 text-blue-600 hover:underline font-medium">
                                📋 {{ $order->number }}
                                — {{ $order->expeditor?->name ?? '—' }}
                                @if($order->customer) / {{ $order->customer->company_name }} @endif
                            </a>
                            @if($trip->status !== \App\Enums\TripStatus::COMPLETED)
                                <a href="#"
                                   wire:click.prevent="removeOrderFromTrip({{ $order->id }})"
                                   data-no-loading-overlay
                                   class="shrink-0 inline-block px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-200 dark:hover:bg-red-900/60">
                                    {{ __('app.trip.show.remove_order') }}
                                </a>
                            @endif
                        </div>
                        <div class="p-3 sm:p-4 space-y-2">
                            @if($cargos->isEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400 py-1">{{ __('app.trip.show.order_no_cargos') }}</p>
                            @elseif($cargos->count() > 1)
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = !open"
                                            class="flex items-center gap-2 w-full text-left text-sm font-medium text-amber-700 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 py-1">
                                        <span x-show="!open" aria-hidden="true">▶</span>
                                        <span x-show="open" x-cloak aria-hidden="true">▼</span>
                                        {{ __('app.trip.show.cargos_count', ['count' => $cargos->count()]) }}
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak class="space-y-2 mt-2">
                                        @foreach($cargos as $idx => $cargo)
                                            @php
                                                $loadStep = $loadingSteps->get($idx) ?? $loadingSteps->first();
                                                $unloadStep = $unloadingSteps->get($idx) ?? $unloadingSteps->first();
                                                $senderName = $cargo->shipper?->company_name ?? $order->customer?->company_name ?? '—';
                                                $receiverName = $cargo->consignee?->company_name ?? '—';
                                                $loadingDate = $loadStep?->date ? $loadStep->date->format('d.m.Y') . ($loadStep->time ? ' ' . $loadStep->time : '') : '—';
                                                $unloadingDate = $unloadStep?->date ? $unloadStep->date->format('d.m.Y') . ($unloadStep->time ? ' ' . $unloadStep->time : '') : '—';
                                                $loadingAddr = $loadStep?->address ?? '';
                                                $unloadingAddr = $unloadStep?->address ?? '';
                                            @endphp
                                            <div class="text-sm text-gray-700 dark:text-gray-300 py-2 px-3 rounded-lg bg-white dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 space-y-0.5">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $senderName }}</div>
                                                <div class="text-gray-500">—</div>
                                                <div>{{ __('app.trip.show.order_loading') }}: {{ $loadingDate }}{{ $loadingAddr ? ' — ' . $loadingAddr : '' }}</div>
                                                <div class="text-gray-400">|</div>
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $receiverName }}</div>
                                                <div class="text-gray-500">—</div>
                                                <div>{{ __('app.trip.show.order_unloading') }}: {{ $unloadingDate }}{{ $unloadingAddr ? ' — ' . $unloadingAddr : '' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @php
                                    $cargo = $cargos->first();
                                    $loadStep = $loadingSteps->first();
                                    $unloadStep = $unloadingSteps->first();
                                    $senderName = $cargo->shipper?->company_name ?? $order->customer?->company_name ?? '—';
                                    $receiverName = $cargo->consignee?->company_name ?? '—';
                                    $loadingDate = $loadStep?->date ? $loadStep->date->format('d.m.Y') . ($loadStep->time ? ' ' . $loadStep->time : '') : '—';
                                    $unloadingDate = $unloadStep?->date ? $unloadStep->date->format('d.m.Y') . ($unloadStep->time ? ' ' . $unloadStep->time : '') : '—';
                                    $loadingAddr = $loadStep?->address ?? '';
                                    $unloadingAddr = $unloadStep?->address ?? '';
                                @endphp
                                <div class="text-sm text-gray-700 dark:text-gray-300 py-2 px-3 rounded-lg bg-white dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 space-y-0.5">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $senderName }}</div>
                                    <div class="text-gray-500">—</div>
                                    <div>{{ __('app.trip.show.order_loading') }}: {{ $loadingDate }}{{ $loadingAddr ? ' — ' . $loadingAddr : '' }}</div>
                                    <div class="text-gray-400">|</div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $receiverName }}</div>
                                    <div class="text-gray-500">—</div>
                                    <div>{{ __('app.trip.show.order_unloading') }}: {{ $unloadingDate }}{{ $unloadingAddr ? ' — ' . $unloadingAddr : '' }}</div>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.trip.show.orders_in_trip_empty') }}</p>
        @endif
        @if(isset($availableOrdersForTrip) && $availableOrdersForTrip->isNotEmpty() && $trip->status !== \App\Enums\TripStatus::COMPLETED)
            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('app.trip.show.add_orders_modal_title') }}</p>
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[200px] flex-1">
                        <select wire:model="add_orders_selection" multiple
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-3 py-2.5 min-h-[140px] focus:ring-2 focus:ring-amber-500 focus:border-amber-500 [&>option]:whitespace-pre [&>option]:py-2 [&>option]:leading-relaxed">
                            @foreach($availableOrdersForTrip as $o)
                                @php
                                    $loadingSteps = $o->steps->where('type', 'loading')->sortBy('order')->values();
                                    $unloadingSteps = $o->steps->where('type', 'unloading')->sortBy('order')->values();
                                    $cargos = $o->cargos;
                                    $lines = ["📋 {$o->number}" . ($o->customer ? ' / ' . $o->customer->company_name : '')];
                                    foreach ($cargos as $idx => $cargo) {
                                        $loadStep = $loadingSteps->get($idx) ?? $loadingSteps->first();
                                        $unloadStep = $unloadingSteps->get($idx) ?? $unloadingSteps->first();
                                        $senderName = $cargo->shipper?->company_name ?? $o->customer?->company_name ?? '—';
                                        $receiverName = $cargo->consignee?->company_name ?? '—';
                                        $loadDt = $loadStep ? ($loadStep->date?->format('d.m.Y') . ($loadStep->time ? ' ' . $loadStep->time : '') . ($loadStep->address ? ' — ' . $loadStep->address : '')) : '—';
                                        $unloadDt = $unloadStep ? ($unloadStep->date?->format('d.m.Y') . ($unloadStep->time ? ' ' . $unloadStep->time : '') . ($unloadStep->address ? ' — ' . $unloadStep->address : '')) : '—';
                                        $lines[] = $senderName . ' / ' . $loadDt . ' — ' . $receiverName . ' / ' . $unloadDt;
                                    }
                                    if ($cargos->isEmpty()) {
                                        $lines[] = '—';
                                    }
                                    $optionLabel = implode("\n", $lines);
                                @endphp
                                <option value="{{ $o->id }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('app.trip.show.add_orders_select_hint') }}</p>
                    </div>
                    <button type="button" wire:click="addOrdersToTrip"
                            class="px-4 py-2 rounded-lg text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white">
                        {{ __('app.trip.show.add_orders_to_trip') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- ===================================================================== --}}
    {{-- 🖥 DESKTOP HEADER                                                     --}}
    {{-- ===================================================================== --}}
    <div class="hidden md:block bg-white dark:bg-gray-900 shadow rounded-xl p-4 sm:p-6 space-y-4">

        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        🚛 {{ $isThirdPartyCarrier ? __('app.trip.show.order_trip') : __('app.trip.show.cmr_trip') }} #{{ $trip->id }}
                    </h1>

                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>

                    @if($schemeLabel)
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $schemeBadge }}">
                            {{ $schemeLabel }}
                        </span>
                    @endif
                </div>

                <p class="text-sm text-gray-500">
                    {{ __('app.trip.show.start_stop') }}
                    <span class="font-medium text-gray-700 dark:text-gray-200">
                        {{ $startDate?->format('d.m.Y') ?? '—' }} →
                        {{ $endDate?->format('d.m.Y') ?? '—' }}
                    </span>
                </p>

                @if($hasContInfo)
                    <p class="text-sm text-gray-500">
                        {{ __('app.trip.show.container_seal') }}
                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            📦 {{ $cont !== '' ? $cont : '—' }}
                            <span class="mx-2 text-gray-300 dark:text-gray-700">|</span>
                            🔒 {{ $seal !== '' ? $seal : '—' }}
                        </span>
                    </p>
                @endif

                <div class="mt-3 grid grid-cols-1 md:grid gap-3 text-xs sm:text-sm md:[grid-template-columns:1fr_1fr_2fr]">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 space-y-1">
                        <div class="text-gray-500 font-semibold">{{ __('app.trip.show.companies') }}</div>
                        <div class="text-gray-800 dark:text-gray-100">
                            <span class="text-gray-500">{{ __('app.trip.show.expeditor') }}</span>
                            <span class="font-medium">
                                {{ $expeditor?->name ?? '—' }}
                            </span>
                        </div>
                        <div class="text-gray-800 dark:text-gray-100">
                            <span class="text-gray-500">{{ __('app.trip.show.carrier') }}</span>
                            <span class="font-medium">
                                {{ $carrier?->name ?? '—' }}
                                @if($isThirdPartyCarrier)
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-800">
                                        {{ __('app.trip.show.third_party_carrier') }}
                                    </span>
                                @endif
                            </span>
                        </div>

                        @if($thirdPartyFee !== null)
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 pt-1">
                                {{ __('app.trip.show.third_party_fee') }}
                                <span class="font-semibold">
                                    €{{ number_format((float)$thirdPartyFee, 2, '.', ' ') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 space-y-1">
                        <div class="text-gray-500 font-semibold">{{ __('app.trip.show.transport') }}</div>
                        <div class="text-gray-800 dark:text-gray-100">
                            <span class="text-gray-500">{{ __('app.trip.show.driver') }}</span>
                            <span class="font-medium">{{ $driverName }}</span>
                        </div>
                        <div class="text-gray-800 dark:text-gray-100">
                            <span class="text-gray-500">{{ __('app.trip.show.truck') }}</span>
                            <span class="font-medium">{{ $truckLabel }}</span>
                        </div>
                        <div class="text-gray-800 dark:text-gray-100">
                            <span class="text-gray-500">{{ __('app.trip.show.trailer') }}</span>
                            <span class="font-medium">{{ $trailerLabel }}</span>
                        </div>
                    </div>

                    {{-- Расчёт маршрута (Google/HERE/ORS по ROUTE_PROVIDER), тип ТС: грузовик; при пересчёте блок оптимального пути скрывается --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 space-y-3 min-w-0">
                        <div>
                            <div class="text-gray-500 font-semibold">{{ __('app.trip.show.route_calc_title') }}</div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight mt-0.5">{{ __('app.orders.route_calc.vehicle_type') }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight mt-0.5">{{ __('app.trip.show.route_calc_optimal_hint') }}</p>
                        </div>
                        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
                            <div class="flex flex-col gap-2 shrink-0">
                                <button type="button" wire:click="calculateRouteDistance"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 min-h-[36px] touch-manipulation disabled:opacity-50 transition-colors min-w-[10rem]">
                                    <span wire:loading.remove wire:target="calculateRouteDistance">📏 {{ __('app.orders.route_calc.btn') }}</span>
                                    <span wire:loading wire:target="calculateRouteDistance" class="inline-flex items-center gap-2">
                                        <span class="inline-block h-3 w-3 rounded-full border-2 border-white border-t-transparent animate-spin" aria-hidden="true"></span>
                                        {{ __('app.please_wait') }}
                                    </span>
                                </button>
                                <button type="button" wire:click="calculateOptimalRoute"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-violet-600 text-white text-xs font-semibold hover:bg-violet-700 focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 min-h-[36px] touch-manipulation disabled:opacity-50 transition-colors min-w-[10rem]">
                                    <span wire:loading.remove wire:target="calculateOptimalRoute">🔄 {{ __('app.trip.show.route_optimal_btn') }}</span>
                                    <span wire:loading wire:target="calculateOptimalRoute" class="inline-flex items-center gap-2">
                                        <span class="inline-block h-3 w-3 rounded-full border-2 border-white border-t-transparent animate-spin" aria-hidden="true"></span>
                                        {{ __('app.please_wait') }}
                                    </span>
                                </button>
                            </div>
                            @if($routeSummary ?? null)
                                <div class="shrink-0 flex flex-col gap-1.5 text-base sm:text-lg">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 text-blue-800 dark:text-blue-200">
                                        <span class="text-blue-600 dark:text-blue-400 font-medium text-sm">{{ __('app.orders.route_calc.distance') }}:</span>
                                        <strong class="text-lg sm:text-xl">{{ number_format($routeSummary['distance_km'], 0, '.', ' ') }} km</strong>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1.5 text-emerald-800 dark:text-emerald-200">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium text-sm">{{ __('app.orders.route_calc.duration') }}:</span>
                                        <strong class="text-lg sm:text-xl">{{ $this->formatRouteDuration($routeSummary['duration_minutes']) }}</strong>
                                    </span>
                                </div>
                            @endif
                        </div>
                        @if($routeSummaryError ?? null)
                            <span class="block w-full text-xs text-amber-700 dark:text-amber-400 break-words">{{ $routeSummaryError }}</span>
                            @if($routeCalcConfigHint ?? false)
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block w-full">
                                    {{ __('app.orders.route_calc.not_configured_hint', ['key' => $routeProviderKey ?? 'OPENROUTESERVICE_API_KEY']) }}
                                    <a href="{{ $routeProviderLink ?? 'https://openrouteservice.org/dev/#/login' }}" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">{{ ($routeProviderKey ?? '') === 'GOOGLE_MAPS_API_KEY' ? 'console.cloud.google.com' : (($routeProviderKey ?? '') === 'HERE_API_KEY' ? 'developer.here.com' : 'openrouteservice.org') }}</a>
                                </span>
                            @endif
                        @endif
                        @if($routeSummaryOptimal !== null)
                            <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-600 space-y-3">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.trip.show.route_optimal_title') }}</div>
                                <div class="flex flex-wrap items-baseline gap-x-4 gap-y-2 text-base">
                                    <span class="inline-flex items-center gap-2 rounded-lg bg-violet-50 dark:bg-violet-900/30 px-3 py-2 text-violet-800 dark:text-violet-200">
                                        <strong class="text-lg">{{ number_format($routeSummaryOptimal['distance_km'], 0, '.', ' ') }} km</strong>
                                        <span class="text-violet-600 dark:text-violet-400 text-sm">{{ __('app.orders.route_calc.duration') }}: {{ $this->formatRouteDuration($routeSummaryOptimal['duration_minutes']) }}</span>
                                    </span>
                                    @if($savedKm !== null && $savedKm > 0)
                                        <span class="text-emerald-700 dark:text-emerald-400 font-medium">{{ __('app.trip.show.route_optimal_saved', ['km' => number_format($savedKm, 0, '.', ' ')]) }}</span>
                                    @elseif($savedKm !== null && $savedKm <= 0)
                                        <span class="text-emerald-700 dark:text-emerald-400 font-medium">{{ __('app.trip.show.route_current_is_optimal') }}</span>
                                    @endif
                                </div>
                                @if(!empty($routeSuggestedOrderLabels))
                                    <div>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-1">{{ __('app.trip.show.route_optimal_suggested_order') }}</p>
                                        <p class="text-base text-gray-700 dark:text-gray-300 break-words leading-relaxed">{{ implode(' → ', array_map('e', $routeSuggestedOrderLabels)) }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if($routeOptimalError)
                            <span class="block w-full text-xs text-amber-700 dark:text-amber-400 break-words">{{ $routeOptimalError }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-start gap-2 shrink-0">
                @if($trip->status !== \App\Enums\TripStatus::COMPLETED)
                <a href="{{ route('trips.edit', $trip->id) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white">
                    ✏️ {{ __('app.trip.show.edit') }}
                </a>
                @endif

                <a href="{{ route('trips.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm bg-gray-100 hover:bg-gray-200 text-gray-700">
                    ⬅ {{ __('app.trip.show.back_to_trips') }}
                </a>
            </div>
        </div>

        {{-- Публичные ссылки отслеживания по грузу (клиент видит только свой груз; после разгрузки ссылка недействительна) --}}
        <div class="mt-4 space-y-3">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.track.link_title') }}</p>
            @foreach($trip->cargos as $cargo)
                @php
                    $sender = $cargo->shipper?->company_name ?? $cargo->customer?->company_name ?? '—';
                    $recipient = $cargo->consignee?->company_name ?? '—';
                @endphp
                <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50" x-data="{ copied: false }">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.track.cargo_for', ['sender' => $sender, 'recipient' => $recipient]) }}</p>
                    @if($cargo->tracking_token)
                        <div class="flex flex-wrap items-center gap-2">
                            <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded truncate max-w-full">{{ route('track.show', $cargo->tracking_token) }}</code>
                            <button type="button"
                                    @click="navigator.clipboard && navigator.clipboard.writeText('{{ route('track.show', $cargo->tracking_token) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                                    class="shrink-0 px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-200">
                                <span x-show="!copied">{{ __('app.track.copy_link') }}</span>
                                <span x-show="copied" x-cloak>{{ __('app.track.copied') }}</span>
                            </button>
                            <a href="{{ route('track.show', $cargo->tracking_token) }}" target="_blank" rel="noopener"
                               class="shrink-0 text-xs text-blue-600 hover:underline">↗ {{ __('app.track.title') }}</a>
                            @if($trip->status !== \App\Enums\TripStatus::COMPLETED)
                                <button type="button" wire:click="disableCargoTracking({{ $cargo->id }})" data-no-loading-overlay
                                        class="shrink-0 px-2 py-1 rounded text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    {{ __('app.track.disable') }}
                                </button>
                            @endif
                        </div>
                    @else
                        <button type="button" wire:click="enableCargoTracking({{ $cargo->id }})" data-no-loading-overlay
                                class="px-3 py-1.5 rounded-lg text-sm font-medium bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-200">
                            {{ __('app.track.enable') }}
                        </button>
                    @endif
                </div>
            @endforeach
            @if($trip->cargos->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.trip.show.cargos') }}: 0. {{ __('app.track.link_per_cargo') }}</p>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mt-4 text-xs sm:text-sm">

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">{{ __('app.trip.show.cargos') }}</div>
                <div class="font-semibold">{{ $totalCargosCount }}</div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">{{ __('app.trip.show.items_lines') }}</div>
                <div class="font-semibold">{{ $totalItemLines }}</div>
                <div class="text-[11px] text-gray-400">
                    {{ __('app.trip.show.unique') }} {{ $totalUniqueItemNames }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">{{ __('app.trip.show.packages_pallets') }}</div>
                <div class="font-semibold">
                    {{ number_format($totalPackages, 0, '.', ' ') }} / {{ number_format($totalPallets, 0, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    {{ __('app.trip.show.units') }} {{ number_format($totalUnits, 0, '.', ' ') }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">{{ __('app.trip.show.net_gross') }}</div>
                <div class="font-semibold">
                    {{ number_format($totalNet, 0, '.', ' ') }} / {{ number_format($totalGross, 0, '.', ' ') }} kg
                </div>
                <div class="text-[11px] text-gray-400">
                    {{ __('app.trip.show.tonnes') }} {{ number_format($totalTonnes, 2, '.', ' ') }} t
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">{{ __('app.trip.show.volume_lm') }}</div>
                <div class="font-semibold">
                    {{ number_format($totalVolume, 2, '.', ' ') }} m³
                </div>
                <div class="text-[11px] text-gray-400">
                    {{ __('app.trip.show.lm') }} {{ number_format($totalLm, 2, '.', ' ') }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">{{ __('app.trip.show.freight_with_vat') }}</div>
                <div class="font-semibold">
                    €{{ number_format($totalFreightWithVat, 2, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    {{ __('app.trip.show.no_vat') }} €{{ number_format($totalFreightNoVat, 2, '.', ' ') }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800">
                <div class="text-gray-500">{{ __('app.trip.show.supplier_invoice') }}</div>
                <div class="font-semibold">
                    €{{ number_format($totalSupplierInvoice, 2, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    {{ __('app.trip.show.sum_of_cargos') }}
                </div>
            </div>

            <div class="border rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-800 lg:col-span-2">
                <div class="text-gray-500">{{ __('app.trip.show.goods_value') }}</div>
                <div class="font-semibold">
                    €{{ number_format($totalGoodsWithVat, 2, '.', ' ') }}
                </div>
                <div class="text-[11px] text-gray-400">
                    {{ __('app.trip.show.no_vat') }} €{{ number_format($totalGoodsNoVat, 2, '.', ' ') }} • {{ __('app.trip.show.vat') }} €{{ number_format($totalGoodsVat, 2, '.', ' ') }}
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
            🛣️ {{ __('app.trip.route.accordion') }}
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
            @php $customer = $customerCargos->first()?->customer; @endphp

            <div x-data="{ openClient: false }"
                 class="bg-white dark:bg-gray-900 shadow rounded-xl border border-gray-200 dark:border-gray-700">

                {{-- CLIENT HEADER --}}
                <button type="button" @click="openClient = !openClient"
                        class="w-full flex items-center justify-between p-4 text-left">
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold truncate">{{ $customer?->company_name ?? '—' }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ $customer ? getCountryById($customer->jur_country_id) : '—' }},
                            {{ $customer ? getCityNameByCountryId($customer->jur_country_id, $customer->jur_city_id) : '—' }}
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
                                    <p class="font-semibold">📦 {{ __('app.trip.show.cargo_label', ['id' => $cargo->id]) }}</p>

                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $cargo->shipper->company_name ?? '—' }} →
                                        {{ $cargo->consignee->company_name ?? '—' }}
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ number_format($cPackages, 0, '.', ' ') }} {{ __('app.trip.show.pkgs') }} •
                                        {{ number_format($cPallets, 0, '.', ' ') }} {{ __('app.trip.show.pallets') }} •
                                        {{ number_format($cUnits, 0, '.', ' ') }} {{ __('app.trip.show.units_short') }} •
                                        {{ number_format($cGross, 0, '.', ' ') }} kg •
                                        {{ number_format($cVolume, 2, '.', ' ') }} m³
                                        @if($cLm > 0) • {{ number_format($cLm, 2, '.', ' ') }} LM @endif
                                    </p>

                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ __('app.trip.show.freight') }}
                                        <span class="font-semibold">€{{ number_format($cargoFreightWithVat, 2, '.', ' ') }}</span>
                                        <span class="text-[11px] text-gray-400">({{ __('app.trip.show.no_vat') }} €{{ number_format($cargoFreightNoVat, 2, '.', ' ') }})</span>

                                        <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>

                                        {{ __('app.trip.show.goods') }}
                                        <span class="font-semibold">€{{ number_format($cargoGoodsWithVat, 2, '.', ' ') }}</span>

                                        @if($cargoSupplier > 0)
                                            <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>
                                            {{ __('app.trip.show.supplier_inv') }}
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
                                        🗺 {{ __('app.trip.show.route_points') }}
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

                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                {{-- ITEMS ACCORDION --}}
                                <div x-data="{ openItems: false }">
                                    <button type="button" @click="openItems = !openItems"
                                            class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold">
                                        📦 {{ __('app.trip.show.items_accordion') }}
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
                                                        {{ (int)($item->packages ?? 0) }} {{ __('app.trip.show.pkgs') }} •
                                                        {{ (int)($item->pallets ?? 0) }} {{ __('app.trip.show.pallets') }} •
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

                                {{-- DELAY (Dikstāve) — если простой был введён ранее: строка с данными + удалить; иначе: чекбокс и поля --}}
                                @php
                                    $hasSavedDelay = !empty($cargo->has_delay) && $cargo->delay_days !== null && $cargo->delay_amount !== null;
                                @endphp
                                <div class="bg-white dark:bg-gray-900 px-3 py-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    @if($hasSavedDelay)
                                        {{-- Сохранённый простой: строка с информацией + кнопка удалить --}}
                                        <div class="flex items-center justify-between gap-3 flex-wrap">
                                            <div class="text-sm">
                                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ __('app.trip.show.delay') }}:</span>
                                                <span class="text-gray-700 dark:text-gray-300">
                                                    {{ (int)$cargo->delay_days }} {{ (int)$cargo->delay_days === 1 ? __('app.trip.show.delay_day') : __('app.trip.show.delay_days_unit') }},
                                                    €{{ number_format((float)$cargo->delay_amount, 2, '.', ' ') }} ({{ __('app.trip.show.no_vat') }})
                                                </span>
                                            </div>
                                            <button type="button"
                                                    wire:click="removeDelay({{ $cargo->id }})"
                                                    class="px-3 py-1.5 text-sm font-medium rounded-lg border border-red-300 text-red-700 bg-red-50 hover:bg-red-100 dark:border-red-600 dark:text-red-300 dark:bg-red-900/20 dark:hover:bg-red-900/30">
                                                {{ __('app.trip.show.delay_remove') }}
                                            </button>
                                        </div>
                                    @else
                                        {{-- Нет сохранённого простоя: чекбокс и поля --}}
                                        <div x-data="{ delayChecked: @entangle('delayChecked.'.$cargo->id) }">
                                            <div class="flex items-center gap-3 flex-wrap">
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                           x-model="delayChecked"
                                                           @change="!delayChecked && $wire.saveDelay({{ $cargo->id }})"
                                                           class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                                                    <span class="font-semibold text-sm">{{ __('app.trip.show.delay') }}</span>
                                                </label>
                                                <div class="flex flex-wrap items-center gap-3" x-show="delayChecked" x-cloak x-collapse>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ __('app.trip.show.delay_days') }}</label>
                                                        <input type="number"
                                                               wire:model.blur="delayDays.{{ $cargo->id }}"
                                                               min="1" max="365"
                                                               class="w-20 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                                                        @error('delayDays.'.$cargo->id)
                                                            <div class="text-[11px] text-red-600 mt-0.5">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ __('app.trip.show.delay_amount') }}</label>
                                                        <input type="number"
                                                               wire:model.blur="delayAmount.{{ $cargo->id }}"
                                                               step="0.01" min="0"
                                                               class="w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                                                        @error('delayAmount.'.$cargo->id)
                                                            <div class="text-[11px] text-red-600 mt-0.5">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <button type="button"
                                                            wire:click="saveDelay({{ $cargo->id }})"
                                                            class="self-end px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
                                                        {{ __('app.trip.show.delay_save') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- DOCS ACCORDION (manual numbers before generate) --}}
                                <div x-data="{ openDocs: false }" class="space-y-2">
                                    <button
                                        type="button"
                                        @click="openDocs = !openDocs"
                                        class="w-full bg-white dark:bg-gray-900 px-3 py-2 rounded-lg flex items-center justify-between text-sm font-semibold"
                                    >
                                        📄 {{ __('app.trip.show.documents') }}
                                        <span class="transition-transform" :class="{ 'rotate-180': openDocs }">▼</span>
                                    </button>

                                    <div x-cloak x-show="openDocs" x-collapse class="mt-3 space-y-3 text-xs">

                                        {{-- CMR (only for own/resell transport, not third-party carrier) --}}
                                        @unless($isThirdPartyCarrier)
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
                                                        <div class="font-semibold text-gray-800 dark:text-gray-100">📘 {{ __('app.trip.show.cmr') }}</div>
                                                        @if(!empty($cargo->cmr_nr))
                                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                                {{ __('app.trip.show.nr') }} <span class="font-medium">{{ $cargo->cmr_nr }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if ($cargo->cmr_file)
                                                        <a href="{{ asset('storage/'.$cargo->cmr_file) }}" target="_blank"
                                                           class="shrink-0 px-3 py-2 bg-blue-200 text-blue-900 rounded-lg font-semibold">
                                                            👁 {{ __('app.trip.show.open') }}
                                                        </a>
                                                    @endif
                                                </div>

                                                @if (!$cargo->cmr_file)
                                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-center">
                                                        <div>
                                                            <input type="text"
                                                                   x-model.trim="nr"
                                                                   placeholder="{{ __('app.trip.show.cmr_placeholder') }}"
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
                                                            <span x-show="!loading">{{ __('app.trip.show.generate') }}</span>
                                                            <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endunless

                                        {{-- ORDER (only for third-party carrier, expeditor issues order to carrier) --}}
                                        @if($isThirdPartyCarrier)
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
                                                        <div class="font-semibold text-gray-800 dark:text-gray-100">📄 {{ __('app.trip.show.order') }}</div>
                                                        @if(!empty($cargo->order_nr))
                                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                                {{ __('app.trip.show.nr') }} <span class="font-medium">{{ $cargo->order_nr }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if ($cargo->order_file)
                                                        <a href="{{ asset('storage/'.$cargo->order_file) }}" target="_blank"
                                                           class="shrink-0 px-3 py-2 bg-indigo-200 text-indigo-900 rounded-lg font-semibold">
                                                            👁 {{ __('app.trip.show.open') }}
                                                        </a>
                                                    @endif
                                                </div>

                                                @if (!$cargo->order_file)
                                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-center">
                                                        <div>
                                                            <input type="text"
                                                                   x-model.trim="nr"
                                                                   placeholder="{{ __('app.trip.show.order_placeholder') }}"
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
                                                            <span x-show="!loading">{{ __('app.trip.show.generate') }}</span>
                                                            <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

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
                                                    <div class="font-semibold text-gray-800 dark:text-gray-100">💶 {{ __('app.trip.show.invoice') }}</div>
                                                    @if(!empty($cargo->inv_nr))
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                            {{ __('app.trip.show.nr') }} <span class="font-medium">{{ $cargo->inv_nr }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($cargo->inv_file)
                                                    <a href="{{ asset('storage/'.$cargo->inv_file) }}" target="_blank"
                                                       class="shrink-0 px-3 py-2 bg-amber-200 text-amber-900 rounded-lg font-semibold">
                                                        👁 {{ __('app.trip.show.open') }}
                                                    </a>
                                                @endif
                                            </div>

                                            @if (!$cargo->inv_file)
                                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-center">
                                                    <div>
                                                        <input type="text"
                                                               x-model.trim="nr"
                                                               placeholder="{{ __('app.trip.show.invoice_placeholder') }}"
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
                                                        <span x-show="!loading">{{ __('app.trip.show.generate') }}</span>
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
            <h2 class="text-lg font-semibold">📄 {{ __('app.trip.show.documents_trip') }}</h2>
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
            <h2 class="text-lg font-semibold">💶 {{ __('app.trip.show.expenses_trip') }}</h2>
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

            Livewire.on('cmrGenerated', () => toast('{{ __("app.trip.show.toast_cmr") }}', 'bg-green-600'));
            Livewire.on('orderGenerated', () => toast('{{ __("app.trip.show.toast_order") }}', 'bg-indigo-600'));
            Livewire.on('invoiceGenerated', () => toast('{{ __("app.trip.show.toast_invoice") }}', 'bg-amber-600'));
            Livewire.on('stepDocumentDeleted', () => toast('{{ __("app.trip.show.toast_doc_deleted") }}', 'bg-red-600'));
            Livewire.on('stepDocumentUploaded', () => toast('{{ __("app.trip.show.toast_doc_uploaded") }}', 'bg-green-600'));
            Livewire.on('tripDocumentUploaded', () => toast('{{ __("app.trip.show.toast_trip_doc") }}', 'bg-green-600'));
            Livewire.on('tripExpenseAdded', () => toast('{{ __("app.trip.show.toast_expense_saved") }}', 'bg-green-600'));
            Livewire.on('tripExpenseDeleted', () => toast('{{ __("app.trip.show.toast_expense_deleted") }}', 'bg-red-600'));
            Livewire.on('delaySaved', () => toast('{{ __("app.trip.show.toast_delay_saved") }}', 'bg-green-600'));
            Livewire.on('delayRemoved', () => toast('{{ __("app.trip.show.toast_deleted") }}', 'bg-green-600'));
            Livewire.on('delayRemoveError', () => toast('{{ __("app.trip.show.toast_error") }}', 'bg-red-600'));
        </script>
    @endpush

</div>
