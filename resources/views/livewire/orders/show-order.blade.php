{{-- resources/views/livewire/orders/show-order.blade.php --}}

<div class="p-4 sm:p-6 max-w-5xl mx-auto">
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm sm:text-base">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.index') }}" wire:navigate
               class="inline-flex items-center gap-1 text-gray-600 hover:text-gray-900 text-sm">
                ← {{ __('app.orders.show.back') }}
            </a>
            <h1 class="text-xl font-semibold text-gray-900">{{ $transportOrder->number }}</h1>
            @php $status = $transportOrder->status instanceof \App\Enums\OrderStatus ? $transportOrder->status : \App\Enums\OrderStatus::tryFrom($transportOrder->status); @endphp
            @if($status)
                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $status->color() }}">{{ $status->label() }}</span>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if(!$transportOrder->trip_id)
                @php
                    $canAddToTrip = in_array($status?->value ?? $transportOrder->status ?? '', ['draft', 'quoted', 'confirmed'], true);
                @endphp
                @if($canAddToTrip)
                    <button type="button" wire:click="openAddToTripModal"
                            class="inline-flex items-center gap-1.5 bg-slate-600 text-white text-sm font-medium px-3 py-2.5 rounded-lg shadow hover:bg-slate-700 min-h-[44px] touch-manipulation">
                        ➕ {{ __('app.orders.add_to_trip.btn') }}
                    </button>
                @endif
                <a href="{{ route('trips.create', ['from_order' => $transportOrder->id]) }}" wire:navigate
                   class="inline-flex items-center gap-1.5 bg-green-600 text-white text-sm font-medium px-3 py-2.5 rounded-lg shadow hover:bg-green-700 min-h-[44px] touch-manipulation">
                    🚛 {{ __('app.orders.show.create_trip') }}
                </a>
            @else
                <a href="{{ route('trips.show', $transportOrder->trip_id) }}" wire:navigate
                   class="inline-flex items-center gap-1 bg-blue-600 text-white text-sm font-medium px-3 py-2 rounded-lg shadow hover:bg-blue-700">
                    🔗 {{ __('app.orders.linked_trip') }}
                </a>
            @endif
            @if($status?->value !== 'converted')
                <a href="{{ route('orders.edit', $transportOrder) }}" wire:navigate
                   class="inline-flex items-center gap-1 bg-gray-200 text-gray-800 text-sm font-medium px-3 py-2 rounded-lg hover:bg-gray-300">
                    ✏️ {{ __('app.orders.edit') }}
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-6 space-y-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <dt class="text-xs text-gray-500">{{ __('app.orders.col_order_date') }}</dt>
                    <dd class="font-medium">{{ $transportOrder->order_date?->format('d.m.Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">{{ __('app.orders.col_expeditor') }}</dt>
                    <dd class="font-medium">{{ $transportOrder->expeditor?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">{{ __('app.orders.col_customer') }}</dt>
                    <dd class="font-medium">{{ $transportOrder->customer?->company_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">{{ __('app.orders.col_requested_dates') }}</dt>
                    <dd class="font-medium">
                        @if($transportOrder->requested_date_from || $transportOrder->requested_date_to)
                            {{ $transportOrder->requested_date_from?->format('d.m.Y') ?? '—' }}
                            → {{ $transportOrder->requested_date_to?->format('d.m.Y') ?? '—' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">{{ __('app.orders.quoted_price_total_hint') }}</dt>
                    <dd class="font-medium">
                        @if($transportOrder->quoted_price !== null)
                            {{ number_format((float)$transportOrder->quoted_price, 2, '.', ' ') }} {{ $transportOrder->currency }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
            @if($transportOrder->customs)
                <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-3">
                    <dt class="text-xs text-gray-600 font-medium mb-1">🧾 {{ __('app.orders.customs_title') }}</dt>
                    <dd class="text-sm">{{ $transportOrder->customs_address ?? '—' }}</dd>
                </div>
            @endif
            @if($transportOrder->notes)
                <div>
                    <dt class="text-xs text-gray-500 mb-1">{{ __('app.orders.show.title') }} — {{ __('app.trips.details') }}</dt>
                    <dd class="text-sm text-gray-700 whitespace-pre-wrap">{{ $transportOrder->notes }}</dd>
                </div>
            @endif
        </div>

        @if($transportOrder->steps->isNotEmpty())
            <div class="border-t border-gray-200 p-4 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">{{ __('app.orders.steps_title') }}</h2>
                <ul class="space-y-3">
                    @foreach($transportOrder->steps as $step)
                        @php
                            $country = $step->country_id && function_exists('getCountryById') ? getCountryById((int)$step->country_id) : null;
                            $city = ($step->country_id && $step->city_id && function_exists('getCityNameByCountryId')) ? getCityNameByCountryId((int)$step->country_id, $step->city_id) : null;
                        @endphp
                        <li class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-3 text-sm">
                            <span class="shrink-0 px-2 py-0.5 rounded text-xs font-medium {{ $step->type === 'loading' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $step->type === 'loading' ? __('app.orders.step_type_loading') : __('app.orders.step_type_unloading') }}
                            </span>
                            <span class="font-medium text-gray-700">{{ $step->date?->format('d.m.Y') ?? '—' }}{{ $step->time ? ' ' . $step->time : '' }}</span>
                            <span class="text-gray-600">
                                @php
                                    $parts = array_filter([$city, $country]);
                                    $location = implode(', ', $parts);
                                    if ($step->address) {
                                        $location = $location ? $location . ' — ' . $step->address : $step->address;
                                    }
                                @endphp
                                {{ $location ?: '—' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                {{-- Расчёт километража по маршруту (Google/HERE/ORS) — помощник для тарифов, тип ТС: грузовик --}}
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">{{ __('app.orders.route_calc.title') }}</p>
                    <p class="text-xs text-gray-500 mb-2">{{ __('app.orders.route_calc.vehicle_type') }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="calculateRouteDistance"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 min-h-[44px] touch-manipulation disabled:opacity-50 transition-colors min-w-[18rem]">
                            <span wire:loading.remove wire:target="calculateRouteDistance">📏 {{ __('app.orders.route_calc.btn') }}</span>
                            <span wire:loading wire:target="calculateRouteDistance" class="inline-flex items-center gap-2">
                                <span class="inline-block h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin" aria-hidden="true"></span>
                                {{ __('app.please_wait') }}
                            </span>
                        </button>
                        @if($routeSummary)
                            <span class="inline-flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm">
                                <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-blue-800">
                                    <span class="text-blue-600 font-medium">{{ __('app.orders.route_calc.distance') }}:</span>
                                    <strong>{{ number_format($routeSummary['distance_km'], 0, '.', ' ') }} km</strong>
                                </span>
                                <span class="text-gray-400" aria-hidden="true">·</span>
                                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-emerald-800">
                                    <span class="text-emerald-600 font-medium">{{ __('app.orders.route_calc.duration') }}:</span>
                                    <strong>{{ $this->formatRouteDuration($routeSummary['duration_minutes']) }}</strong>
                                </span>
                            </span>
                        @endif
                        @if($routeSummaryError)
                            <span class="text-sm text-amber-700">{{ $routeSummaryError }}</span>
                            @if($routeCalcConfigHint ?? false)
                                <span class="text-xs text-gray-500 block mt-1">
                                    {{ __('app.orders.route_calc.not_configured_hint', ['key' => $routeProviderKey ?? 'OPENROUTESERVICE_API_KEY']) }}
                                    <a href="{{ $routeProviderLink ?? 'https://openrouteservice.org/dev/#/login' }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ ($routeProviderKey ?? '') === 'HERE_API_KEY' ? 'developer.here.com' : 'openrouteservice.org' }}</a>
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($transportOrder->cargos->isNotEmpty())
            <div class="border-t border-gray-200 p-4 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-1">{{ __('app.stats.clients.col_cargos') }}</h2>
                <p class="text-xs text-gray-500 mb-3">{{ __('app.orders.cargos_price_per_client_hint') }}</p>
                <ul class="space-y-2 text-sm">
                    @foreach($transportOrder->cargos as $cargo)
                        <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                            @if($cargo->customer)
                                <span class="font-medium">{{ $cargo->customer->company_name }}</span>
                                <span class="text-gray-400">·</span>
                            @endif
                            @if($cargo->quoted_price !== null)
                                <span>{{ number_format((float)$cargo->quoted_price, 2, '.', ' ') }} {{ $transportOrder->currency }}</span>
                                <span class="text-gray-400">·</span>
                            @endif
                            @if($cargo->requested_date_from || $cargo->requested_date_to)
                                <span>{{ $cargo->requested_date_from?->format('d.m.Y') ?? '—' }} → {{ $cargo->requested_date_to?->format('d.m.Y') ?? '—' }}</span>
                                <span class="text-gray-400">·</span>
                            @endif
                            {{ $cargo->description ?: '—' }}
                            @if($cargo->weight_kg) · {{ $cargo->weight_kg }} kg @endif
                            @if($cargo->shipper) · {{ $cargo->shipper->company_name }} @endif
                            @if($cargo->consignee) → {{ $cargo->consignee->company_name }} @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Модалка: добавить заказ в существующий рейс (только незавершённые) --}}
    @if($showAddToTripModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
            <div class="fixed inset-0 bg-black/50" wire:click="closeAddToTripModal" aria-hidden="true"></div>
            <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
                <div class="relative w-full max-w-lg max-h-[85vh] flex flex-col rounded-xl bg-white shadow-xl border border-gray-200">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-5 sm:py-4 border-b border-gray-200 shrink-0">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                            {{ __('app.orders.add_to_trip.modal_title') }}
                        </h2>
                        <button type="button" wire:click="closeAddToTripModal"
                                class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 touch-manipulation"
                                aria-label="Close">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="px-4 sm:px-5 pt-2 text-sm text-gray-600">
                        {{ __('app.orders.add_to_trip.modal_hint') }}
                    </p>
                    <div class="flex-1 overflow-y-auto px-4 sm:px-5 py-3 space-y-2">
                        @if(count($availableTrips) === 0)
                            <p class="py-6 text-center text-gray-500 text-base">
                                {{ __('app.orders.add_to_trip.no_trips') }}
                            </p>
                        @else
                            @foreach($availableTrips as $t)
                                @php
                                    $tripStatus = $t->status instanceof \App\Enums\TripStatus ? $t->status : \App\Enums\TripStatus::tryFrom($t->status);
                                @endphp
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 p-3 sm:p-4 rounded-lg border border-gray-200 bg-gray-50/50 hover:bg-gray-50">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-gray-900 text-base">#{{ $t->id }}</span>
                                            @if($tripStatus)
                                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $tripStatus->color() }}">{{ $tripStatus->label() }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm text-gray-600">
                                            {{ $t->start_date?->format('d.m.Y') ?? '—' }}
                                            @if($t->end_date && $t->end_date->format('Y-m-d') !== $t->start_date?->format('Y-m-d'))
                                                — {{ $t->end_date->format('d.m.Y') }}
                                            @endif
                                        </div>
                                        @if($t->carrierCompany || $t->driver)
                                            <p class="mt-0.5 text-xs text-gray-500 truncate">
                                                {{ $t->carrierCompany?->name ?? $t->driver?->full_name ?? '' }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="shrink-0">
                                        <button type="button"
                                                wire:click="addOrderToTrip({{ $t->id }})"
                                                @disabled($addingTripId === $t->id)
                                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-green-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-green-700 min-h-[44px] touch-manipulation disabled:opacity-50">
                                            @if($addingTripId === $t->id)
                                                <span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                            @else
                                                {{ __('app.orders.add_trip.select') }}
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
