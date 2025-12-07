<div class="bg-white p-6 rounded-xl shadow space-y-4"
     wire:key="route-editor-{{ $tripId }}"
     x-data>

    {{-- =====================================
         🔔 WARNING POPUP при невозможности
         перемещения шагов
    ====================================== --}}
    <div
        x-data="{ show: false }"
        x-on:locked-step-warning.window="
            show = true;
            setTimeout(() => show = false, 2000);
        "
        x-show="show"
        x-transition.opacity.scale
        class="fixed inset-0 flex items-center justify-center z-[9999] pointer-events-none"
    >
        <div class="bg-yellow-500 text-white px-4 py-2 rounded-lg shadow-lg 
                    pointer-events-auto text-sm font-semibold">
            🔒 Šo soli nevar pārvietot
        </div>
    </div>


    {{-- =====================================
         HEADER
    ====================================== --}}
    <h2 class="text-2xl font-bold flex items-center gap-2">
        🧭 Маршрут рейса
    </h2>

    @unless($readonly)
        <p class="text-gray-600 text-sm mb-4">
            Перетащите карточки, чтобы изменить порядок действий.
            Водитель увидит маршрут именно в этом порядке.
        </p>
    @endunless


    {{-- =====================================
         SUCCESS TOAST (Livewire)
    ====================================== --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif


    {{-- =====================================
         SORTABLE LIST
    ====================================== --}}
    <ul id="sortableSteps-{{ $tripId }}"
        wire:ignore
        class="space-y-4"
      x-init="
    if ($el.dataset.sortableAttached === '1') return;

    // сохраняем исходный порядок в data-атрибуте
    const initialIds = Array.from($el.querySelectorAll('li[data-step-id]'))
        .map(li => Number(li.dataset.stepId));
    $el.dataset.stepOrder = initialIds.join(',');

    new Sortable($el, {
        animation: 240,
        easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)',
        handle: '.drag-handle',

        ghostClass: 'route-step-ghost',
        chosenClass: 'route-step-chosen',
        dragClass: 'route-step-dragging',

        fallbackOnBody: true,
        swapThreshold: 0.6,

        onMove(evt) {
            const dragged = evt.dragged;
            const related = evt.related;
            const to = evt.to;

            // 1) Заблокированный шаг нельзя тянуть
            if (dragged.classList.contains('locked-step')) {
                window.dispatchEvent(new CustomEvent('locked-step-warning'));
                return false;
            }

            // 2) Нельзя тянуть на место заблокированного
            if (related.classList.contains('locked-step')) {
                window.dispatchEvent(new CustomEvent('locked-step-warning'));
                return false;
            }

            // 3) Нельзя перепрыгивать через заблокированный шаг
            const items = Array.from(to.children);
            const dIndex = items.indexOf(dragged);
            const rIndex = items.indexOf(related);
            const dir = dIndex < rIndex ? 1 : -1;

            for (let i = dIndex + dir; dir === 1 ? i <= rIndex : i >= rIndex; i += dir) {
                if (items[i]?.classList.contains('locked-step')) {
                    window.dispatchEvent(new CustomEvent('locked-step-warning'));
                    return false;
                }
            }

            return true;
        },

        onEnd() {
            const ids = Array.from($el.querySelectorAll('li[data-step-id]'))
                .map(li => Number(li.dataset.stepId));

            const prev = $el.dataset.stepOrder || '';
            const next = ids.join(',');

            // 👉 Порядок не изменился — ничего не делаем, тост не нужен
            if (prev === next) {
                return;
            }

            // сохраняем новый порядок в data-атрибут
            $el.dataset.stepOrder = next;

            const root = $el.closest('[wire\\:id]');
            if (!root) return;

            Livewire.find(root.getAttribute('wire:id'))
                ?.call('updateOrder', { orderedIds: ids });
        },
    });

    $el.dataset.sortableAttached = '1';
"

    >

        {{-- =====================================
             ШАГИ СПИСКА
        ====================================== --}}
        @foreach($steps as $step)

            @php
                $isLocked = $step['locked'];
            @endphp

            <li data-step-id="{{ $step['id'] }}"
                wire:key="step-{{ $step['id'] }}"
                class="route-step-item rounded-xl border shadow-sm transition relative
                    {{ $isLocked ? 'bg-gray-200 opacity-75 locked-step' : ($readonly ? 'bg-gray-100' : 'bg-gray-50 hover:bg-gray-100') }}
                    p-4">

                <div class="flex justify-between items-start">

                    {{-- LEFT PART --}}
                    <div class="space-y-1">
                        <p class="text-lg font-semibold">
                            {{ $step['type'] === 'loading' ? '📦 Погрузка' : '📤 Разгрузка' }}
                        </p>

                        <p class="text-sm text-gray-700"><b>Страна:</b> {{ $step['country'] }}</p>
                        <p class="text-sm text-gray-700"><b>Город:</b> {{ $step['city'] }}</p>
                        <p class="text-sm"><b>Адрес:</b> {{ $step['address'] }}</p>

                        <p class="text-xs text-gray-500">
                            <b>Дата:</b> {{ $step['date'] ?? '—' }}
                            &nbsp;
                            <b>{{ $step['type'] === 'loading' ? 'Время погрузки' : 'Время разгрузки' }}:</b>
                            {{ $step['time'] ?? '—' }}
                        </p>
                    </div>

                    {{-- DRAG HANDLE --}}
                    @if(!$isLocked && !$readonly)
                        <div class="drag-handle cursor-move text-gray-400 text-xl select-none">☰</div>
                    @endif
                </div>

                {{-- LOCKED WARNING INLINE --}}
                @if($isLocked)
                    <div class="mt-3 text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded">
                        🔒 Šo soli vairs nevar pārvietot — tas jau tiek veikts vai pabeigts
                    </div>
                @endif

            </li>

        @endforeach

    </ul>


</div>


@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('order-updated', () => routeToast("Новая последовательность сохранена"));
});

function routeToast(text) {
    const box = document.createElement("div");
    box.className =
        "fixed bottom-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade";
    box.textContent = text;
    document.body.appendChild(box);

    setTimeout(() => {
        box.style.opacity = '0';
        box.style.transition = 'opacity 0.5s';
    }, 2500);

    box.addEventListener('transitionend', () => box.remove());
}
</script>

<style>
#sortableSteps-{{ $tripId }} .route-step-item {
    transition:
        transform 0.24s cubic-bezier(0.2, 0.8, 0.2, 1),
        box-shadow 0.2s ease,
        background-color 0.2s ease,
        opacity 0.2s ease;
}

.route-step-chosen {
    background-color: #eff6ff !important;
    transform: scale(1.03) translateY(-2px);
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.28);
    border-radius: 16px;
    z-index: 50;
}

.route-step-dragging {
    opacity: 0.95;
}

.route-step-ghost {
    opacity: 0.25;
    background: #dbeafe !important;
    border-radius: 16px;
    box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.35);
}

@keyframes fade {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade { animation: fade 0.25s ease-out; }
</style>
@endpush
