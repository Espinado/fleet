@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')


    <div class="bg-white shadow rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4">Drivers</h2>
        @livewire('trucks-table')
    </div>
@endsection
