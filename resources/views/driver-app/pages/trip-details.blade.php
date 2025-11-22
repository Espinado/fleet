<div class="flex flex-col min-h-screen bg-gray-100">

    {{-- HEADER --}}
    @include('driver-app.components.topbar', [
        'back' => 1,
        'title' => 'Рейс #' . $trip->id
    ])

    <div class="flex-1 pb-24 px-4 pt-4 space-y-6">

        {{-- ====== ОБЩАЯ ИНФОРМАЦИЯ ====== --}}
        <div class="bg-white shadow rounded-xl p-4 space-y-2">
            <h2 class="text-lg font-semibold">🚛 Рейс #{{ $trip->id }}</h2>

            <p class="text-sm"><strong>Машина:</strong> {{ $trip->truck->plate }}</p>

            @php
                $steps = $trip->steps()->orderBy('order')->get();

                $routeLine = $steps->map(function($s) {
                    return ($s->type === 'loading' ? '📦' : '📤') . ' ' .
                           (getCityNameByCountryId($s->country_id, $s->city_id) ??
                            getCountryById($s->country_id));
                })->implode(' → ');
            @endphp

            <p class="text-xs bg-blue-50 text-blue-700 rounded p-2">
                <strong>Маршрут:</strong> {!! $routeLine !!}
            </p>

            <p class="text-sm">
                <strong>Статус:</strong>
                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                    {{ $trip->status }}
                </span>
            </p>
        </div>



        {{-- ====== ШАГИ ====== --}}
        @foreach($steps as $step)

            @php
                $isLoading = $step->type === 'loading';

                $cargos = $trip->cargos()->where(function($q) use ($step, $isLoading) {
                    if ($isLoading) {
                        $q->where('loading_country_id', $step->country_id)
                          ->where('loading_city_id', $step->city_id)
                          ->where('loading_address', $step->address);
                    } else {
                        $q->where('unloading_country_id', $step->country_id)
                          ->where('unloading_city_id', $step->city_id)
                          ->where('unloading_address', $step->address);
                    }
                })->get();
            @endphp

            <div class="bg-white shadow rounded-xl p-4 space-y-3">

                {{-- Заголовок блока --}}
                <h3 class="text-lg font-semibold">
                    {{ $isLoading ? '📦 Погрузка' : '📤 Разгрузка' }}
                </h3>

                {{-- Краткая инфа о точке --}}
                <div class="bg-gray-50 rounded p-3 text-sm space-y-1">

                    @php
                        $country = getCountryById($step->country_id) ?? '—';
                        $city = $step->city_id
                            ? getCityNameByCountryId($step->country_id, $step->city_id)
                            : null;
                    @endphp

                    <p><strong>📍 Локация:</strong>
                        {{ $city ? "$city, $country" : $country }}
                    </p>

                    <p><strong>📍 Адрес:</strong> {{ $step->address }}</p>

                    <p><strong>📅 Дата:</strong> {{ optional($step->date)->format('d.m.Y') }}</p>
                </div>


                {{-- Клиенты --}}
                @if($cargos->count() > 0)
                    <div class="text-xs space-y-1">
                        <p><strong>Отправитель:</strong> {{ $cargos->first()->shipper?->company_name }}</p>
                        <p><strong>Получатель:</strong> {{ $cargos->first()->consignee?->company_name }}</p>
                    </div>
                @endif



                {{-- ===== КНОПКИ ДЕЙСТВИЙ ===== --}}
                <div class="grid grid-cols-2 gap-3">

                    {{-- Фото ДО --}}
                    <a href="{{ route('driver.documents.upload', [$trip->id, $step->id, 'before']) }}"
                       class="p-3 bg-gray-100 rounded-lg text-center shadow hover:bg-gray-200 transition">
                        <div class="text-2xl mb-1">📷</div>
                        <div class="text-xs font-semibold">Фото ДО</div>
                    </a>

                    {{-- Фото ПОСЛЕ --}}
                    <a href="{{ route('driver.documents.upload', [$trip->id, $step->id, 'after']) }}"
                       class="p-3 bg-gray-100 rounded-lg text-center shadow hover:bg-gray-200 transition">
                        <div class="text-2xl mb-1">📸</div>
                        <div class="text-xs font-semibold">Фото ПОСЛЕ</div>
                    </a>

                    {{-- Документы --}}
                    <a href="{{ route('driver.documents.upload', [$trip->id, $step->id, 'docs']) }}"
                       class="p-3 bg-gray-100 rounded-lg text-center shadow hover:bg-gray-200 transition col-span-2">
                        <div class="text-xl">📄</div>
                        <div class="text-xs font-semibold">Документы</div>
                    </a>

                    {{-- Другое фото --}}
                    <a href="{{ route('driver.documents.upload', [$trip->id, $step->id, 'extra']) }}"
                       class="p-3 bg-gray-100 rounded-lg text-center shadow hover:bg-gray-200 transition col-span-2">
                        <div class="text-xl">➕</div>
                        <div class="text-xs font-semibold">Доп. фото</div>
                    </a>
                </div>



                {{-- ===== ПРЕДПРОСМОТР ФОТО ===== --}}
                <div class="flex gap-2 overflow-x-auto pt-1">

                    @foreach($step->documents as $doc)
                        <a href="{{ route('driver.documents.view', $doc->id) }}">
                            <img src="{{ $doc->file_url }}"
                                 class="w-20 h-20 rounded object-cover shadow" />
                        </a>
                    @endforeach

                </div>

            </div>

        @endforeach




        {{-- ====== ИСТОРИЯ ====== --}}
        <div class="bg-white p-4 rounded-xl shadow space-y-2">
            <h3 class="font-semibold text-lg">🕒 История рейса</h3>

            @forelse($history as $item)
                <div class="border-b py-1 flex justify-between text-sm">
                    <span>{{ $item->status }}</span>
                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($item->time)->format('d.m.Y H:i') }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm">Пока пусто…</p>
            @endforelse
        </div>

    </div>
</div>
