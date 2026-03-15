{{-- resources/views/livewire/orders/show-order.blade.php --}}

<div class="p-4 sm:p-6 max-w-5xl mx-auto">

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
        <div class="flex items-center gap-2">
            @if(!$transportOrder->trip_id)
                <a href="{{ route('trips.create', ['from_order' => $transportOrder->id]) }}" wire:navigate
                   class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-2 rounded-lg shadow hover:bg-green-700">
                    🚛 {{ __('app.orders.show.create_trip') }}
                </a>
            @else
                <a href="{{ route('trips.show', $transportOrder->trip_id) }}" wire:navigate
                   class="inline-flex items-center gap-1 bg-blue-600 text-white text-sm font-medium px-3 py-2 rounded-lg shadow hover:bg-blue-700">
                    🔗 {{ __('app.orders.linked_trip') }}
                </a>
            @endif
            <a href="{{ route('orders.edit', $transportOrder) }}" wire:navigate
               class="inline-flex items-center gap-1 bg-gray-200 text-gray-800 text-sm font-medium px-3 py-2 rounded-lg hover:bg-gray-300">
                ✏️ {{ __('app.orders.edit') }}
            </a>
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
</div>
