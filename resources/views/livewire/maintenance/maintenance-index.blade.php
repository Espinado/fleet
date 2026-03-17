<div class="min-h-screen bg-gray-100 pb-24">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-1">
            {{ __('app.maintenance.page_title') }}
        </h1>
        <p class="text-sm text-gray-500 mb-6">
            {{ __('app.owner_dashboard.upcoming_maintenance_hint') }}
        </p>

        <div class="flex flex-wrap items-center gap-3 mb-6">
            <a href="{{ route('maintenance.records.index') }}" wire:navigate
               class="px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-sm font-medium text-gray-700 min-h-[44px] inline-flex items-center touch-manipulation">
                {{ __('app.maintenance_record.journal_title') }} →
            </a>
            <a href="{{ route('maintenance.records.create') }}" wire:navigate
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium min-h-[44px] inline-flex items-center touch-manipulation">
                {{ __('app.maintenance_record.add_record') }}
            </a>
        </div>

        {{-- Истекающие документы (все типы) --}}
        <section class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">{{ __('app.maintenance.expiring_documents') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('app.maintenance.expiring_documents_hint') }}</p>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="search" wire:model.live.debounce.300ms="expiringSearch"
                           placeholder="{{ __('app.maintenance.search_placeholder') }}"
                           class="flex-1 min-w-0 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px] text-sm">
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-gray-500 hidden sm:inline">{{ __('app.maintenance.sort') }}:</span>
                        <select wire:model.live="expiringSort" class="rounded-lg border-gray-300 text-sm min-h-[44px] py-2 pr-8">
                            <option value="expires_at">{{ __('app.maintenance.sort_by_date') }}</option>
                            <option value="entity_name">{{ __('app.maintenance.sort_by_name') }}</option>
                        </select>
                        <button type="button" wire:click="$set('expiringDir', '{{ $expiringDir === 'asc' ? 'desc' : 'asc' }}')"
                                class="p-2 rounded-lg border border-gray-300 bg-gray-50 hover:bg-gray-100 min-h-[44px] min-w-[44px] touch-manipulation"
                                title="{{ $expiringDir === 'asc' ? __('app.maintenance.sort_desc') : __('app.maintenance.sort_asc') }}">
                            {{ $expiringDir === 'asc' ? '↑' : '↓' }}
                        </button>
                    </div>
                </div>
            </div>
            @if($expiringDocuments->isEmpty())
                <div class="px-4 py-8 text-center text-gray-500 text-sm">
                    {{ __('app.maintenance.no_expiring_docs') }}
                </div>
            @else
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm min-w-[320px]">
                        <thead class="bg-gray-50 border-t border-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('app.maintenance.doc_type') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('app.maintenance.entity_name') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('app.maintenance.expires_at') }}</th>
                                <th class="px-4 py-2 w-24"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringDocuments as $item)
                                <tr class="border-t border-gray-100 hover:bg-gray-50/50">
                                    <td class="px-4 py-2 text-gray-900">{{ __('app.' . $item->doc_label_key) }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $item->entity_name }}</td>
                                    <td class="px-4 py-2 tabular-nums">
                                        @if($item->is_overdue)
                                            <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">{{ __('app.maintenance.inspection_overdue') }}</span>
                                        @endif
                                        <span class="{{ $item->is_overdue ? 'ml-1' : '' }}">{{ $item->expires_at->format('d.m.Y') }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($item->entity_type === 'truck')
                                            <a href="{{ route('trucks.show', $item->entity_id) }}" wire:navigate class="text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation">{{ __('app.truck.show.title') }} →</a>
                                        @elseif($item->entity_type === 'trailer')
                                            <a href="{{ route('trailers.show', $item->entity_id) }}" wire:navigate class="text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation">{{ __('app.trailer.show.title') }} →</a>
                                        @else
                                            <a href="{{ route('drivers.show', $item->entity_id) }}" wire:navigate class="text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation">{{ __('app.driver.show.title') }} →</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Мобильные карточки --}}
                <div class="sm:hidden divide-y divide-gray-100 border-t border-gray-100">
                    @foreach($expiringDocuments as $item)
                        <a href="{{ $item->entity_type === 'truck' ? route('trucks.show', $item->entity_id) : ($item->entity_type === 'trailer' ? route('trailers.show', $item->entity_id) : route('drivers.show', $item->entity_id)) }}" wire:navigate
                           class="block px-4 py-3 hover:bg-gray-50 active:bg-gray-100 touch-manipulation">
                            <p class="font-medium text-gray-900">{{ $item->entity_name }}</p>
                            <p class="text-sm text-gray-600">{{ __('app.' . $item->doc_label_key) }}</p>
                            <p class="text-sm tabular-nums mt-1">
                                @if($item->is_overdue)
                                    <span class="text-red-600 font-medium">{{ __('app.maintenance.inspection_overdue') }}</span>
                                @endif
                                {{ $item->expires_at->format('d.m.Y') }}
                            </p>
                        </a>
                    @endforeach
                </div>
                <div class="px-4 py-3 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-gray-500">
                        {{ __('app.maintenance.showing_page', ['from' => $expiringDocuments->firstItem() ?? 0, 'to' => $expiringDocuments->lastItem() ?? 0, 'total' => $expiringDocuments->total()]) }}
                    </p>
                    <div class="flex items-center gap-2">
                        @if($expiringDocuments->currentPage() > 1)
                            <button type="button" wire:click="setExpiringPage({{ $expiringDocuments->currentPage() - 1 }})"
                                    class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium min-h-[44px] touch-manipulation">{{ __('app.pagination.previous') }}</button>
                        @endif
                        @if($expiringDocuments->currentPage() < $expiringDocuments->lastPage())
                            <button type="button" wire:click="setExpiringPage({{ $expiringDocuments->currentPage() + 1 }})"
                                    class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium min-h-[44px] touch-manipulation">{{ __('app.pagination.next') }}</button>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        {{-- Предстоящее ТО --}}
        <section class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">{{ __('app.owner_dashboard.upcoming_maintenance') }}</h2>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="search" wire:model.live.debounce.300ms="upcomingSearch"
                           placeholder="{{ __('app.maintenance.search_placeholder') }}"
                           class="flex-1 min-w-0 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px] text-sm">
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-gray-500 hidden sm:inline">{{ __('app.maintenance.sort') }}:</span>
                        <select wire:model.live="upcomingSort" class="rounded-lg border-gray-300 text-sm min-h-[44px] py-2 pr-8">
                            <option value="expires_at">{{ __('app.maintenance.sort_by_date') }}</option>
                            <option value="name">{{ __('app.maintenance.sort_by_name') }}</option>
                        </select>
                        <button type="button" wire:click="$set('upcomingDir', '{{ $upcomingDir === 'asc' ? 'desc' : 'asc' }}')"
                                class="p-2 rounded-lg border border-gray-300 bg-gray-50 hover:bg-gray-100 min-h-[44px] min-w-[44px] touch-manipulation">
                            {{ $upcomingDir === 'asc' ? '↑' : '↓' }}
                        </button>
                    </div>
                </div>
            </div>
            @if($upcomingMaintenance->isEmpty())
                <div class="px-4 py-8 text-center text-gray-500 text-sm">
                    {{ __('app.maintenance.no_upcoming') }}
                </div>
            @else
                <ul class="divide-y divide-gray-100 hidden sm:block">
                    @foreach($upcomingMaintenance as $item)
                        <li class="px-4 py-3 grid grid-cols-1 sm:grid-cols-[1fr_12rem_11rem_11rem] items-center gap-x-4 gap-y-2 hover:bg-gray-50/50">
                            <div class="min-w-0 flex items-center">
                                <span class="font-medium text-gray-900 truncate">{{ $item->name }}</span>
                                <span class="ml-2 text-xs flex-shrink-0 {{ $item->type === 'truck' ? 'text-blue-600' : 'text-amber-600' }}">{{ $item->type === 'truck' ? '🚛' : '🚚' }}</span>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2 text-sm text-gray-600 min-w-0 tabular-nums">
                                @if($item->due_by_km && $item->next_service_km)
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-800 shrink-0">{{ __('app.maintenance.due_by_km') }}: {{ number_format($item->next_service_km, 0, '.', ' ') }} km</span>
                                @endif
                                @if($item->due_by_date && $item->next_service_date)
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 shrink-0">{{ __('app.maintenance.due_by_date') }}: {{ \Carbon\Carbon::parse($item->next_service_date)->format('d.m.Y') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-center min-w-0">
                                <a href="{{ $item->type === 'truck' ? route('maintenance.records.create', ['truck_id' => $item->id]) : route('maintenance.records.create', ['trailer_id' => $item->id]) }}" wire:navigate
                                   class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 min-h-[44px] touch-manipulation whitespace-nowrap">
                                    {{ __('app.maintenance.conduct_service') }}
                                </a>
                            </div>
                            <div class="flex items-center justify-end min-w-0">
                                <a href="{{ $item->type === 'truck' ? route('trucks.show', $item->id) : route('trailers.show', $item->id) }}" wire:navigate class="text-sm text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation">
                                    {{ $item->type === 'truck' ? __('app.truck.show.title') : __('app.trailer.show.title') }} →
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="sm:hidden divide-y divide-gray-100">
                    @foreach($upcomingMaintenance as $item)
                        <div class="px-4 py-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-900">{{ $item->name }} <span class="text-xs {{ $item->type === 'truck' ? 'text-blue-600' : 'text-amber-600' }}">{{ $item->type === 'truck' ? '🚛' : '🚚' }}</span></p>
                            <div class="flex flex-wrap gap-2 mt-1 text-sm text-gray-600">
                                @if($item->due_by_km && $item->next_service_km)
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-800">{{ __('app.maintenance.due_by_km') }}: {{ number_format($item->next_service_km, 0, '.', ' ') }} km</span>
                                @endif
                                @if($item->due_by_date && $item->next_service_date)
                                    <span class="tabular-nums">{{ __('app.maintenance.due_by_date') }}: {{ \Carbon\Carbon::parse($item->next_service_date)->format('d.m.Y') }}</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <a href="{{ $item->type === 'truck' ? route('maintenance.records.create', ['truck_id' => $item->id]) : route('maintenance.records.create', ['trailer_id' => $item->id]) }}" wire:navigate
                                   class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 min-h-[44px] touch-manipulation">
                                    {{ __('app.maintenance.conduct_service') }}
                                </a>
                                <a href="{{ $item->type === 'truck' ? route('trucks.show', $item->id) : route('trailers.show', $item->id) }}" wire:navigate
                                   class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 min-h-[44px] touch-manipulation">
                                    {{ $item->type === 'truck' ? __('app.truck.show.title') : __('app.trailer.show.title') }} →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-4 py-3 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-gray-500">
                        {{ __('app.maintenance.showing_page', ['from' => $upcomingMaintenance->firstItem() ?? 0, 'to' => $upcomingMaintenance->lastItem() ?? 0, 'total' => $upcomingMaintenance->total()]) }}
                    </p>
                    <div class="flex items-center gap-2">
                        @if($upcomingMaintenance->currentPage() > 1)
                            <button type="button" wire:click="setUpcomingPage({{ $upcomingMaintenance->currentPage() - 1 }})"
                                    class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium min-h-[44px] touch-manipulation">{{ __('app.pagination.previous') }}</button>
                        @endif
                        @if($upcomingMaintenance->currentPage() < $upcomingMaintenance->lastPage())
                            <button type="button" wire:click="setUpcomingPage({{ $upcomingMaintenance->currentPage() + 1 }})"
                                    class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium min-h-[44px] touch-manipulation">{{ __('app.pagination.next') }}</button>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        @if($expiringDocuments->isEmpty() && $upcomingMaintenance->isEmpty())
            <div class="rounded-xl bg-white border border-gray-200 p-8 text-center text-gray-500 mt-6">
                <p class="text-base">{{ __('app.maintenance.no_upcoming') }}</p>
                <p class="text-sm mt-2">{{ __('app.maintenance.no_upcoming_hint') }}</p>
                <a href="{{ route('trucks.index') }}" wire:navigate class="mt-4 inline-block text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation">
                    {{ __('app.nav.trucks') }} →
                </a>
                <span class="mx-2">·</span>
                <a href="{{ route('trailers.index') }}" wire:navigate class="inline-block text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation">
                    {{ __('app.nav.trailers') }} →
                </a>
            </div>
        @endif
    </div>
</div>
