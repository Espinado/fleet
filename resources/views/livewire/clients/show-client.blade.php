{{-- resources/views/clients/show.blade.php --}}
<div class="min-h-screen bg-gray-100 pb-24">

    {{-- ✅ TOP BAR (PWA-friendly) --}}
    <div class="sticky top-0 z-30 bg-white/95 border-b border-gray-200 backdrop-blur">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 truncate">
                    🏢 {{ $client->company_name }}
                </h1>
                <div class="text-xs text-gray-500 truncate">
                    {{ $client->reg_nr ? 'Reg: '.$client->reg_nr : '—' }}
                    @if($client->representative)
                        <span class="text-gray-300 px-1">•</span>
                        {{ $client->representative }}
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('clients.edit', $client->id) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow">
                    ✏️ <span class="hidden sm:inline">Edit</span>
                </a>

                <a href="{{ route('clients.index') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-gray-200 text-gray-800 hover:bg-gray-300">
                    ← <span class="hidden sm:inline">Back</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 pt-4 space-y-6">

        {{-- FLASH --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-300 text-green-800 rounded-2xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- =========================
            COMPANY INFO CARD
        ========================== --}}
        <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 space-y-6">

            {{-- Company Info --}}
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-800">Company Info</h2>
                    <span class="text-[11px] text-gray-500">Client #{{ $client->id }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="text-xs text-gray-500 mb-1">Reg. Nr</div>
                        <div class="font-semibold text-gray-900">{{ $client->reg_nr ?? '—' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="text-xs text-gray-500 mb-1">Representative</div>
                        <div class="font-semibold text-gray-900">{{ $client->representative ?? '—' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="text-xs text-gray-500 mb-1">Email</div>
                        <div class="font-semibold text-gray-900 break-words">{{ $client->email ?? '—' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="text-xs text-gray-500 mb-1">Phone</div>
                        <div class="font-semibold text-gray-900">{{ $client->phone ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Addresses --}}
            <div class="border-t pt-5">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-3">Addresses</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    {{-- Legal --}}
                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-800">Legal Address</div>
                            <span class="text-[11px] text-gray-500">jur_*</span>
                        </div>

                        <div class="mt-2 space-y-1 text-gray-700">
                            <div>{{ $client->jur_address ?? '—' }}</div>
                            <div>
                                {{ getCityById($client->jur_city_id ?? null, $client->jur_country_id ?? null) }}
                                {{ $client->jur_post_code ?? '' }}
                            </div>
                            <div>{{ getCountryById($client->jur_country_id ?? null) }}</div>
                        </div>
                    </div>

                    {{-- Physical --}}
                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-800">Physical Address</div>
                            <span class="text-[11px] text-gray-500">fiz_*</span>
                        </div>

                        <div class="mt-2 space-y-1 text-gray-700">
                            <div>{{ $client->fiz_address ?? '—' }}</div>
                            <div>
                                {{ getCityById($client->fiz_city_id ?? null, $client->fiz_country_id ?? null) }}
                                {{ $client->fiz_post_code ?? '' }}
                            </div>
                            <div>{{ getCountryById($client->fiz_country_id ?? null) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank --}}
            <div class="border-t pt-5">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-3">Bank Details</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="text-xs text-gray-500 mb-1">Bank Name</div>
                        <div class="font-semibold text-gray-900">{{ $client->bank_name ?? '—' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-3">
                        <div class="text-xs text-gray-500 mb-1">SWIFT</div>
                        <div class="font-semibold text-gray-900">{{ $client->swift ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- =========================
                ACTIVITY (Invoices / Documents)
            ========================== --}}
            <div class="border-t pt-6">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-800">📚 Activity</h2>
                    <div class="text-[11px] text-gray-500">
                        Invoices & Docs connected to this client
                    </div>
                </div>

                @php
                    use App\Models\TripCargo;
                    use App\Models\Invoice;
                    use Illuminate\Support\Facades\Storage;

                    // --- cargos where client participates in any role ---
                    $clientCargos = TripCargo::query()
                        ->where('customer_id', $client->id)
                        ->orWhere('shipper_id', $client->id)
                        ->orWhere('consignee_id', $client->id)
                        ->with(['trip'])
                        ->orderByDesc('created_at')
                        ->get();

                    // --- invoices for this client (payer_client_id in your Invoice model) ---
                    $clientInvoices = Invoice::query()
                        ->where('payer_client_id', $client->id)
                        ->withSum('payments', 'amount')   // payments_sum_amount
                        ->withMax('payments', 'paid_at')  // payments_max_paid_at (paid date)
                        ->orderByDesc('issued_at')
                        ->get();

                    $hasDocs = $clientCargos->isNotEmpty();
                    $hasInvs = $clientInvoices->isNotEmpty();

                    $roleLabel = function($cargo) use ($client) {
                        $roles = [];
                        if ((int)$cargo->customer_id === (int)$client->id)  $roles[] = 'Customer';
                        if ((int)$cargo->shipper_id === (int)$client->id)   $roles[] = 'Shipper';
                        if ((int)$cargo->consignee_id === (int)$client->id) $roles[] = 'Consignee';
                        return $roles ? implode(' / ', $roles) : '—';
                    };

                    $fileLink = function($path) {
                        if (!$path) return null;

                        $rel = ltrim(str_replace('storage/', '', $path), '/');
                        if (!Storage::disk('public')->exists($rel)) return null;

                        return asset('storage/' . $rel);
                    };
                @endphp

                <div x-data="{ tab: '{{ $hasInvs ? 'invoices' : 'docs' }}' }"
                     class="bg-gray-50 border border-gray-200 rounded-2xl p-3 sm:p-4">

                    {{-- Tabs --}}
                    <div class="flex items-center gap-2 mb-4">
                        <button type="button"
                                @click="tab = 'invoices'"
                                class="px-3 py-2 rounded-xl text-sm font-semibold border transition"
                                :class="tab === 'invoices'
                                    ? 'bg-white border-gray-300 text-gray-900 shadow-sm'
                                    : 'bg-transparent border-transparent text-gray-600 hover:bg-white/60'">
                            💶 Invoices
                            <span class="ml-1 text-xs text-gray-500">({{ $clientInvoices->count() }})</span>
                        </button>

                        <button type="button"
                                @click="tab = 'docs'"
                                class="px-3 py-2 rounded-xl text-sm font-semibold border transition"
                                :class="tab === 'docs'
                                    ? 'bg-white border-gray-300 text-gray-900 shadow-sm'
                                    : 'bg-transparent border-transparent text-gray-600 hover:bg-white/60'">
                            📄 Documents
                            <span class="ml-1 text-xs text-gray-500">({{ $clientCargos->count() }})</span>
                        </button>
                    </div>

                    {{-- =========================
                        TAB: INVOICES
                    ========================== --}}
                    <div x-show="tab === 'invoices'" x-cloak class="space-y-3">
                        @if(!$hasInvs)
                            <div class="text-sm text-gray-500 italic">No invoices for this client.</div>
                        @else
                            <div class="space-y-2">
                                @foreach($clientInvoices as $inv)
                                    @php
                                        $total = (float) ($inv->total ?? 0);
                                        $paid  = (float) ($inv->payments_sum_amount ?? 0);
                                        $currency = $inv->currency ?? 'EUR';
                                        $balance = $total - $paid;

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
                                        $paidAt   = $inv->payments_max_paid_at
                                            ? \Carbon\Carbon::parse($inv->payments_max_paid_at)->format('d.m.Y')
                                            : '—';

                                        $dueAt    = $inv->due_date
                                            ? \Carbon\Carbon::parse($inv->due_date)->format('d.m.Y')
                                            : '—';
                                    @endphp

                                    <div class="bg-white rounded-2xl border border-gray-200 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                                        {{ $statusText }}
                                                    </span>
                                                    <div class="font-semibold text-gray-900 truncate">
                                                        {{ $inv->invoice_no ?? ('#'.$inv->id) }}
                                                    </div>
                                                </div>

                                                <div class="mt-1 text-xs text-gray-600">
                                                    Issued: <span class="font-semibold text-gray-900">{{ $issuedAt }}</span>
                                                    <span class="text-gray-300 px-1">•</span>
                                                    Due: <span class="font-semibold text-gray-900">{{ $dueAt }}</span>
                                                    <span class="text-gray-300 px-1">•</span>
                                                    Paid: <span class="font-semibold text-gray-900">{{ $paidAt }}</span>
                                                </div>
                                            </div>

                                            @if(!empty($inv->pdf_file))
                                                <a href="{{ route('invoices.open', $inv->id) }}"
                                                   target="_blank"
                                                   rel="noopener"
                                                   class="shrink-0 inline-flex items-center justify-center px-3 py-2 rounded-xl
                                                          bg-amber-200 text-amber-900 font-semibold text-sm hover:bg-amber-300 transition">
                                                    👁 Open
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400">no pdf</span>
                                            @endif
                                        </div>

                                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                            <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                                                <div class="text-gray-500">Total</div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ number_format($total, 2, '.', ' ') }} {{ $currency }}
                                                </div>
                                            </div>

                                            <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                                                <div class="text-gray-500">Paid</div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ number_format($paid, 2, '.', ' ') }} {{ $currency }}
                                                </div>
                                            </div>

                                            <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                                                <div class="text-gray-500">Balance</div>
                                                <div class="text-sm font-semibold {{ $balance <= 0 ? 'text-green-700' : 'text-gray-900' }}">
                                                    {{ number_format(max($balance, 0), 2, '.', ' ') }} {{ $currency }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                            @if($inv->trip_id)
                                                <a href="{{ route('trips.show', $inv->trip_id) }}"
                                                   class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold">
                                                    🧭 Trip #{{ $inv->trip_id }}
                                                </a>
                                            @endif

                                            @if($inv->trip_cargo_id)
                                                <span class="px-3 py-1.5 rounded-xl bg-gray-100 text-gray-700 font-semibold">
                                                    📦 Cargo #{{ $inv->trip_cargo_id }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- =========================
                        TAB: DOCUMENTS
                    ========================== --}}
                    <div x-show="tab === 'docs'" x-cloak class="space-y-3">
                        @if(!$hasDocs)
                            <div class="text-sm text-gray-500 italic">No documents found for this client.</div>
                        @else
                            <div class="space-y-2">
                                @foreach($clientCargos as $cargo)
                                    @php
                                        $roles = $roleLabel($cargo);

                                        $cmrUrl   = $fileLink($cargo->cmr_file ?? null);
                                        $orderUrl = $fileLink($cargo->order_file ?? null);
                                        $invUrl   = $fileLink($cargo->inv_file ?? null);

                                        $tripId = $cargo->trip_id;
                                        $hasAny = $cmrUrl || $orderUrl || $invUrl;
                                    @endphp

                                    <div class="bg-white rounded-2xl border border-gray-200 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 truncate">
                                                    Trip
                                                    <a href="{{ route('trips.show', $tripId) }}" class="text-blue-600 hover:underline">
                                                        #{{ $tripId }}
                                                    </a>
                                                    <span class="text-gray-300 px-1">•</span>
                                                    <span class="text-xs text-gray-600">{{ $roles }}</span>
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    Cargo #{{ $cargo->id }}
                                                    <span class="text-gray-300 px-1">•</span>
                                                    {{ \Carbon\Carbon::parse($cargo->created_at)->format('d.m.Y H:i') }}
                                                </div>
                                            </div>

                                            @if(!$hasAny)
                                                <span class="text-xs text-gray-400">no files</span>
                                            @endif
                                        </div>

                                        {{-- files --}}
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @if($cmrUrl)
                                                <a href="{{ $cmrUrl }}" target="_blank"
                                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold
                                                          bg-blue-600 text-white hover:bg-blue-700 transition">
                                                    📄 CMR
                                                    @if($cargo->cmr_created_at)
                                                        <span class="text-[10px] opacity-80">
                                                            {{ \Carbon\Carbon::parse($cargo->cmr_created_at)->format('d.m.Y') }}
                                                        </span>
                                                    @endif
                                                </a>
                                            @endif

                                            @if($orderUrl)
                                                <a href="{{ $orderUrl }}" target="_blank"
                                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold
                                                          bg-gray-900 text-white hover:bg-black transition">
                                                    🧾 Order
                                                    @if($cargo->order_created_at)
                                                        <span class="text-[10px] opacity-80">
                                                            {{ \Carbon\Carbon::parse($cargo->order_created_at)->format('d.m.Y') }}
                                                        </span>
                                                    @endif
                                                </a>
                                            @endif

                                            @if($invUrl)
                                                <a href="{{ $invUrl }}" target="_blank"
                                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold
                                                          bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                                    💶 Invoice
                                                    @if($cargo->inv_created_at)
                                                        <span class="text-[10px] opacity-80">
                                                            {{ \Carbon\Carbon::parse($cargo->inv_created_at)->format('d.m.Y') }}
                                                        </span>
                                                    @endif
                                                </a>
                                            @endif
                                        </div>

                                        {{-- meta --}}
                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                            <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                                                <div class="text-gray-500">CMR №</div>
                                                <div class="font-semibold text-gray-900">{{ $cargo->cmr_nr ?? '—' }}</div>
                                            </div>

                                            <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                                                <div class="text-gray-500">Order №</div>
                                                <div class="font-semibold text-gray-900">{{ $cargo->order_nr ?? '—' }}</div>
                                            </div>

                                            <div class="rounded-xl bg-gray-50 border border-gray-200 p-2">
                                                <div class="text-gray-500">Invoice №</div>
                                                <div class="font-semibold text-gray-900">{{ $cargo->inv_nr ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </section>

    </div>

    {{-- ✅ BOTTOM BAR (optional, to keep actions reachable in PWA) --}}
    <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 border-t border-gray-200 backdrop-blur">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="text-xs text-gray-500 truncate">
                Client: {{ $client->company_name }}
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('clients.edit', $client->id) }}"
                   class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow">
                    ✏️ Edit
                </a>
                <a href="{{ route('clients.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold bg-gray-200 text-gray-800 hover:bg-gray-300">
                    ← Back
                </a>
            </div>
        </div>
    </div>

</div>
