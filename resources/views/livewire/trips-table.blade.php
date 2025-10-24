<div class="p-6">
    @if (session('success'))
  <div class="mb-4 p-4 rounded bg-green-100 border border-green-400 text-green-800">
    ✅ {{ session('success') }}
  </div>
@endif

@if (session('error'))
  <div class="mb-4 p-4 rounded bg-red-100 border border-red-400 text-red-800">
    ⚠️ {{ session('error') }}
  </div>
@endif
  <div class="bg-white shadow rounded-lg p-4">
    <div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
      <div class="flex items-center gap-2">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Search: client, expeditor, route, cargo"
               class="border rounded px-3 py-2" />
        <button wire:click="$set('search','')" class="px-2 py-1 rounded bg-gray-200">✖</button>
      </div>

      <div class="flex items-center gap-3">
        <label class="text-sm">Status:</label>
        <select wire:model.live="status" class="border rounded px-2 py-1 w-36">
          <option value="">All</option>
          <option value="planned">Planned</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <label class="text-sm">Rows:</label>
        <select wire:model.live="perPage" class="border rounded px-2 py-1 w-24">
          <option value="5">5</option><option value="10">10</option>
          <option value="20">20</option><option value="50">50</option>
        </select>

        <a href="{{ route('trips.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
           + Add Trip
        </a>
      </div>
    </div>

    <table class="w-full border-collapse text-left">
      <thead class="bg-gray-100 text-gray-700 border-b">
        <tr>
          <th wire:click="sortBy('start_date')" class="px-3 py-2 cursor-pointer">Start</th>
          <th class="px-3 py-2">Expeditor</th>
          <th class="px-3 py-2">Client</th>
          <th class="px-3 py-2">Driver</th>
          <th class="px-3 py-2">Truck</th>
          <th class="px-3 py-2">Trailer</th>
          <th class="px-3 py-2">Route</th>
          <th wire:click="sortBy('status')" class="px-3 py-2 cursor-pointer">Status</th>
          <th class="px-3 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($trips as $t)
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2">{{ optional($t->start_date)->format('d-m-Y') ?? '-' }}</td>
          <td class="px-3 py-2">{{ $t->expeditor_name }}</td>
          <td class="px-3 py-2">{{ $t->client->company_name ?? '-' }}</td>
          <td class="px-3 py-2">{{ ($t->driver->first_name ?? '') . ' ' . ($t->driver->last_name ?? '') }}</td>
          <td class="px-3 py-2">{{ $t->truck->plate ?? '-' }}</td>
          <td class="px-3 py-2">{{ $t->trailer->plate ?? '-' }}</td>
       <td class="px-3 py-2">
    {{ config('countries.' . $t->origin_country_id)['iso'] ?? '—' }}
    →
    {{ config('countries.' . $t->destination_country_id)['iso'] ?? '—' }}
</td>
          <td class="px-3 py-2">
            <span class="px-2 py-1 rounded text-xs {{ $t->status->color() }}">
              {{ $t->status->label() }}
            </span>
          </td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('trips.show', $t->id) }}" class="text-blue-600 hover:underline">👁️</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="9" class="text-center text-gray-500 py-4">No trips found</td></tr>
      @endforelse
      </tbody>
    </table>

    <div class="mt-4">{{ $trips->links() }}</div>
  </div>
</div>
