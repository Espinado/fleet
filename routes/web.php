<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ExpiringDocumentsTable;
use App\Livewire\DriversTable;
use App\Livewire\TrucksTable;
use App\Livewire\TrailersTable;
use App\Livewire\Drivers\{ShowDriver, EditDriver, CreateDriver};
use App\Livewire\Trucks\{ShowTruck, EditTruck, CreateTruck};
use App\Livewire\Trailers\{ShowTrailer, EditTrailer, CreateTrailer};

// Главная страница → редирект на дашборд
Route::redirect('/', '/dashboard');

// === Защищённые маршруты (требуют авторизацию) ===
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (твоя таблица истекающих документов)
    Route::get('/dashboard', ExpiringDocumentsTable::class)
        ->name('dashboard');

    // Drivers
    Route::get('/drivers', DriversTable::class)->name('drivers.index');
    Route::get('/drivers/create', CreateDriver::class)->name('drivers.create');
    Route::get('/drivers/{driver}', ShowDriver::class)->name('drivers.show');
    Route::get('/drivers/{driver}/edit', EditDriver::class)->name('drivers.edit');
    Route::post('/drivers/destroy', EditDriver::class)->name('drivers.destroy');

    // Trucks
    Route::get('/trucks', TrucksTable::class)->name('trucks.index');
    Route::get('/trucks/create', CreateTruck::class)->name('trucks.create');
    Route::get('/trucks/{truck}', ShowTruck::class)->name('trucks.show');
    Route::get('/trucks/{truck}/edit', EditTruck::class)->name('trucks.edit');
    Route::post('/trucks/destroy', ShowTruck::class)->name('trucks.destroy');

    // Trailers
    Route::get('/trailers', TrailersTable::class)->name('trailers.index');
    Route::get('/trailers/create', CreateTrailer::class)->name('trailers.create');
    Route::get('/trailers/{trailer}', ShowTrailer::class)->name('trailers.show');
    Route::get('/trailers/{trailer}/edit', EditTrailer::class)->name('trailers.edit');
     Route::post('/trailers/destroy', ShowTrailer::class)->name('trailers.destroy');


    Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
});

// === Профиль (опционально) ===
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// === Breeze аутентификация ===
require __DIR__.'/auth.php';
