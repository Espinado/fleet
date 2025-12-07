<div class="flex flex-col min-h-screen bg-gray-100 px-4 pt-4 pb-24">

    {{-- ============================
         ОБЩАЯ ИНФОРМАЦИЯ
    ============================ --}}
    <div class="bg-white shadow rounded-xl p-4 space-y-2">
        <h2 class="text-lg font-semibold">🚛 Рейс #{{ $trip->id }}</h2>

        <p class="text-sm"><strong>Машина:</strong> {{ $trip->truck?->plate ?? '—' }}</p>

        @php
            $routeLine = $steps->map(fn($s) =>
                ($s->type === 'loading' ? '📦' : '📤').' '.
                (getCityNameByCountryId($s->country_id, $s->city_id)
                    ?? getCountryById($s->country_id))
            )->implode(' → ');
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


    {{-- ============================
         СПИСОК ШАГОВ
    ============================ --}}
    @foreach ($steps as $step)

        <div class="bg-white shadow rounded-xl p-4 space-y-3">

            {{-- Шапка шага --}}
            <h3 class="text-lg font-semibold">
                {{ $step->type === 'loading' ? '📦 Погрузка' : '📤 Разгрузка' }}
            </h3>

            {{-- Локация --}}
            <div class="bg-gray-50 rounded p-3 text-sm space-y-1">
                @php
                    $country = getCountryById($step->country_id);
                    $city = getCityNameByCountryId($step->country_id, $step->city_id);
                @endphp

                <p><strong>📍 Локация:</strong> {{ $city ? "$city, $country" : $country }}</p>
                <p><strong>📍 Адрес:</strong> {{ $step->address }}</p>
                <p><strong>📅 Дата:</strong> {{ optional($step->date)->format('d.m.Y') }}</p>
            </div>

            {{-- Клиенты --}}
            @if($step->cargos->count())
                <div class="text-xs space-y-1">
                    <p><strong>Отправитель:</strong> {{ $step->cargos->first()->shipper?->company_name }}</p>
                    <p><strong>Получатель:</strong> {{ $step->cargos->first()->consignee?->company_name }}</p>
                </div>
            @endif


            {{-- ============================
                 ДОКУМЕНТЫ (как у админа)
            ============================ --}}
            <livewire:driver-app.step-document-uploader 
                :trip="$trip"
                :step="$step"
                :key="'driver-step-'.$step->id"
            />

        </div>

    @endforeach


    {{-- ============================
         ИСТОРИЯ
    ============================ --}}
    <div class="bg-white p-4 rounded-xl shadow space-y-2 mt-4">
        <h3 class="font-semibold text-lg">🕒 История рейса</h3>

        @forelse ($history as $item)
            <div class="border-b py-1 flex justify-between text-sm">
                <span>{{ $item->status }}</span>
                <span class="text-gray-500">
                    {{ \Carbon\Carbon::parse($item->time)->format('d.m.Y H:i') }}
                </span>
            </div>
        @empty
            <p class="text-gray-400 text-sm">Пока пусто…</p>
        @endforelse
    </div>

</div>
