<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\DriversTable;
use App\Livewire\TrucksTable;
use App\Livewire\TrailersTable;
use App\Http\Controllers\DriverController;
use App\Livewire\Drivers\ShowDriver;
use App\Livewire\Drivers\EditDriver;
use App\Livewire\Drivers\CreateDriver;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/drivers', DriversTable::class)->name('drivers.index');

Route::get('/trucks',  function () {
    return view('trucks');
})->name('trucks.index');

Route::get('/trailers', TrailersTable::class)->name('trailers.index');

// Сначала создание водителя


Route::get('/drivers/create', \App\Livewire\Drivers\CreateDriver::class)->name('drivers.create');
Route::get('/drivers/{driver}/edit', \App\Livewire\Drivers\EditDriver::class)->name('drivers.edit');
Route::get('/drivers/{driver}', \App\Livewire\Drivers\ShowDriver::class)->name('drivers.show');
Route::post('/drivers/destroy', \App\Livewire\Drivers\EditDriver::class)->name('drivers.destroy');


