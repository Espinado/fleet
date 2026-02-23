<div class="p-6 bg-white rounded-2xl shadow-lg max-w-6xl mx-auto mt-10 relative">
@php
    use Carbon\Carbon;

    $fmt = function ($value) {
        if (blank($value)) return '-';
        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp
    {{-- Верхняя панель действий --}}
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            {{ $truck->brand }} {{ $truck->model }} <span class="text-gray-500">({{ $truck->plate }})</span>
        </h1>

        <div class="flex items-center gap-4">
            {{-- Кнопка редактирования --}}
            <a href="{{ route('trucks.edit', $truck->id) }}"
               class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                ✏️ Edit
            </a>

            {{-- Кнопка удаления --}}

        </div>
    </div>

    {{-- Основная информация --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="space-y-2">
            <p><strong>Year:</strong> {{ $truck->year }}</p>
            <p><strong>Status:</strong> {{ $truck->status ? '✅ Active' : '❌ Inactive' }}</p>
            <p><strong>Active:</strong> {{ $truck->is_active ? '✅ Yes' : '❌ No' }}</p>
            <p><strong>VIN:</strong> {{ $truck->vin }}</p>
            <p><strong>Company:</strong> {{config('companies')[$truck->company]['name']}}</p>
        </div>

        <div class="space-y-2">
            <p><strong>Insurance Company:</strong> {{ $truck->insurance_company }}</p>
            <p><strong>Insurance #:</strong> {{ $truck->insurance_number }}</p>
            <p><strong>Valid:</strong> {{  $fmt($truck->insurance_issued) }} → {{ $fmt($truck->insurance_expired)    }}</p>
        </div>
    </div>
   {{-- MAPON --}}
<div class="mb-8">
    <div class="flex items-center justify-between border-b pb-1 mb-3">
        <h2 class="text-xl font-semibold">Mapon</h2>

        <button
            type="button"
            wire:click="refreshMaponData"
            wire:loading.attr="disabled"
            class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-gray-100 hover:bg-gray-200
                   text-gray-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="refreshMaponData">🔄 Refresh</span>
            <span wire:loading wire:target="refreshMaponData">⏳ Refreshing...</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Unit ID --}}
        <div class="bg-gray-50 border rounded-xl p-4">
            <p class="text-sm text-gray-500 mb-1">Unit ID</p>
            <p class="text-base font-semibold text-gray-800">
                {{ $truck->mapon_unit_id ?? '—' }}
            </p>
        </div>

      {{-- Odometer (CAN → mileage) --}}
<div class="bg-gray-50 border rounded-xl p-4">
    <p class="text-sm text-gray-500 mb-1">Odometer</p>

   @if($maponError && $maponCanMileageKm === null)
    <p class="text-sm font-semibold text-red-700">{{ $maponError }}</p>
@else
        <p class="text-base font-semibold text-gray-800">
            {{ $maponCanMileageKm !== null ? number_format($maponCanMileageKm, 0, '.', ' ') . ' km' : '—' }}
        </p>

        @php
            $at = $maponCanAt ? \Carbon\Carbon::parse($maponCanAt) : null;
            $daysAgo = $at ? $at->diffInDays(now()) : null;
            $minutesAgo = $at ? $at->diffInMinutes(now()) : null;

            $staleDays = (int) config('mapon.can_stale_days', 2);
            $staleMinutes = (int) config('mapon.can_stale_minutes', 30);

            // если источник = CAN (т.е. can.odom.value был) — обычно maponCanAt будет can.odom.gmt
            // если CAN нет — maponCanAt мы ставим в last_update
            $isStale = false;
            if ($minutesAgo !== null && $staleMinutes > 0 && $minutesAgo >= $staleMinutes) $isStale = true;
            if ($daysAgo !== null && $staleDays > 0 && $daysAgo >= $staleDays) $isStale = true;

            // Попытка определить источник по наличию CAN timestamp:
            // если can.odom.gmt пустой, а last_update есть — вероятнее всего mileage.
            $source = ($maponCanAt && $maponLastUpdate && $maponCanAt === $maponLastUpdate) ? 'mileage' : 'CAN';
        @endphp

        {{-- Source badge --}}
        <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                    text-xs font-semibold rounded-full
                    bg-gray-200 text-gray-700">
            📡 Source: {{ $source }}
        </div>

        {{-- Updated / stale badge --}}
        @if($at)
            @if($isStale)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                            text-xs font-semibold rounded-full
                            bg-red-100 text-red-700">
                    🚨 Not updated ({{ $at->diffForHumans() }})
                </div>
            @else
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                            text-xs font-semibold rounded-full
                            bg-green-100 text-green-700">
                    ✅ Updated {{ $at->diffForHumans() }}
                </div>
            @endif

            <p class="text-xs text-gray-400 mt-2">
                Odometer at: {{ $maponCanAt }}
            </p>
        @else
            <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                        text-xs font-semibold rounded-full
                        bg-yellow-100 text-yellow-800">
                ⚠️ No timestamp
            </div>
        @endif

        @if($maponLastUpdate)
            <p class="text-xs text-gray-400 mt-1">
                Last update: {{ $maponLastUpdate }}
            </p>
        @endif
    @endif
</div>

        {{-- Unit name --}}
        <div class="bg-gray-50 border rounded-xl p-4">
            <p class="text-sm text-gray-500 mb-1">Unit name</p>
            <p class="text-base font-semibold text-gray-800">
                {{ $maponUnitName ?? '—' }}
            </p>
        </div>
    </div>

    <div wire:loading wire:target="refreshMaponData" class="mt-3 text-sm text-gray-500 animate-pulse">
        Loading Mapon data...
    </div>
</div>


    {{-- Техосмотр --}}
   <div class="mb-8">
    <h2 class="text-xl font-semibold mb-2 border-b pb-1">Inspection</h2>
    <p><strong>Issued:</strong> {{ $fmt($truck->inspection_issued) }}</p>
    <p><strong>Expires:</strong> {{ $fmt($truck->inspection_expired) }}</p>
</div>

    {{-- License --}}
<div class="bg-gray-50 border rounded-xl p-4">
    <p class="text-sm text-gray-500 mb-1">Expired</p>
    <p class="text-base font-semibold text-gray-800">
        {{ $truck->license_expired ? $truck->license_expired->format('d.m.Y') : '—' }}
    </p>

    @if($truck->license_expired)
        @if($truck->license_expired->isPast())
            <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                Expired
            </span>
        @elseif($truck->license_expired->diffInDays(now()) <= 30)
            <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                Expires soon
            </span>
        @else
            <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                Valid
            </span>
        @endif
    @endif
</div>


    {{-- Техпаспорт --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-2 border-b pb-1">Technical Passport</h2>
        <p><strong>Number:</strong> {{ $truck->tech_passport_nr ?? '-' }}</p>
       <p>
    <strong>Issued:</strong>
    {{ $fmt($truck->tech_passport_issued) }}
</p>

<p>
    <strong>Expired:</strong>
    {{ $fmt($truck->tech_passport_expired) }}
</p>
    </div>

    {{-- Фото --}}
    <div>
        <h2 class="text-xl font-semibold mb-3 border-b pb-1">Documents</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-3">
            @php $photos = [$truck->tech_passport_photo]; @endphp
            @foreach($photos as $photo)
                <div class="border rounded-lg p-2 flex items-center justify-center h-48 bg-gray-50">
                    @if($photo)
                        <img src="{{ asset('storage/' . $photo) }}" alt="Tech Passport Photo"
                             class="h-full object-contain rounded">
                    @else
                        <span class="text-gray-400 text-sm">No image</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Кнопка "Назад" --}}
    <div class="mt-10 flex justify-between items-center">
        <a href="{{ route('trucks.index') }}"
           class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            ← Back
        </a>
         {{-- Кнопка удаления --}}
        <button type="button"
        wire:click="destroy"
        onclick="confirm('Are you sure you want to delete this driver?') || event.stopImmediatePropagation()"
        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
        🗑 Delete
    </button>
    </div>
</div>


