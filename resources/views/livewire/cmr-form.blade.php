<div class="max-w-5xl mx-auto p-6 bg-white rounded shadow space-y-6">

    {{-- Уведомления --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="generatePdf" class="space-y-6">
        <h2 class="text-2xl font-bold">📄 Create CMR Document</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Shipper *</label>
                <input type="text" wire:model.blur="shipper_name" class="w-full border rounded p-2">
                @error('shipper_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block font-medium">Consignee *</label>
                <input type="text" wire:model.blur="consignee_name" class="w-full border rounded p-2">
                @error('consignee_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Loading Place *</label>
                <input type="text" wire:model.blur="loading_place" class="w-full border rounded p-2">
                @error('loading_place') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block font-medium">Unloading Place *</label>
                <input type="text" wire:model.blur="unloading_place" class="w-full border rounded p-2">
                @error('unloading_place') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block font-medium">Carrier *</label>
            <input type="text" wire:model.blur="carrier" class="w-full border rounded p-2">
            @error('carrier') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block font-medium">Attached Documents</label>
            <textarea wire:model.blur="attached_documents" class="w-full border rounded p-2"></textarea>
        </div>

        {{-- Таблица грузов --}}
        <h3 class="font-semibold text-lg border-b pb-1">📦 Cargo Items</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">Marks</th>
                        <th class="border p-2">Qty</th>
                        <th class="border p-2">Pack</th>
                        <th class="border p-2">Description</th>
                        <th class="border p-2">Gross</th>
                        <th class="border p-2">Volume</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                        <tr>
                            <td class="border"><input type="text" wire:model.blur="items.{{ $i }}.marks" class="w-full p-1 border-none"></td>
                            <td class="border"><input type="number" wire:model.blur="items.{{ $i }}.qty" class="w-full p-1 border-none"></td>
                            <td class="border"><input type="text" wire:model.blur="items.{{ $i }}.pack" class="w-full p-1 border-none"></td>
                            <td class="border"><input type="text" wire:model.blur="items.{{ $i }}.desc" class="w-full p-1 border-none"></td>
                            <td class="border"><input type="number" wire:model.blur="items.{{ $i }}.gross" class="w-full p-1 border-none"></td>
                            <td class="border"><input type="number" wire:model.blur="items.{{ $i }}.volume" class="w-full p-1 border-none"></td>
                            <td class="border text-center">
                                <button type="button" wire:click="removeItem({{ $i }})" class="text-red-600 hover:underline">✖</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="button" wire:click="addItem" class="mt-2 px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
            ➕ Add Item
        </button>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                💾 Generate PDF
            </button>
        </div>
    </form>

    {{-- JS для открытия PDF --}}
    <script>
        window.addEventListener('open-pdf', event => {
            if (event.detail.url) {
                window.open(event.detail.url, '_blank');
            }
        });
    </script>
</div>
