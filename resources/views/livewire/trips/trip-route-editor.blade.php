<div class="bg-white p-6 rounded-xl shadow space-y-4"
     wire:key="route-editor-{{ $tripId }}">

    <h2 class="text-2xl font-bold flex items-center gap-2">
        🧭 Маршрут рейса
    </h2>

    @unless($readonly)
        <p class="text-gray-600 text-sm mb-4">
            Перетащите карточки, чтобы изменить порядок действий.
            Водитель увидит маршрут именно в этом порядке.
        </p>
    @endunless

    {{-- SUCCESS TOAST --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- UL for Sortable --}}
    <ul id="sortableSteps-{{ $tripId }}"
        wire:ignore
        class="space-y-4"
        x-data
        x-init="
            if ($el.dataset.sortableAttached === '1') return;

            new Sortable($el, {
                animation: 240,
                easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)',
                handle: '.cursor-move',

                ghostClass: 'route-step-ghost',
                chosenClass: 'route-step-chosen',
                dragClass: 'route-step-dragging',

                fallbackOnBody: true,
                swapThreshold: 0.6,

                onEnd() {
                    const ids = Array.from($el.querySelectorAll('li[data-step-id]'))
                        .map(li => Number(li.dataset.stepId));

                    console.log('SORTED →', ids);

                    const root = $el.closest('[wire\\:id]');
                    if (!root) return;

                    const componentId = root.getAttribute('wire:id');
                    if (!componentId) return;

                    Livewire.find(componentId)?.call('updateOrder', { orderedIds: ids });
                },
            });

            $el.dataset.sortableAttached = '1';
        "
    >

        @foreach($steps as $step)
            <li data-step-id="{{ $step['id'] }}"
                wire:key="step-{{ $step['id'] }}"
                class="route-step-item p-4 rounded-xl border shadow-sm transition
                {{ $readonly ? 'bg-gray-100' : 'bg-gray-50 hover:bg-gray-100' }}">

                <div class="flex justify-between items-start">

                    <div class="space-y-1">
                        {{-- TYPE --}}
                        <p class="text-lg font-semibold">
                            {{ $step['type'] === 'loading' ? '📦 Погрузка' : '📤 Разгрузка' }}
                        </p>

                        {{-- LOCATION --}}
                        <p class="text-sm text-gray-700"><b>Страна:</b> {{ $step['country'] }}</p>
                        <p class="text-sm text-gray-700"><b>Город:</b> {{ $step['city'] }}</p>
                        <p class="text-sm"><b>Адрес:</b> {{ $step['address'] }}</p>

                        {{-- DATE & TIME --}}
                        <p class="text-xs text-gray-500">
                            <b>Дата:</b> {{ $step['date'] ?? '—' }}
                            &nbsp;
                            <b>{{ $step['type'] === 'loading' ? 'Время погрузки' : 'Время разгрузки' }}:</b>
                            {{ $step['time'] ?? '—' }}
                        </p>
                    </div>

                    @unless($readonly)
                        <div class="cursor-move text-gray-400 text-xl select-none">☰</div>
                    @endunless

                </div>

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
/* Базовая плавность для всех элементов списка */
#sortableSteps-{{ $tripId }} .route-step-item {
    transition:
        transform 0.24s cubic-bezier(0.2, 0.8, 0.2, 1),
        box-shadow 0.2s ease,
        background-color 0.2s ease,
        opacity 0.2s ease;
}

/* Элемент, который "выбрали" (подняли) */
.route-step-chosen {
    background-color: #eff6ff !important;
    transform: scale(1.03) translateY(-2px);
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.28);
    border-radius: 16px;
    z-index: 50;
}

/* Элемент, который тянем (active drag) */
.route-step-dragging {
    opacity: 0.95;
}

/* "Призрак" (ghost) на месте исходного элемента */
.route-step-ghost {
    opacity: 0.25;
    background: #dbeafe !important;
    border-radius: 16px;
    box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.35);
}

/* Небольшой fade-in для тостов */
@keyframes fade {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade { animation: fade 0.25s ease-out; }
</style>
@endpush
