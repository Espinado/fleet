{{-- resources/views/livewire/driver-app/trip-details.blade.php --}}
<div class="flex flex-col min-h-screen bg-gray-100 px-4 pt-4 pb-24 min-w-0">

    {{-- Errors show via global driver toast (layout): "Ошибка! Свяжитесь с администратором" --}}

    {{-- ============================
         TRIP SUMMARY
    ============================ --}}
    <div class="bg-white shadow rounded-xl p-4 space-y-2 min-w-0">
        <h2 class="text-lg font-semibold break-words">
            🚛 {{ __('app.driver.trip_details.trip_title', ['id' => $trip->id]) }}
        </h2>

        <p class="text-sm break-words">
            <strong>{{ __('app.driver.trip_details.truck') }}:</strong> {{ $trip->truck?->plate ?? '—' }}
        </p>

        @php
            $routeLine = $steps->map(fn($s) =>
                ($s->type === 'loading' ? '📦' : '📤') . ' ' .
                (getCityNameByCountryId($s->country_id, $s->city_id) ?? getCountryById($s->country_id))
            )->implode(' → ');
        @endphp

        <p class="text-xs bg-blue-50 text-blue-700 rounded p-2 break-words break-all">
            <strong>{{ __('app.driver.trip_details.route') }}:</strong> {!! $routeLine !!}
        </p>

        <p class="text-sm">
            <strong>{{ __('app.driver.trip_details.status') }}:</strong>
            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                {{ $trip->status_label }}
            </span>
        </p>
    </div>

    {{-- ============================
         GARAGE → GARAGE (INFO ONLY)
         (start/end handled on Dashboard)
    ============================ --}}
    <div wire:key="garage-{{ $trip->id }}" class="bg-white shadow rounded-xl p-4 space-y-3 mt-3 min-w-0">

        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm font-semibold min-w-0 flex-1">
                🚪 {{ __('app.driver.trip_details.garage_to_garage') }}
            </div>

            @php
                // сейчас CAN не используем для гаража, но бейдж оставим как инфо по траку
                $can = (bool)($trip->truck?->can_available);
            @endphp

            <span class="text-[11px] px-2 py-1 rounded-full shrink-0 {{ $can ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800' }}">
                {{ $can ? __('app.driver.trip_details.can_auto_unused') : __('app.driver.trip_details.manual_odo') }}
            </span>
        </div>

            <div class="text-xs text-gray-600 space-y-1 min-w-0 break-words">
            <div><strong>{{ __('app.driver.trip_details.start') }}:</strong> {{ $trip->started_at?->format('d.m.Y H:i') ?? '—' }}</div>
            <div><strong>{{ __('app.driver.trip_details.end') }}:</strong> {{ $trip->ended_at?->format('d.m.Y H:i') ?? '—' }}</div>

            {{-- Odometer snapshot --}}
            <div class="pt-1 space-y-1">
                <div>
                    <strong>{{ __('app.driver.trip_details.odo_start') }}:</strong>
                    {{ $trip->odo_start_km !== null ? number_format($trip->odo_start_km, 0, '.', ' ') . ' km' : '—' }}
                </div>
                <div>
                    <strong>{{ __('app.driver.trip_details.odo_end') }}:</strong>
                    {{ $trip->odo_end_km !== null ? number_format($trip->odo_end_km, 0, '.', ' ') . ' km' : '—' }}
                </div>

                @if($trip->odo_start_km !== null && $trip->odo_end_km !== null)
                    <div class="pt-1">
                        <strong>{{ __('app.driver.trip_details.distance') }}:</strong>
                        {{ number_format(($trip->odo_end_km - $trip->odo_start_km), 0, '.', ' ') }} km
                    </div>
                @endif
            </div>

            <div class="text-[11px] text-gray-500 pt-1 break-words">
                ℹ️ {{ __('app.driver.trip_details.garage_hint') }}
            </div>
        </div>

        {{-- Status hint: «смена открыта» / «В пути» по факту выезда (started_at) и возврата (ended_at) --}}
            @php
                $driverOnRoad = $trip->started_at && !$trip->ended_at;
            @endphp
            <div class="text-xs text-gray-500 flex flex-wrap items-center justify-between gap-1 min-w-0">
                <span class="min-w-0 break-words">
                    {{ __('app.driver.trip_details.shift') }}:
                    <span class="font-medium">
                        {{ $driverOnRoad ? __('app.driver.trip_details.shift_open') : __('app.driver.trip_details.shift_closed') }}
                    </span>
                </span>
                <span class="shrink-0">
                    {{ $driverOnRoad ? '🚚 ' . __('app.driver.trip_details.state_road') : '🏠 ' . __('app.driver.trip_details.state_garage') }}
                </span>
            </div>

    </div>

    {{-- ============================
         STEPS (accordion)
    ============================ --}}
    @php $TS = \App\Enums\TripStepStatus::class; @endphp

@foreach ($steps as $step)
        @php
            $city = getCityNameByCountryId($step->country_id, $step->city_id)
                ?? getCountryById($step->country_id);

            $label = $step->type === 'loading' ? '📦 Погрузка' : '📤 Разгрузка';
            $stepStatus = $step->status;
            $isErrorStep = isset($errorStepId) && (int)$errorStepId === (int)$step->id;
        @endphp

        <div
            wire:key="step-{{ $step->id }}"
            x-data="{ open: false }"
            class="bg-white shadow rounded-xl mb-4 overflow-hidden border min-w-0 {{ $isErrorStep ? 'border-red-500 ring-2 ring-red-200' : '' }}"
        >

            {{-- Header --}}
            <button @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between gap-2 bg-gray-50 min-w-0">
                <div class="flex flex-col text-left min-w-0 flex-1">
                    <span class="text-[15px] font-semibold break-words">
                        {{ $label }}
                        @if($isErrorStep)
                            <span class="ml-2 text-[11px] font-semibold text-red-600">⚠️ {{ __('app.driver.trip_details.step_error') }}</span>
                        @endif
                    </span>
                    <span class="text-xs text-gray-500 break-words">{{ $city }}</span>
                </div>

                <div class="flex items-center shrink-0">
                    <span @class([
                        'text-[11px] px-2 py-1 rounded-full mr-3',
                        'bg-gray-200 text-gray-700'      => $stepStatus === $TS::NOT_STARTED,
                        'bg-blue-200 text-blue-700'      => $stepStatus === $TS::ON_THE_WAY,
                        'bg-yellow-200 text-yellow-800'  => $stepStatus === $TS::ARRIVED,
                        'bg-purple-200 text-purple-800'  => $stepStatus === $TS::PROCESSING,
                        'bg-green-200 text-green-700'    => $stepStatus === $TS::COMPLETED,
                    ])>
                        {{ $stepStatus?->label() ?? 'Nav uzsākts' }}
                    </span>

                    <span class="text-xs text-gray-400" x-text="open ? '▲' : '▼'"></span>
                </div>
            </button>

            {{-- Body --}}
            <div x-show="open" x-collapse class="p-4 space-y-4">

                {{-- Location --}}
                <div class="bg-gray-50 rounded p-3 text-sm space-y-1">
                    <p><strong>📍 {{ __('app.driver.trip_details.location') }}:</strong> {{ $city }}</p>
                    <p><strong>📍 {{ __('app.driver.trip_details.address') }}:</strong> {{ $step->address }}</p>
                    @php
                        $phone1 = trim((string) ($step->contact_phone_1 ?? ''));
                        $phone2 = trim((string) ($step->contact_phone_2 ?? ''));
                        $hasPhones = $phone1 !== '' || $phone2 !== '';
                    @endphp
                    @if($hasPhones)
                        <p class="pt-1">
                            @if($phone1 !== '')
                                <a href="tel:{{ preg_replace('/\s+/', '', $phone1) }}" class="text-blue-600 hover:underline font-medium">📞 {{ $phone1 }}</a>
                            @endif
                            @if($phone2 !== '')
                                @if($phone1 !== '') <span class="text-gray-400 mx-1">|</span> @endif
                                <a href="tel:{{ preg_replace('/\s+/', '', $phone2) }}" class="text-blue-600 hover:underline font-medium">📞 {{ $phone2 }}</a>
                            @endif
                        </p>
                    @endif
                    <p><strong>📅 {{ __('app.driver.trip_details.date') }}:</strong> {{ optional($step->date)->format('d.m.Y') }}</p>
                </div>

                {{-- Clients --}}
                @if($step->cargos->count())
                    <div class="text-xs space-y-1">
                        <p><strong>{{ __('app.driver.trip_details.shipper') }}:</strong> {{ $step->cargos->first()->shipper?->company_name }}</p>
                        <p><strong>{{ __('app.driver.trip_details.consignee') }}:</strong> {{ $step->cargos->first()->consignee?->company_name }}</p>
                    </div>
                @endif

                {{-- Status + actions --}}
                <div class="border-t pt-3 mt-3 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">{{ __('app.driver.trip_details.step_status_label') }}</span>

                        {{-- ✅ FIX: valid @class usage --}}
                        <span @class([
                            'px-2 py-1 rounded-full text-[11px]',
                            'bg-gray-100 text-gray-700'     => $stepStatus === $TS::NOT_STARTED,
                            'bg-blue-100 text-blue-700'     => $stepStatus === $TS::ON_THE_WAY,
                            'bg-yellow-100 text-yellow-700' => $stepStatus === $TS::ARRIVED,
                            'bg-purple-100 text-purple-700' => $stepStatus === $TS::PROCESSING,
                            'bg-green-100 text-green-700'   => $stepStatus === $TS::COMPLETED,
                        ])>
                            {{ $stepStatus?->label() ?? 'Nav uzsākts' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-2">
                        @switch($stepStatus)

                            @case($TS::ON_THE_WAY)
                                <button
                                    wire:click="updateStepStatus({{ $step->id }}, {{ $TS::ARRIVED->value }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 inline-flex justify-center items-center px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold active:scale-95">
                                    📍 {{ __('app.driver.trip_details.btn_im_here') }}
                                </button>
                                @break

                            @case($TS::ARRIVED)
                                <button
                                    wire:click="updateStepStatus({{ $step->id }}, {{ $TS::PROCESSING->value }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 inline-flex justify-center items-center px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold active:scale-95">
                                    ⚙ {{ __('app.driver.trip_details.btn_start_loading') }}
                                </button>
                                @break

                            @case($TS::PROCESSING)
                                <button
                                    wire:click="updateStepStatus({{ $step->id }}, {{ $TS::COMPLETED->value }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 inline-flex justify-center items-center px-3 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold active:scale-95">
                                    ✔ {{ __('app.driver.trip_details.btn_finish_loading') }}
                                </button>
                                @break

                            @case($TS::COMPLETED)
                                <div class="text-xs text-green-600 font-semibold">
                                    ✅ {{ __('app.driver.trip_details.step_completed') }}
                                </div>
                                @break

                            @case($TS::NOT_STARTED)
                            @default
                                <button
                                    wire:click="updateStepStatus({{ $step->id }}, {{ $TS::ON_THE_WAY->value }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 inline-flex justify-center items-center px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold active:scale-95">
                                    🚚 {{ __('app.driver.trip_details.btn_go_to_address') }}
                                </button>
                                @break

                        @endswitch
                    </div>
                </div>

                {{-- Documents --}}
                @php $docCount = $step->stepDocuments->count(); @endphp

                <div x-data="{ openUpload: @js($errors->isNotEmpty()), openList: false }"
                     x-on:step-document-uploaded.window="openUpload = false"
                     class="border-t pt-3 mt-3">
                    <button @click="openUpload = !openUpload"
                            class="w-full flex items-center justify-between px-3 py-2 bg-indigo-50 rounded-lg text-sm font-semibold">
                        📤 {{ __('app.driver.trip_details.add_document') }}
                        <span x-text="openUpload ? '▲' : '▼'" class="text-xs"></span>
                    </button>

                    <div x-show="openUpload" x-collapse class="mt-3">
                        <livewire:driver-app.driver-step-document-uploader
                            :trip="$trip"
                            :step="$step"
                            :key="'driver-upload-'.$step->id"
                        />
                    </div>

                    <button @click="openList = !openList"
                            class="w-full flex items-center justify-between mt-4 px-3 py-2 bg-gray-100 rounded-lg text-sm font-semibold">
                        📁 {{ __('app.driver.trip_details.step_documents') }} <span class="text-blue-600">({{ $docCount }})</span>
                        <span x-text="openList ? '▲' : '▼'" class="text-xs"></span>
                    </button>

                    <div x-show="openList" x-collapse class="mt-3">
                        @foreach ($step->stepDocuments as $doc)
                            @php
                                $typeEnum = $doc->type;
                                $url = asset('storage/'.$doc->file_path);
                                $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                                $isPdf = $ext === 'pdf';
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
                            @endphp

                            <div class="flex items-center gap-3 bg-white rounded-xl p-3 border shadow-sm mb-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-800 truncate">
                                        {{ $typeEnum->label() }}
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $doc->comment ?: '—' }}
                                    </div>
                                </div>

                                <div class="text-[11px] text-gray-400 whitespace-nowrap">
                                    {{ $doc->created_at->format('d.m.Y H:i') }}
                                </div>

                                <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                    @if ($isPdf)
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="font-bold text-red-600 text-sm">PDF</a>
                                    @elseif ($isImage)
                                        <a href="{{ $url }}" target="_blank" rel="noopener">
                                            <img src="{{ $url }}" class="w-14 h-14 object-cover" alt="">
                                        </a>
                                    @else
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-indigo-600 underline text-xs">
                                            {{ __('app.driver.step_docs.open') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    @endforeach

    {{-- ============================
         STEP ODOMETER MODAL
    ============================ --}}
    @if($showStepOdoModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl p-4 w-full max-w-sm space-y-3">
                <h2 class="text-sm font-semibold">
                    ⛽ {{ __('app.driver.trip_details.odo_modal_title') }}
                </h2>
                <p class="text-xs text-gray-600">
                    {{ __('app.driver.trip_details.odo_modal_hint') }}
                </p>

                <div>
                    <input
                        type="number"
                        step="0.1"
                        wire:model.blur="stepOdoKm"
                        class="w-full rounded-lg border-gray-300 text-sm"
                        placeholder="{{ __('app.driver.trip_details.odo_placeholder') }}"
                    >
                    @error('stepOdoKm')
                        <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="cancelStepOdo"
                        wire:loading.attr="disabled"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold"
                    >
                        {{ __('app.driver.trip_details.cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="confirmStepStatusWithOdo"
                        wire:loading.attr="disabled"
                        class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-semibold"
                    >
                        {{ __('app.driver.trip_details.save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================
         DRIVER EXPENSES
    ============================ --}}
    <livewire:driver-app.driver-trip-expenses :trip="$trip" :key="'expenses-'.$trip->id" />

</div>
