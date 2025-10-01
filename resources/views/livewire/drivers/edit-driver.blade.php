@section('title', 'Edit ' . $driver->first_name . ' ' . $driver->last_name)

<div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-8 space-y-8">

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-6">

        {{-- Основная информация --}}
        <h2 class="text-xl font-semibold border-b pb-2 mb-4">Personal Info</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="font-semibold">First Name</label>
                <input type="text" wire:model.defer="driver.first_name" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="font-semibold">Last Name</label>
                <input type="text" wire:model.defer="driver.last_name" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="font-semibold">Personal Code</label>
                <input type="text" wire:model.defer="driver.personal_code" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        {{-- Адреса --}}
        <h2 class="text-xl font-semibold border-b pb-2 mb-4">Addresses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="font-semibold mb-1">Declared Address</h3>
                <input type="text" wire:model.defer="driver.declared_country" placeholder="Country" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.declared_city" placeholder="City" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.declared_street" placeholder="Street" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.declared_building" placeholder="Building" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.declared_room" placeholder="Room" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.declared_postcode" placeholder="Postcode" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <h3 class="font-semibold mb-1">Actual Address</h3>
                <input type="text" wire:model.defer="driver.actual_country" placeholder="Country" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.actual_city" placeholder="City" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.actual_street" placeholder="Street" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.actual_building" placeholder="Building" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.actual_room" placeholder="Room" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        {{-- Лицензии и медсправка --}}
        <h2 class="text-xl font-semibold border-b pb-2 mb-4">License & Medical</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="font-semibold">License Number</label>
                <input type="text" wire:model.defer="driver.license_number" class="w-full border rounded px-3 py-2">
                <div class="flex gap-2 mt-2">
                    <div>
                        <label class="font-semibold">Issued</label>
                        <input type="date" wire:model.defer="driver.license_issued" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="font-semibold">Expires</label>
                        <input type="date" wire:model.defer="driver.license_end" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
            </div>
            <div>
                <label class="font-semibold">95 Code</label>
                <input type="text" wire:model.defer="driver.code95_issued" placeholder="Issued" class="w-full border rounded px-3 py-2 mb-1">
                <input type="text" wire:model.defer="driver.code95_end" placeholder="Expires" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        {{-- Фото --}}
        <h2 class="text-xl font-semibold border-b pb-2 mb-4">Photos</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="font-semibold">Driver Photo</label>
                <input type="file" wire:model="photo" class="mt-1">
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-40 object-cover mt-2 rounded">
                @elseif ($driver->photo)
                    <img src="{{ asset('storage/' . $driver->photo) }}" class="w-full h-40 object-cover mt-2 rounded">
                @endif
            </div>
            <div>
                <label class="font-semibold">License Photo</label>
                <input type="file" wire:model="license_photo" class="mt-1">
                @if ($license_photo)
                    <img src="{{ $license_photo->temporaryUrl() }}" class="w-full h-40 object-cover mt-2 rounded">
                @elseif ($driver->license_photo)
                    <img src="{{ asset('storage/' . $driver->license_photo) }}" class="w-full h-40 object-cover mt-2 rounded">
                @endif
            </div>
            <div>
                <label class="font-semibold">Medical Certificate</label>
                <input type="file" wire:model="medical_certificate_photo" class="mt-1">
                @if ($medical_certificate_photo)
                    <img src="{{ $medical_certificate_photo->temporaryUrl() }}" class="w-full h-40 object-cover mt-2 rounded">
                @elseif ($driver->medical_certificate_photo)
                    <img src="{{ asset('storage/' . $driver->medical_certificate_photo) }}" class="w-full h-40 object-cover mt-2 rounded">
                @endif
            </div>
        </div>

        {{-- Кнопка Сохранить --}}
        <div class="mt-6">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700">
                💾 Save Changes
            </button>
        </div>

    </form>
</div>
