@extends('layouts.app')

@section('title', __('app.trucks.title'))

@section('content')


    <div class="bg-white shadow rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4">{{ __('app.trucks.title') }}</h2>
        @livewire('trucks-table')
    </div>
@endsection
