<div class="space-y-6 pb-24">

    {{-- ====== МАРШРУТ ОДНОЙ СТРОКОЙ ====== --}}
    @php
        $steps = $trip->steps()->orderBy('order')->get();

        $routeLine = $steps->map(function($step) {

            $icon = $step->type === 'loading' ? '📦' : '📤';

            $country = getCountryById($step->country_id) ?? '—';
            $city = $step->city_id
                ? getCityNameByCountryId($step->country_id, $step->city_id)
                : null;

            return $icon . ' ' . ($city ?: $country);
        })->implode(' → ');
    @endphp

   


    {{-- ====== НАЗАД ====== --}}
    <a onclick="history.back()" class="text-blue-600 text-sm cursor-pointer block">
        ← Назад
    </a>


    {{-- ====== ОБЩАЯ ИНФОРМАЦИЯ О РЕЙСЕ ====== --}}
    <div class="bg-white shadow rounded-xl p-5 space-y-2">
        <h1 class="text-xl font-bold flex items-center gap-2">
            🚛 Рейс #{{ $trip->id }}
        </h1>

        <p class="text-gray-700">
            <strong>Машина:</strong> {{ $trip->truck->plate }}
        </p>
         @if($routeLine)
        <div class="bg-blue-50 p-4 rounded-lg shadow text-sm text-blue-900">
            <strong>Маршрут:</strong> {!! $routeLine !!}
        </div>
    @endif

        <p class="text-gray-700">
            <strong>Статус:</strong>
            <span class="px-2 py-1 rounded bg-blue-100 text-blue-700">
                {{ $trip->status }}
            </span>
        </p>
    </div>


    {{-- ====== ШАГИ МАРШРУТА — СТРОГО ПО ORDER ====== --}}
    <div class="space-y-4">

        @foreach($steps as $step)

            <div class="bg-white p-5 shadow rounded-xl space-y-5">

                {{-- Заголовок шага --}}
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    {{ $step->type === 'loading' ? '📦 Погрузка' : '📤 Разгрузка' }}
                </h2>

                {{-- Данные клиента (shipper/consignee/customer) --}}
                @php
                    $cargo = $step->cargo;
                @endphp

                @if($cargo)
                    <div class="space-y-1 text-sm">
                        <p><span class="font-semibold">Отправитель:</span> {{ $cargo->shipper?->company_name ?? '—' }}</p>
                        <p><span class="font-semibold">Получатель:</span> {{ $cargo->consignee?->company_name ?? '—' }}</p>
                        <p><span class="font-semibold">Плательщик:</span> {{ $cargo->customer?->company_name ?? '—' }}</p>
                    </div>
                @endif

                {{-- Локация --}}
                <div class="{{ $step->type === 'loading' ? 'bg-blue-50' : 'bg-green-50' }} p-4 rounded-lg space-y-1">

                    <p class="font-semibold text-gray-800">
                        {{ $step->type === 'loading' ? '⬆ Погрузка' : '⬇ Разгрузка' }}
                    </p>

                    <p><strong>Страна:</strong> {{ getCountryById($step->country_id) ?? '—' }}</p>

                    <p><strong>Город:</strong>
                        @if($step->country_id && $step->city_id)
                            {{ getCityNameByCountryId($step->country_id, $step->city_id) }}
                        @else — @endif
                    </p>

                    <p><strong>Адрес:</strong> {{ $step->address ?? '—' }}</p>
                    <p><strong>Дата:</strong> {{ optional($step->date)->format('d.m.Y') ?? '—' }}</p>
                </div>

            </div>

        @endforeach

    </div>


    {{-- ====== ИСТОРИЯ СТАТУСОВ ====== --}}
    <div class="bg-white p-5 rounded-xl shadow space-y-3">
        <h2 class="text-lg font-bold flex items-center gap-2">
            🕒 История шагов
        </h2>

        @forelse($history as $item)
            <div class="flex justify-between text-sm border-b pb-2">
                <span class="font-medium">{{ $item->status }}</span>
                <span class="text-gray-500">
                    {{ \Carbon\Carbon::parse($item->time)->format('d.m.Y H:i') }}
                </span>
            </div>
        @empty
            <div class="text-gray-400 text-sm">История пуста</div>
        @endforelse
    </div>

</div>
