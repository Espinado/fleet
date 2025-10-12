<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\DriversTable;
use App\Livewire\TrucksTable;
use App\Livewire\TrailersTable;
use App\Http\Controllers\DriverController;
use App\Livewire\Drivers\ShowDriver;
use App\Livewire\Drivers\EditDriver;
use App\Livewire\Drivers\CreateDriver;
use App\Livewire\Trucks\ShowTruck;
use App\Livewire\Trucks\EditTruck;
use App\Livewire\Trucks\CreateTruck;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/drivers', DriversTable::class)->name('drivers.index');



Route::get('/trailers', TrailersTable::class)->name('trailers.index');

// Сначала создание водителя


Route::get('/drivers/create', \App\Livewire\Drivers\CreateDriver::class)->name('drivers.create');
Route::get('/drivers/{driver}/edit', \App\Livewire\Drivers\EditDriver::class)->name('drivers.edit');
Route::get('/drivers/{driver}', \App\Livewire\Drivers\ShowDriver::class)->name('drivers.show');
Route::post('/drivers/destroy', \App\Livewire\Drivers\EditDriver::class)->name('drivers.destroy');

 Route::get('/trucks', \App\Livewire\TrucksTable::class)->name('trucks.index');
Route::get('/trucks/create', \App\Livewire\Trucks\CreateTruck::class)->name('trucks.create');
Route::get('/trucks/{truck}', \App\Livewire\Trucks\ShowTruck::class)->name('trucks.show');
Route::get('/trucks/{truck}/edit', \App\Livewire\Trucks\EditTruck::class)->name('trucks.edit');


