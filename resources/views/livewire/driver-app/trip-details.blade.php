<div class="flex flex-col min-h-screen bg-gray-100 px-4 pt-4 pb-24">

    {{-- ============================
         ОБЩАЯ ИНФОРМАЦИЯ
    ============================ --}}
    <div class="bg-white shadow rounded-xl p-4 space-y-2">
        <h2 class="text-lg font-semibold">🚛 Рейс #{{ $trip->id }}</h2>

        <p class="text-sm">
            <strong>Машина:</strong> {{ $trip->truck?->plate ?? '—' }}
        </p>

        @php
            $routeLine = $steps->map(fn($s) =>
                ($s->type === 'loading' ? '📦' : '📤') . ' ' .
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
         СПИСОК ШАГОВ (АККОРДЕОН)
    ============================ --}}
    @foreach ($steps as $step)

        @php
            $city = getCityNameByCountryId($step->country_id, $step->city_id)
                ?? getCountryById($step->country_id);
            $label = $step->type === 'loading' ? '📦 Погрузка' : '📤 Разгрузка';
        @endphp

        <div x-data="{ open: false }"
             class="bg-white shadow rounded-xl mb-4 overflow-hidden border">

            {{-- 🔹 ШАПКА ШАГА --}}
            <button @click="open = !open"
                    class="w-full px-4 py-3 flex items-center justify-between bg-gray-50">
                <div class="flex flex-col text-left">
                    <span class="text-[15px] font-semibold">{{ $label }}</span>
                    <span class="text-xs text-gray-500">{{ $city }}</span>
                </div>
                <span class="text-xs text-gray-400" x-text="open ? '▲' : '▼'"></span>
            </button>


            {{-- 🔹 СОДЕРЖИМОЕ ШАГА --}}
            <div x-show="open" x-collapse class="p-4 space-y-4">

                {{-- Локация --}}
                <div class="bg-gray-50 rounded p-3 text-sm space-y-1">
                    <p><strong>📍 Локация:</strong> {{ $city }}</p>
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
                     ДОКУМЕНТЫ ШАГА
                ============================ --}}
                @php $docCount = $step->stepDocuments->count(); @endphp

                <div x-data="{ openUpload: @js($errors->isNotEmpty()), openList: false }"
                     class="border-t pt-3 mt-3">


                    {{-- Кнопка загрузки --}}
                    <button @click="openUpload = !openUpload"
                            class="w-full flex items-center justify-between px-3 py-2 bg-indigo-50 rounded-lg text-sm font-semibold">
                        📤 Pievienot dokumentu
                        <span x-text="openUpload ? '▲' : '▼'" class="text-xs"></span>
                    </button>

                    {{-- Форма загрузки --}}
                    <div x-show="openUpload" x-collapse class="mt-3">
                        <livewire:driver-app.driver-step-document-uploader 
                            :trip="$trip"
                            :step="$step"
                            :key="'driver-upload-'.$step->id"
                        />
                    </div>


                    {{-- Кнопка списка --}}
                    <button @click="openList = !openList"
                            class="w-full flex items-center justify-between mt-4 px-3 py-2 bg-gray-100 rounded-lg text-sm font-semibold">
                        📁 Dokumenti solim <span class="text-blue-600">({{ $docCount }})</span>
                        <span x-text="openList ? '▲' : '▼'" class="text-xs"></span>
                    </button>

                    {{-- Список документов --}}
                    <div x-show="openList" x-collapse class="mt-3">

                        @foreach ($step->stepDocuments as $doc)
                            @php
                                $typeEnum = $doc->type;
                                $url = asset('storage/'.$doc->file_path);
                                $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                                $isPdf = $ext === 'pdf';
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
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
                                        <a href="{{ $url }}" target="_blank" class="font-bold text-red-600 text-sm">PDF</a>

                                    @elseif ($isImage)
                                        <a href="{{ $url }}" target="_blank">
                                            <img src="{{ $url }}" class="w-14 h-14 object-cover">
                                        </a>

                                    @else
                                        <a href="{{ $url }}" target="_blank" class="text-indigo-600 underline text-xs">
                                            Open
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
         РАСХОДЫ ВОДИТЕЛЯ
    ============================ --}}
    <livewire:driver-app.driver-trip-expenses :trip="$trip" :key="'expenses-'.$trip->id" />

</div>
