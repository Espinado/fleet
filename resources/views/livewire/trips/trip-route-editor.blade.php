<div class="bg-white p-6 rounded-xl shadow space-y-4" wire:key="trip-route-editor">

    <h2 class="text-2xl font-bold flex items-center gap-2">
        🧭 Маршрут рейса
    </h2>

    @if(!$readonly)
        <p class="text-gray-600 text-sm mb-4">
            Перетащите карточки, чтобы изменить порядок выполнения.
            Водитель увидит маршрут именно в этом порядке.
        </p>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Главное: wire:ignore.self — не пересоздаёт UL --}}
    <ul id="sortableSteps"
        wire:ignore.self
        class="space-y-4">

        @foreach($steps as $step)
            <li wire:key="step-{{ $step['id'] }}"
                data-id="{{ $step['id'] }}"
                class="p-4 rounded-xl border shadow-sm transition
                       {{ $readonly ? 'bg-gray-100' : 'bg-gray-50 hover:bg-gray-100' }}"
            >
                <div class="flex justify-between items-start">

                    <div class="space-y-1">
                        <p class="text-lg font-semibold">
                            {{ $step['type'] === 'loading' ? '📦 Погрузка' : '📤 Разгрузка' }}
                        </p>

                        <p class="text-sm text-gray-700"><b>Страна:</b> {{ $step['country'] }}</p>
                        <p class="text-sm text-gray-700"><b>Город:</b> {{ $step['city'] }}</p>
                        <p class="text-sm"><b>Адрес:</b> {{ $step['address'] }}</p>
                        <p class="text-xs text-gray-500"><b>Дата:</b> {{ $step['date'] }}</p>
                    </div>

                    @unless($readonly)
                        <div class="text-gray-400 text-xl cursor-move select-none">☰</div>
                    @endunless
                </div>
            </li>
        @endforeach

    </ul>
</div>


@push('scripts')
@if(!$readonly)
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<script>
    function initSortableSteps() {
        const list = document.getElementById('sortableSteps');
        if (!list || list.dataset.sortableAttached === "1") return;

        new Sortable(list, {
            animation: 150,
            handle: '.cursor-move',

            onEnd() {
                let orderedIds = [];
                list.querySelectorAll('li').forEach(li => {
                    orderedIds.push(parseInt(li.dataset.id));
                });

                console.log("SORTED IDS:", orderedIds);

                // 🔥 Получаем ID текущего Livewire-компонента
                const compId = list.closest('[wire\\:id]').getAttribute('wire:id');

                // 🔥 Находим компонент
                const component = Livewire.find(compId);

                // 🔥 Вызываем метод напрямую — ПЕРЕДАЧА ТОЧНАЯ
                component.call('updateOrder', { ids: orderedIds });
            }
        });

        list.dataset.sortableAttached = "1";
    }

    // При обновлении Livewire
    document.addEventListener('livewire:updated', initSortableSteps);

    // При SPA-навигации Livewire
    document.addEventListener('livewire:navigated', initSortableSteps);

    // При первом открытии страницы
    document.addEventListener('DOMContentLoaded', initSortableSteps);
</script>
@endif
@endpush
