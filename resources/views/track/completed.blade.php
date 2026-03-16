@extends('layouts.track')

@section('title', __('app.track.completed_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-8 sm:px-6 text-center">
            <p class="text-4xl mb-4" aria-hidden="true">✓</p>
            <h1 class="text-lg font-semibold text-gray-900">{{ __('app.track.completed_heading') }}</h1>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('app.track.completed_message') }}
            </p>
            @if($cargo->shipper || $cargo->consignee)
                <p class="mt-3 text-sm text-gray-500">
                    {{ $cargo->shipper?->company_name ?? '—' }} → {{ $cargo->consignee?->company_name ?? '—' }}
                </p>
            @endif
        </div>
    </div>
    <p class="mt-4 text-center text-xs text-gray-400">{{ __('app.track.footer') }}</p>
</div>
@endsection
