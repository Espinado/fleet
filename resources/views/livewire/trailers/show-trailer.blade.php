<div class="p-8 bg-gray-100 min-h-screen flex justify-center">
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
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl p-8">

        {{-- Augšējā darbību josla --}}
        <div class="flex justify-between items-start mb-10 gap-4">
            <h1 class="text-4xl font-extrabold text-gray-800">
                {{ $trailer->brand }} {{ $trailer->model }}
                <span class="text-gray-500 text-lg">({{ $trailer->plate }})</span>
            </h1>

            <div class="flex gap-3 mt-4 md:mt-0">
                <a href="{{ route('trailers.edit', $trailer->id) }}"
                   class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-md">
                    ✏️ {{ __('app.trailer.show.edit') }}
                </a>
            </div>
        </div>

        {{-- Pamatinformācija --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-inner space-y-3">
                <p><strong>{{ __('app.trailer.show.year') }}:</strong> {{ $trailer->year }}</p>
                <p><strong>{{ __('app.trailer.show.status') }}:</strong>
                    {{ $trailer->status ? '✅ ' . __('app.trailers.status_active') : '❌ ' . __('app.trailers.status_inactive') }}
                </p>
                <p><strong>{{ __('app.trailers.col_active') }}:</strong>
                    {{ $trailer->is_active ? '✅ ' . __('app.trailer.show.yes') : '❌ ' . __('app.trailer.show.no') }}
                </p>
                <p><strong>VIN:</strong> {{ $trailer->vin }}</p>
                <p>
                    <strong>{{ __('app.trailer.show.type') }}:</strong>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold
                                 bg-white border border-gray-200 shadow-sm">
                        <span class="text-base">{{ $trailer->type_icon ?? '📦' }}</span>
                        <span>{{ $trailer->type_label ?? '—' }}</span>
                        @if($trailer->type_key)
                            <span class="text-xs font-medium text-gray-500">({{ $trailer->type_key }})</span>
                        @endif
                    </span>
                </p>
                 <p><strong>{{ __('app.trailer.show.company') }}:</strong>  {{ $trailer->company?->name ?? '—' }}</p>
            </div>

            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-inner space-y-3">
                <p><strong>{{ __('app.trailer.show.ins_company') }}:</strong> {{ $trailer->insurance_company }}</p>
                <p><strong>{{ __('app.trailer.show.ins_number') }}:</strong> {{ $trailer->insurance_number }}</p>
                <p><strong>{{ __('app.trailer.show.ins_valid') }}:</strong> {{ $fmt($trailer->insurance_issued) }} → {{ $fmt($trailer->insurance_expired) }}</p>
            </div>
        </div>

        {{-- Tehniskā apskate --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">{{ __('app.trailer.show.inspection') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <p><strong>{{ __('app.trailer.show.issued') }}:</strong> {{  $fmt($trailer->inspection_issued) }}</p>
                <p><strong>{{ __('app.trailer.show.expires') }}:</strong> {{ $fmt($trailer->inspection_expired) }}</p>
            </div>
        </div>

        {{-- Tehniskā pase --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">{{ __('app.trailer.show.tech_passport') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <p><strong>{{ __('app.trailer.show.tech_number') }}:</strong> {{ $trailer->tech_passport_nr ?? '-' }}</p>
                <p><strong>{{ __('app.trailer.show.issued') }}:</strong> {{  $fmt($trailer->tech_passport_issued ?? null) }}</p>
                <p><strong>{{ __('app.trailer.show.expires') }}:</strong> {{ $fmt($trailer->tech_passport_expired ?? null) }}</p>
            </div>
        </div>

        {{-- Dokumenti / foto --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">{{ __('app.trailer.show.docs') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-4">
                @php $photos = [$trailer->tech_passport_photo]; @endphp
                @foreach($photos as $photo)
                    <div class="border rounded-2xl p-2 flex items-center justify-center h-56 bg-white shadow-sm">
                        @if($photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Tech Passport Photo"
                                 class="h-full object-contain rounded-xl">
                        @else
                            <span class="text-gray-400 text-sm">{{ __('app.trailer.show.no_image') }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

       <div class="flex justify-between items-center mt-6">
    <a href="{{ route('trailers.index') }}"
       class="px-6 py-2 bg-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-300 transition shadow-md">
        ← {{ __('app.trailer.show.back') }}
    </a>
</div>

    </div>

</div>
