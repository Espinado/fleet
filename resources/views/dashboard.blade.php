@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
   
    <div class="bg-white shadow rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4">Expiring Documents</h2>
        @livewire('expiring-documents-table')
    </div>
@endsection

