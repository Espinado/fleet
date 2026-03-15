{{-- resources/views/livewire/orders/orders-table.blade.php --}}

<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    @if (session('success'))
        <div class="mb-4 p-4 rounded bg-green-100 border border-green-400 text-green-800">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 rounded bg-red-100 border border-red-400 text-red-800">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-4">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input
                    type="text"
                    placeholder="🔍 {{ __('app.orders.search_placeholder') }}"
                    wire:model.live.debounce.300ms="search"
                    class="w-full border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
                >
                @if ($search)
                    <button wire:click="$set('search','')"
                            class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm"
                            title="{{ __('app.orders.clear_search') }}">✖</button>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 w-full md:w-auto">
                <a href="{{ route('orders.create') }}"
                   class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700">
                    ➕ {{ __('app.orders.add') }}
                </a>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 whitespace-nowrap">{{ __('app.orders.rows') }}</label>
                    <select wire:model.live="perPage" class="border rounded-lg px-2 py-1 text-sm shadow-sm w-16">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 min-w-0">
                    <label class="text-sm text-gray-600 whitespace-nowrap">{{ __('app.orders.status_label') }}</label>
                    <select wire:model.live="status" class="border rounded-lg px-2 py-1 text-sm shadow-sm min-w-0 flex-1 md:w-40">
                        <option value="">{{ __('app.orders.status_all') }}</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Мобильная карточная раскладка (PWA на телефоне) — по умолчанию удобно на маленьком экране --}}
        <div class="md:hidden space-y-3" wire:loading.class="opacity-50">
            @forelse($orders as $order)
                @php $status = $order->status instanceof \App\Enums\OrderStatus ? $order->status : \App\Enums\OrderStatus::tryFrom($order->status); @endphp
                <a href="{{ route('orders.show', $order) }}" wire:navigate
                   class="block bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl p-4 transition">
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-semibold text-gray-900">{{ $order->number }}</span>
                        @if($status)
                            <span class="shrink-0 px-2 py-0.5 rounded text-xs font-medium {{ $status->color() }}">{{ $status->label() }}</span>
                        @endif
                    </div>
                    <div class="mt-2 text-sm text-gray-600">{{ $order->expeditor?->name ?? '—' }}</div>
                    @if($order->customer?->company_name)
                        <div class="text-sm text-gray-500 truncate">{{ $order->customer->company_name }}</div>
                    @endif
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ $order->order_date?->format('d.m.Y') ?? '—' }}</span>
                        @if($order->quoted_price !== null)
                            <span class="font-medium text-gray-700">{{ number_format((float)$order->quoted_price, 2, '.', ' ') }} {{ $order->currency }}</span>
                        @endif
                    </div>
                    @if($order->trip_id)
                        <div class="mt-1 text-xs text-gray-400">🔗 {{ __('app.orders.linked_trip') }}</div>
                    @endif
                </a>
            @empty
                <div class="py-8 text-center text-gray-500">{{ __('app.orders.empty') }}</div>
            @endforelse
        </div>

        {{-- Десктоп: таблица --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700 border-b text-sm">
                    <tr>
                        <th class="px-3 py-2 text-left cursor-pointer select-none" wire:click="sortBy('number')">
                            {{ __('app.orders.col_number') }}
                            @if($sortField === 'number') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="px-3 py-2 text-left cursor-pointer select-none" wire:click="sortBy('order_date')">
                            {{ __('app.orders.col_order_date') }}
                            @if($sortField === 'order_date') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="px-3 py-2 text-left">{{ __('app.orders.col_expeditor') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('app.orders.col_customer') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('app.orders.col_requested_dates') }}</th>
                        <th class="px-3 py-2 text-right cursor-pointer select-none" wire:click="sortBy('quoted_price')">
                            {{ __('app.orders.col_quoted_price') }}
                            @if($sortField === 'quoted_price') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="px-3 py-2 cursor-pointer select-none" wire:click="sortBy('status')">
                            {{ __('app.orders.col_status') }}
                            @if($sortField === 'status') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="px-3 py-2 text-right">{{ __('app.orders.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-50">
                    @forelse($orders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm font-medium">
                                <a href="{{ route('orders.show', $order) }}" wire:navigate class="text-blue-600 hover:underline">
                                    {{ $order->number }}
                                </a>
                            </td>
                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                {{ $order->order_date?->format('d.m.Y') ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm">{{ $order->expeditor?->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-sm">{{ $order->customer?->company_name ?? '—' }}</td>
                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                @if($order->requested_date_from || $order->requested_date_to)
                                    {{ $order->requested_date_from?->format('d.m.Y') ?? '—' }}
                                    → {{ $order->requested_date_to?->format('d.m.Y') ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm text-right">
                                @if($order->quoted_price !== null)
                                    {{ number_format((float)$order->quoted_price, 2, '.', ' ') }} {{ $order->currency }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @php $status = $order->status instanceof \App\Enums\OrderStatus ? $order->status : \App\Enums\OrderStatus::tryFrom($order->status); @endphp
                                @if($status)
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $status->color() }}">{{ $status->label() }}</span>
                                @else
                                    <span class="text-gray-500">{{ $order->status ?? '—' }}</span>
                                @endif
                                @if($order->trip_id)
                                    <span class="ml-1 text-xs text-gray-500" title="{{ __('app.orders.linked_trip') }}">🔗</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right text-sm whitespace-nowrap">
                                <a href="{{ route('orders.show', $order) }}" wire:navigate class="text-blue-600 hover:underline mr-2">{{ __('app.orders.view') }}</a>
                                <a href="{{ route('orders.edit', $order) }}" wire:navigate class="text-gray-600 hover:underline">{{ __('app.orders.edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500">
                                {{ __('app.orders.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
