@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')


    <div class="bg-white shadow rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4">Drivers</h2>
         <a href="{{ route('drivers.create') }}"
       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        ➕ New Driver
    </a>
        @livewire('drivers-table')
    </div>
@endsection
