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

    {{-- UL для Sortable --}}
    <ul id="sortableSteps-{{ $tripId }}"
        wire:ignore
        class="space-y-4">

        @foreach($steps as $step)
            <li data-id="{{ $step['id'] }}"
                wire:key="step-{{ $step['id'] }}"
                class="p-4 rounded-xl border shadow-sm transition
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
document.addEventListener("DOMContentLoaded", initRouteSortable);
document.addEventListener("livewire:initialized", initRouteSortable);
document.addEventListener("livewire:navigated", initRouteSortable);

function initRouteSortable() {
    const list = document.getElementById("sortableSteps-{{ $tripId }}");

    if (!list || list.dataset.sortableAttached === "1") return;

    new Sortable(list, {
        animation: 150,
        handle: '.cursor-move',

        onEnd() {
            const ids = Array.from(list.querySelectorAll('li[data-id]'))
                .map(li => Number(li.dataset.id));

            console.log("SORTED →", ids);

            // 🚀 ПРЯМОЙ ВЫЗОВ МЕТОДА Livewire
            $wire.updateOrder({ orderedIds: ids });
        }
    });

    list.dataset.sortableAttached = "1";
}
</script>
@endif
@endpush


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
        box.style.opacity = "0";
        box.style.transition = "opacity 0.5s";
    }, 2500);

    box.addEventListener("transitionend", () => box.remove());
}
</script>

<style>
@keyframes fade {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade { animation: fade 0.25s ease-out; }
</style>
@endpush
