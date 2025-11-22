<div class="sticky top-0 z-50 bg-white shadow-md">
    <div class="h-14 flex items-center px-4 gap-3 relative">

        @if(!empty($back))
            <button onclick="history.back()" 
                class="absolute left-4 flex items-center justify-center 
                       w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200
                       transition text-gray-700 text-xl">
                ←
            </button>
        @endif

        <h1 class="mx-auto text-lg font-semibold text-gray-800">
            {{ $title ?? '' }}
        </h1>
    </div>
</div>
