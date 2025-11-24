<div class="flex flex-col min-h-screen bg-black">

    {{-- HEADER --}}
    @include('driver-app.components.topbar', [
        'back' => 1,
        'title' => $title
    ])

    <div class="flex-1 flex items-center justify-center p-4">

        <img src="{{ $document->file_url }}"
             class="max-w-full max-h-full rounded shadow-lg">

    </div>

</div>
