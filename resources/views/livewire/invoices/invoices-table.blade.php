<div class="min-h-screen bg-gray-100 pb-24">

    {{-- TOP BAR --}}
    <div class="sticky top-0 z-30 bg-white/95 border-b border-gray-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 truncate">💶 Invoices</h1>

            <div class="hidden sm:flex text-sm text-gray-600 items-center">
                <span class="px-3 py-2 rounded-xl bg-gray-200/60">
                    Rows: {{ $rows->total() }}
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
                            placeholder="Search: payer / reg nr / invoice no..."
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-400 focus:ring-indigo-200 pr-10"
                        />

                        @if(filled($search))
                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="absolute right-2 top-1/2 -translate-y-1/2 z-10
                                       w-8 h-8 inline-flex items-center justify-center
                                       rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                                aria-label="Clear search"
                                title="Clear"
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
                        <option value="all">All statuses</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partially paid</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>

                {{-- Per page --}}
                <div class="sm:col-span-3">
                    <select
                        wire:model.live="perPage"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-400 focus:ring-indigo-200"
                    >
                        <option value="10">10 / page</option>
                        <option value="20">20 / page</option>
                        <option value="50">50 / page</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>

                            <th class="px-4 py-3 text-left font-semibold">
                                <button wire:click="sort('invoice_no')" class="hover:underline">
                                    Invoice #
                                </button>
                            </th>

                            <th class="px-4 py-3 text-left font-semibold">Payer</th>

                            <th class="px-4 py-3 text-right font-semibold">
                                <button wire:click="sort('total')" class="hover:underline">
                                    Total
                                </button>
                            </th>

                            <th class="px-4 py-3 text-right font-semibold">Paid</th>

                            <th class="px-4 py-3 text-left font-semibold">
                                <button wire:click="sort('issued_at')" class="hover:underline">
                                    Issued
                                </button>
                            </th>

                            <th class="px-4 py-3 text-left font-semibold">
                                <button wire:click="sort('last_paid_at')" class="hover:underline">
                                    Paid date
                                </button>
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
                                    $statusText = 'Paid';
                                    $badge = 'bg-green-100 text-green-700 border-green-200';
                                } elseif ($paid > 0 && $paid < $total) {
                                    $statusText = 'Partial';
                                    $badge = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                } else {
                                    $statusText = 'Unpaid';
                                    $badge = 'bg-red-100 text-red-700 border-red-200';
                                }

                                $issuedAt = $inv->issued_at ? \Carbon\Carbon::parse($inv->issued_at)->format('d.m.Y') : '—';
                                $paidAt   = $inv->last_paid_at ? \Carbon\Carbon::parse($inv->last_paid_at)->format('d.m.Y') : '—';

                                $payerName = $inv->payer?->company_name ?? '—';
                                $payerReg  = $inv->payer?->reg_nr ? (' • ' . $inv->payer->reg_nr) : '';
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                        {{ $statusText }}
                                    </span>
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
                👁 Open
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
                                    {{ $issuedAt }}
                                </td>

                                <td class="px-4 py-3 text-gray-800">
                                    {{ $paidAt }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                    No invoices found.
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
    </div>

</div>
