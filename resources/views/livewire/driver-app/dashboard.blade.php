{{-- resources/views/livewire/driver-app/dashboard.blade.php --}}

<div class="space-y-6">

    {{-- Sveiciens --}}
    <div class="text-2xl font-bold">
        👋 {{ __('app.driver.dashboard.hello', ['name' => $driver->first_name]) }}
    </div>

    {{-- Informācija par vadītāju --}}
    <div class="bg-white p-4 rounded-xl shadow space-y-2">
        <div class="flex items-center gap-4">

            <div class="w-16 h-16 rounded-full bg-gray-200 overflow-hidden shrink-0">
                @if($driver->photo)
                    <img src="{{ Storage::url($driver->photo) }}" class="w-full h-full object-cover" alt="Driver photo">
                @else
                    <div class="flex items-center justify-center h-full text-gray-500">
                        👤
                    </div>
                @endif
            </div>

            <div class="text-gray-700">
                <div class="font-semibold text-lg">
                    {{ $driver->first_name }} {{ $driver->last_name }}
                </div>
                <div class="text-sm">📞 {{ $driver->phone }}</div>
                <div class="text-sm">✉️ {{ $driver->email }}</div>
            </div>

        </div>
    </div>

    {{-- Dokumenti --}}
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="font-bold text-lg mb-3">📄 {{ __('app.driver.dashboard.documents') }}</h2>

        <ul class="space-y-2 text-gray-700 text-sm">
            <li>
                {{ __('app.driver.dashboard.license') }}:
                <span class="font-medium">{{ $driver->license_number }}</span>
                ({{ __('app.driver.dashboard.license_to', ['date' => $driver->license_end]) }})
            </li>

            <li>
                Code95:
                <span class="font-medium">{{ $driver->code95_end }}</span>
            </li>

            <li>
                {{ __('app.driver.dashboard.medical') }}: {{ $driver->medical_expired }}
            </li>
        </ul>
    </div>

    {{-- Aktīvais reiss --}}
    @if($trip)
        <div class="bg-white p-4 rounded-xl shadow space-y-3">

            <div class="flex items-start justify-between gap-3">
                <h2 class="text-lg font-bold">
                    🚛 {{ __('app.driver.dashboard.active_trip', ['id' => $trip->id]) }}
                </h2>

                {{-- Бейдж статуса --}}
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $trip->status->color() }}">
                    {{ $trip->status->label() }}
                </span>
            </div>

            <p class="text-gray-700">
                {{ __('app.driver.dashboard.truck') }}: <strong>{{ $trip->truck?->plate ?? '—' }}</strong>
            </p>

            {{-- Ja visi soļi pabeigti, bet vēl nav atgriezies garāžā --}}
            @if($trip->status->value === 'awaiting_garage')
                <div class="p-3 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm">
                    ✅ {{ __('app.driver.dashboard.awaiting') }}
                </div>
            @endif

            {{-- Garāža: izbraukšana / atgriešanās --}}
            <div class="pt-2 space-y-2">

                @if($garageError)
                    <div class="p-3 rounded-xl bg-red-100 text-red-800 text-sm">
                        {{ $garageError }}
                    </div>
                @endif

                @if($garageSuccess)
                    <div class="p-3 rounded-xl bg-green-100 text-green-800 text-sm">
                        {{ $garageSuccess }}
                    </div>
                @endif

                {{-- IZBRAUKŠANA --}}
                <button
                    type="button"
                    wire:click="departFromGarage"
                    wire:target="departFromGarage"
                    wire:loading.attr="disabled"
                    {{ $canDepart ? '' : 'disabled' }}
                    class="w-full flex items-center justify-center gap-2
                           bg-emerald-600 hover:bg-emerald-700
                           text-white py-3 rounded-xl font-semibold
                           disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="departFromGarage">
                        🚛 <span class="ml-1">{{ __('app.driver.dashboard.depart') }}</span>
                    </span>

                    <span wire:loading wire:target="departFromGarage">
                        ⏳ {{ __('app.driver.dashboard.open_form') }}
                    </span>
                </button>

                {{-- ATGRIEŠANĀS --}}
                <button
                    type="button"
                    wire:click="backToGarage"
                    wire:target="backToGarage"
                    wire:loading.attr="disabled"
                    {{ $canReturn ? '' : 'disabled' }}
                    class="w-full flex items-center justify-center gap-2
                           bg-blue-600 hover:bg-blue-700
                           text-white py-3 rounded-xl font-semibold
                           disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="backToGarage">
                        🏁 <span class="ml-1">{{ __('app.driver.dashboard.return') }}</span>
                    </span>

                    <span wire:loading wire:target="backToGarage">
                        ⏳ {{ __('app.driver.dashboard.open_form') }}
                    </span>
                </button>

                {{-- Stāvokļa indikators --}}
                <div class="text-xs text-gray-500 flex items-center justify-between">
                    <span>
                        {{ __('app.driver.dashboard.shift') }}:
                        <span class="font-medium">
                            {{ $trip->vehicle_run_id ? __('app.driver.dashboard.shift_open') : __('app.driver.dashboard.shift_closed') }}
                        </span>
                    </span>
                    <span>
                        {{ $trip->vehicle_run_id ? '🚚 '. __('app.driver.dashboard.state_road') : '🏠 '. __('app.driver.dashboard.state_garage') }}
                    </span>
                </div>

            </div>

            <a
                href="{{ route('driver.trip', $trip) }}"
                class="block text-center bg-blue-600 hover:bg-blue-700 transition
                       text-white py-2 rounded-xl font-medium mt-3"
            >
                {{ __('app.driver.dashboard.open_details') }}
            </a>

        </div>
    @else
        <div class="bg-yellow-100 border border-yellow-300 rounded-xl p-4">
            {{ __('app.driver.dashboard.no_active') }}
        </div>
    @endif


    {{-- ✅ Manual odometer modal (Dashboard only) --}}
    @if(!empty($showManualOdo) && $showManualOdo)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40"></div>

            <div class="relative bg-white w-[92%] max-w-md rounded-2xl shadow-xl p-4 space-y-4">

                <div class="flex items-start justify-between gap-3">
                    <div class="text-lg font-bold">
                        @if(($manualOdoMode ?? 'departure') === 'departure')
                            🚛 Odometra ievade (izbraukšana)
                        @else
                            🏁 Odometra ievade (atgriešanās)
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="cancelManualOdo"
                        class="text-gray-500 text-xl leading-none"
                        aria-label="Close"
                    >
                        ✕
                    </button>
                </div>

                <div class="text-sm text-gray-600">
                    Ievadiet odometra rādījumu (km).
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-700">Odometrs (km)</label>
                    <input
                        type="number"
                        inputmode="numeric"
                        step="1"
                        wire:model.defer="manualOdoKm"
                        class="w-full border-gray-300 rounded-xl text-base p-3 mt-1"
                        placeholder="piem.: 123456"
                    >

                    @error('manualOdoKm')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="cancelManualOdo"
                        class="w-1/2 bg-gray-100 hover:bg-gray-200 text-gray-900 py-3 rounded-xl font-semibold"
                    >
                        Atcelt
                    </button>

                    <button
                        type="button"
                        wire:click="saveManualOdo"
                        wire:target="saveManualOdo"
                        wire:loading.attr="disabled"
                        class="w-1/2 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold
                               disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="saveManualOdo">💾 Saglabāt</span>
                        <span wire:loading wire:target="saveManualOdo">⏳ Saglabā...</span>
                    </button>
                </div>

                <div class="text-[11px] text-gray-500">
                    Piezīme: odometrs tiek saglabāts manuāli (Mapon/CAN pagaidām netiek izmantots).
                </div>
            </div>
        </div>
    @endif

</div>
