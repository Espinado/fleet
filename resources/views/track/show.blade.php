@extends('layouts.track')

@section('title', __('app.track.page_title'))

@section('content')
@php
    $trip = $cargo->trip;
@endphp
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header: cargo / sender → recipient --}}
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
            <h1 class="text-lg font-semibold text-gray-900">{{ __('app.track.heading') }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $trip->expeditor_name ?? __('app.track.trip') }}
            </p>
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-sm text-gray-700">
                    <span class="font-medium">{{ $cargo->shipper?->company_name ?? '—' }}</span>
                    → <span class="font-medium">{{ $cargo->consignee?->company_name ?? '—' }}</span>
                </span>
            </div>
            <div class="mt-2">
                @php
                    $status = $trip->status;
                    $statusLabel = $status instanceof \App\Enums\TripStatus ? $status->label() : (is_object($status) && isset($status->value) ? __('app.trip.status.' . $status->value) : '—');
                    $statusColor = $status instanceof \App\Enums\TripStatus ? $status->color() : 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        {{-- Route: only loading and unloading for this cargo --}}
        <div class="px-4 py-4 sm:px-6">
            <h2 class="text-sm font-medium text-gray-700 mb-3">{{ __('app.track.route') }}</h2>
            <ul class="space-y-0">
                @foreach([$loadingStep, $unloadingStep] as $step)
                    @if($step)
                        @php
                            $countryName = getCountryById($step->country_id);
                            $cityName = getCityNameByCountryId($step->country_id, $step->city_id) ?? getCityById($step->city_id, $step->country_id);
                            $location = trim(implode(', ', array_filter([$cityName, $countryName])));
                            if (trim((string) $step->address) !== '') {
                                $location = $location ? $location . ', ' . $step->address : $step->address;
                            }
                            $stepStatus = $step->status;
                            $stepLabel = $stepStatus && method_exists($stepStatus, 'label') ? $stepStatus->label() : '—';
                            $stepColor = $stepStatus && method_exists($stepStatus, 'color') ? $stepStatus->color() : 'bg-gray-100 text-gray-700';
                            $dateTime = $step->date ? $step->date->format('d.m.Y') : '';
                            if (!empty($step->time)) {
                                $dateTime .= ($dateTime ? ' ' : '') . $step->time;
                            }
                            $isLoading = ($step->type ?? '') === 'loading';
                        @endphp
                        <li class="flex gap-3 sm:gap-4">
                            <div class="flex flex-col items-center shrink-0">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium {{ $isLoading ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $isLoading ? '↧' : '↥' }}
                                </span>
                                @if($step !== $unloadingStep)
                                    <div class="w-0.5 flex-1 min-h-[20px] bg-gray-200 my-0.5"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 pb-5">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                    {{ $isLoading ? __('app.trip.route.loading') : __('app.trip.route.unloading') }}
                                </p>
                                <p class="mt-0.5 font-medium text-gray-900">{{ $location ?: '—' }}</p>
                                @if($dateTime)
                                    <p class="mt-1 text-sm text-gray-600">{{ __('app.track.eta') }}: {{ $dateTime }}</p>
                                @endif
                                <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded text-xs font-medium {{ $stepColor }}">
                                    {{ $stepLabel }}
                                </span>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
            @if(!$loadingStep && !$unloadingStep)
                <p class="text-sm text-gray-500 py-2">{{ __('app.track.no_steps') }}</p>
            @endif
        </div>
    </div>

    <p class="mt-4 text-center text-xs text-gray-400">{{ __('app.track.footer') }}</p>
</div>
@endsection
