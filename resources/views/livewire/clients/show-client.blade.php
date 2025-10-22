<div class="p-6">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 max-w-4xl mx-auto relative">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">🏢 {{ $client->company_name }}</h2>

            <div class="flex gap-2">
                <a href="{{ route('clients.edit', $client->id) }}"
                   class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                    ✏️ Edit
                </a>
                <a href="{{ route('clients.index') }}"
                   class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                    ← Back
                </a>
            </div>
        </div>

        <div class="space-y-4 text-gray-700">
            {{-- Company Info --}}
            <div>
                <h3 class="font-semibold text-lg mb-2">Company Info</h3>
                <p><span class="font-medium">Reg. Nr:</span> {{ $client->reg_nr ?? '-' }}</p>
                <p><span class="font-medium">Representative:</span> {{ $client->representative ?? '-' }}</p>
                <p><span class="font-medium">Email:</span> {{ $client->email ?? '-' }}</p>
                <p><span class="font-medium">Phone:</span> {{ $client->phone ?? '-' }}</p>
            </div>

            {{-- Legal Address --}}
            <div>
                <h3 class="font-semibold text-lg mb-2">Legal Address</h3>
                <p>{{ $client->jur_address ?? '-' }}</p>
                <p>{{ $client->jur_city ?? '' }} {{ $client->jur_post_code ?? '' }}</p>
                <p>{{ $client->jur_country ?? '' }}</p>
            </div>

            {{-- Physical Address --}}
            <div>
                <h3 class="font-semibold text-lg mb-2">Physical Address</h3>
                <p>{{ $client->fiz_address ?? '-' }}</p>
                <p>{{ $client->fiz_city ?? '' }} {{ $client->fiz_post_code ?? '' }}</p>
                <p>{{ $client->fiz_country ?? '' }}</p>
            </div>

            {{-- Bank Details --}}
            <div>
                <h3 class="font-semibold text-lg mb-2">Bank Details</h3>
                <p><span class="font-medium">Bank Name:</span> {{ $client->bank_name ?? '-' }}</p>
                <p><span class="font-medium">SWIFT:</span> {{ $client->swift ?? '-' }}</p>
            </div>
        </div>

        {{-- Кнопка удаления --}}
        <div class="mt-8 border-t pt-4 flex justify-end">
            <button
                wire:click="$set('confirmingDelete', true)"
                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                🗑 Delete Client
            </button>
        </div>
    </div>

    {{-- Модалка подтверждения удаления --}}
    @if($confirmingDelete ?? false)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Delete client?</h3>
                <p class="text-gray-600 mb-5">
                    Are you sure you want to delete <b>{{ $client->company_name }}</b>?<br>
                    This action cannot be undone.
                </p>

                <div class="flex justify-end gap-3">
                    <button wire:click="$set('confirmingDelete', false)"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button wire:click="delete"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
