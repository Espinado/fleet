<div class="min-h-screen bg-gray-100 pb-24">

    {{-- TOP BAR --}}
    <div class="sticky top-0 z-30 bg-white/95 border-b border-gray-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 truncate">💶 {{ __('app.inv.title') }}</h1>

            <div class="hidden sm:flex text-sm text-gray-600 items-center">
                <span class="px-3 py-2 rounded-xl bg-gray-200/60">
                    {{ __('app.inv.rows') }} {{ $rows->total() }}
                </span>
            </div>
        </div>

        {{-- Filters --}}
        <div class="max-w-7xl mx-auto px-4 pb-3">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">

                {{-- Search --}}
                <div class="sm:col-span-6">
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="search"
                            placeholder="{{ __('app.inv.search_placeholder') }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-400 focus:ring-indigo-200 pr-10"
                        />

                        @if(filled($search))
                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="absolute right-2 top-1/2 -translate-y-1/2 z-10
                                       w-8 h-8 inline-flex items-center justify-center
                                       rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                                aria-label="{{ __('app.trips.clear_search') }}"
                                title="{{ __('app.trips.clear_search') }}"
                            >
                                ✕
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="sm:col-span-3">
                    <select
                        wire:model.live="status"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-400 focus:ring-indigo-200"
                    >
                        <option value="all">{{ __('app.inv.status_all') }}</option>
                        <option value="paid">{{ __('app.inv.status_paid') }}</option>
                        <option value="partial">{{ __('app.inv.status_partial') }}</option>
                        <option value="unpaid">{{ __('app.inv.status_unpaid') }}</option>
                    </select>
                </div>

                {{-- Per page --}}
                <div class="sm:col-span-3">
                    <select
                        wire:model.live="perPage"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-400 focus:ring-indigo-200"
                    >
                        <option value="10">{{ __('app.inv.per_page_10') }}</option>
                        <option value="20">{{ __('app.inv.per_page_20') }}</option>
                        <option value="50">{{ __('app.inv.per_page_50') }}</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-4">

        @if (session('success'))
            <div class="mb-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- ✅ DESKTOP TABLE --}}
        <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('app.inv.col_status') }}</th>

                            <th class="px-4 py-3 text-left font-semibold">
                                <button wire:click="sort('invoice_no')" class="hover:underline">
                                    {{ __('app.inv.col_invoice') }}
                                </button>
                            </th>

                            <th class="px-4 py-3 text-left font-semibold">{{ __('app.inv.col_payer') }}</th>

                            <th class="px-4 py-3 text-right font-semibold">
                                <button wire:click="sort('total')" class="hover:underline">
                                    {{ __('app.inv.col_total') }}
                                </button>
                            </th>

                            <th class="px-4 py-3 text-right font-semibold">{{ __('app.inv.col_paid') }}</th>

                            <th class="px-4 py-3 text-left font-semibold">
                                <button wire:click="sort('issued_at')" class="hover:underline">
                                    {{ __('app.inv.col_issued') }}
                                </button>
                            </th>

                            <th class="px-4 py-3 text-left font-semibold">
                                <button wire:click="sort('last_paid_at')" class="hover:underline">
                                    {{ __('app.inv.col_paid_date') }}
                                </button>
                            </th>

                            <th class="px-4 py-3 text-right font-semibold">
                                {{ __('app.inv.col_actions') }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($rows as $inv)
                            @php
                                $total = (float) $inv->total;
                                $paid  = (float) ($inv->paid_total ?? 0);
                                $currency = $inv->currency ?? 'EUR';

                                if ($paid >= $total && $total > 0) {
                                    $statusText = __('app.inv.status_badge_paid');
                                    $badge = 'bg-green-100 text-green-700 border-green-200';
                                } elseif ($paid > 0 && $paid < $total) {
                                    $statusText = __('app.inv.status_badge_partial');
                                    $badge = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                } else {
                                    $statusText = __('app.inv.status_badge_unpaid');
                                    $badge = 'bg-red-100 text-red-700 border-red-200';
                                }

                                $issuedAt = $inv->issued_at ? \Carbon\Carbon::parse($inv->issued_at)->format('d.m.Y') : '—';

                                $dueCarbon = $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->startOfDay() : null;
                                $today     = \Carbon\Carbon::now()->startOfDay();
                                $isOverdue = $dueCarbon && $paid < $total && $today->gt($dueCarbon);
                                $overdueDays = $isOverdue ? $dueCarbon->diffInDays($today) : null;
                                $dueDate  = $dueCarbon ? $dueCarbon->format('d.m.Y') : '—';

                                $paidAt   = $inv->last_paid_at ? \Carbon\Carbon::parse($inv->last_paid_at)->format('d.m.Y') : '—';

                                $payerName = $inv->payer?->company_name ?? '—';
                                $payerReg  = $inv->payer?->reg_nr ? (' • ' . $inv->payer->reg_nr) : '';
                            @endphp

                            <tr class="hover:bg-gray-50 @if($isOverdue) bg-red-50/60 @endif">
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                        {{ $statusText }}
                                    </span>

                                    @if($isOverdue && $overdueDays !== null)
                                        @php
                                            $unpaid = max($total - $paid, 0);
                                        @endphp
                                        <div class="mt-1 text-[11px] text-red-700 space-y-0.5">
                                            <div>{{ __('app.inv.overdue_days', ['days' => $overdueDays]) }}</div>
                                            <div>{{ __('app.inv.unpaid_amount', ['amount' => number_format($unpaid, 2, '.', ' '), 'currency' => $currency]) }}</div>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900">
                                            {{ $inv->invoice_no ?? '—' }}
                                        </span>

                                        @if(!empty($inv->pdf_file))
                                            <a
                                                href="{{ route('invoices.open', $inv->id) }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="shrink-0 px-3 py-2 bg-amber-200 text-amber-900 rounded-lg font-semibold
                                                       hover:bg-amber-300 transition"
                                            >
                                                👁 {{ __('app.client.show.open_pdf') }}
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-gray-800">
                                    {{ $payerName }}<span class="text-gray-500">{{ $payerReg }}</span>
                                </td>

                                <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                    {{ number_format($total, 2, '.', ' ') }} {{ $currency }}
                                </td>

                                <td class="px-4 py-3 text-right text-gray-800">
                                    {{ number_format($paid, 2, '.', ' ') }} {{ $currency }}
                                </td>

                                <td class="px-4 py-3 text-gray-800">
                                    <div>{{ $issuedAt }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ __('app.inv.due') }} {{ $dueDate }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-gray-800 align-top">
                                    @php
                                        $payments = $inv->payments->sortBy('paid_at');
                                    @endphp

                                    @if($payments->isEmpty())
                                        <div>{{ $paidAt }}</div>
                                    @else
                                        <div class="space-y-0.5">
                                            @foreach($payments as $p)
                                                @php
                                                    $pDate = $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->format('d.m.Y') : '—';
                                                    $pAmount = (float) $p->amount;
                                                @endphp
                                                <div class="text-xs text-gray-800">
                                                    {{ $pDate }} — {{ number_format($pAmount, 2, '.', ' ') }} {{ $p->currency ?? $currency }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                {{-- Actions / Add payment --}}
                                <td class="px-4 py-3 text-right align-top">
                                    @if($paymentInvoiceId === $inv->id)
                                        <form wire:submit.prevent="savePayment" class="space-y-1 inline-block text-left">
                                            <div>
                                                <input
                                                    type="date"
                                                    wire:model.blur="payment_date"
                                                    class="w-40 rounded-lg border-gray-300 text-xs"
                                                >
                                                @error('payment_date')
                                                    <div class="mt-0.5 text-[11px] text-red-600">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    wire:model.blur="payment_amount"
                                                    class="w-40 rounded-lg border-gray-300 text-xs"
                                                    placeholder="{{ __('app.inv.payment.placeholder') }}"
                                                >
                                                @error('payment_amount')
                                                    <div class="mt-0.5 text-[11px] text-red-600">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="flex justify-end gap-1 pt-1">
                                                <button
                                                    type="submit"
                                                    class="px-2 py-1 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700"
                                                >
                                                    {{ __('app.inv.payment.save') }}
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="cancelAddPayment"
                                                    class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200"
                                                >
                                                    {{ __('app.inv.payment.cancel') }}
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="startAddPayment({{ $inv->id }})"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700"
                                        >
                                            {{ __('app.inv.payment.add') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                    {{ __('app.inv.no_invoices') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                {{ $rows->links() }}
            </div>
        </div>

        {{-- ✅ MOBILE / PWA CARDS --}}
        <div class="md:hidden space-y-3">
            @forelse($rows as $inv)
                @php
                    $total = (float) $inv->total;
                    $paid  = (float) ($inv->paid_total ?? 0);
                    $currency = $inv->currency ?? 'EUR';

                    if ($paid >= $total && $total > 0) {
                        $statusText = __('app.inv.status_badge_paid');
                        $badge = 'bg-green-100 text-green-700 border-green-200';
                    } elseif ($paid > 0 && $paid < $total) {
                        $statusText = __('app.inv.status_badge_partial');
                        $badge = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    } else {
                        $statusText = __('app.inv.status_badge_unpaid');
                        $badge = 'bg-red-100 text-red-700 border-red-200';
                    }

                    $issuedAt = $inv->issued_at ? \Carbon\Carbon::parse($inv->issued_at)->format('d.m.Y') : '—';

                    $dueCarbon = $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->startOfDay() : null;
                    $today     = \Carbon\Carbon::now()->startOfDay();
                    $isOverdue = $dueCarbon && $paid < $total && $today->gt($dueCarbon);
                    $overdueDays = $isOverdue ? $dueCarbon->diffInDays($today) : null;
                    $dueDate  = $dueCarbon ? $dueCarbon->format('d.m.Y') : '—';

                    $paidAt   = $inv->last_paid_at ? \Carbon\Carbon::parse($inv->last_paid_at)->format('d.m.Y') : '—';

                    $payerName = $inv->payer?->company_name ?? '—';
                    $payerReg  = $inv->payer?->reg_nr ? $inv->payer->reg_nr : null;
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border {{ $isOverdue ? 'border-red-300' : 'border-gray-200' }} p-4 @if($isOverdue) bg-red-50/60 @endif">
                    {{-- top row --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                    {{ $statusText }}
                                </span>
                                <span class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $inv->invoice_no ?? '—' }}
                                </span>
                            </div>

                            <div class="mt-1 text-xs text-gray-700 truncate">
                                {{ $payerName }}
                                @if($payerReg)
                                    <span class="text-gray-400">• {{ $payerReg }}</span>
                                @endif
                            </div>

                            @if($isOverdue && $overdueDays !== null)
                                @php
                                    $unpaid = max($total - $paid, 0);
                                @endphp
                                <div class="mt-1 text-[11px] text-red-700 space-y-0.5">
                                    <div>{{ __('app.inv.overdue_days', ['days' => $overdueDays]) }}</div>
                                    <div>{{ __('app.inv.unpaid_amount', ['amount' => number_format($unpaid, 2, '.', ' '), 'currency' => $currency]) }}</div>
                                </div>
                            @endif
                        </div>

                        @if(!empty($inv->pdf_file))
                            <a
                                href="{{ route('invoices.open', $inv->id) }}"
                                target="_blank"
                                rel="noopener"
                                class="shrink-0 inline-flex items-center justify-center px-3 py-2 rounded-xl
                                       bg-amber-200 text-amber-900 font-semibold text-sm hover:bg-amber-300 transition"
                            >
                                👁 Open
                            </a>
                        @endif
                    </div>

                    {{-- amounts --}}
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                            <div class="text-gray-500">{{ __('app.inv.col_total') }}</div>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ number_format($total, 2, '.', ' ') }} {{ $currency }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                            <div class="text-gray-500">{{ __('app.inv.col_paid') }}</div>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ number_format($paid, 2, '.', ' ') }} {{ $currency }}
                            </div>
                        </div>
                    </div>

                    {{-- dates --}}
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-700">
                        <div class="flex flex-col rounded-xl bg-gray-50 border border-gray-200 p-2">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">{{ __('app.inv.col_issued') }}</span>
                                <span class="font-semibold text-gray-900">{{ $issuedAt }}</span>
                            </div>
                            <div class="mt-1 text-[11px] text-gray-600">
                                {{ __('app.inv.due') }} {{ $dueDate }}
                            </div>
                        </div>

                        <div class="flex flex-col rounded-xl bg-gray-50 border border-gray-200 p-2">
                            <span class="text-gray-500 mb-1">{{ __('app.inv.col_paid_date') }}</span>

                            @php
                                $payments = $inv->payments->sortBy('paid_at');
                            @endphp

                            @if($payments->isEmpty())
                                <span class="text-[11px] text-gray-500">No payments (last: {{ $paidAt }})</span>
                            @else
                                <div class="space-y-0.5">
                                    @foreach($payments as $p)
                                        @php
                                            $pDate = $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->format('d.m.Y') : '—';
                                            $pAmount = (float) $p->amount;
                                        @endphp
                                        <div class="flex items-center justify-between text-[11px] text-gray-800">
                                            <span>{{ $pDate }}</span>
                                            <span class="font-semibold">
                                                {{ number_format($pAmount, 2, '.', ' ') }} {{ $p->currency ?? $currency }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Add payment (mobile) --}}
                    <div class="mt-3 pt-2 border-t border-dashed border-gray-200">
                        @if($paymentInvoiceId === $inv->id)
                            <form wire:submit.prevent="savePayment" class="space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] text-gray-600 mb-1">Date</label>
                                        <input
                                            type="date"
                                            wire:model.blur="payment_date"
                                            class="w-full rounded-lg border-gray-300 text-xs"
                                        >
                                        @error('payment_date')
                                            <div class="mt-0.5 text-[11px] text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-600 mb-1">Amount</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model.blur="payment_amount"
                                            class="w-full rounded-lg border-gray-300 text-xs"
                                            placeholder="0.00"
                                        >
                                        @error('payment_amount')
                                            <div class="mt-0.5 text-[11px] text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        wire:click="cancelAddPayment"
                                        class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold"
                                    >
                                        {{ __('app.inv.payment.cancel') }}
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-semibold"
                                    >
                                        Save
                                    </button>
                                </div>
                            </form>
                        @else
                            <button
                                type="button"
                                wire:click="startAddPayment({{ $inv->id }})"
                                        class="w-full mt-1 inline-flex items-center justify-center px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700"
                                    >
                                        {{ __('app.inv.payment.add') }}
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center text-gray-500">
                    {{ __('app.inv.no_invoices') }}
                </div>
            @endforelse

            <div class="pt-2">
                {{ $rows->links() }}
            </div>
        </div>

    </div>
</div>
