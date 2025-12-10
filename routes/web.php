<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\ExpiringDocumentsTable;
use App\Http\Controllers\CmrController;
use App\Livewire\DriversTable;
use App\Livewire\TrucksTable;
use App\Livewire\TrailersTable;
use App\Livewire\ClientsTable;

use App\Livewire\Drivers\{ShowDriver, EditDriver, CreateDriver};
use App\Livewire\Trucks\{ShowTruck, EditTruck, CreateTruck};
use App\Livewire\Trailers\{ShowTrailer, EditTrailer, CreateTrailer};
use App\Livewire\Clients\{ShowClient, EditClient, CreateClient};

use App\Livewire\TripsTable;
use App\Livewire\Trips\{CreateTrip, ViewTrip, EditTrip};

use App\Notifications\TestPushNotification;

// Главная страница
Route::redirect('/', '/dashboard');

// test push
Route::get('/test-push', function () {
    $user = Auth::user();

    if (!$user) return "❌ You are not logged in";

    $user->notify(new TestPushNotification());
    return "✅ Push sent to {$user->email}";
});

// === ЛОГИН ВОДИТЕЛЯ (публичный) ===

// === БЛОК АДМИНА (auth + verified) ===
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', ExpiringDocumentsTable::class)->name('dashboard');

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

    // Clients
    Route::get('/clients', ClientsTable::class)->name('clients.index');
    Route::get('/clients/create', CreateClient::class)->name('clients.create');
    Route::get('/clients/{client}', ShowClient::class)->name('clients.show');
    Route::get('/clients/{client}/edit', EditClient::class)->name('clients.edit');

    // Trips
    Route::get('/trips', TripsTable::class)->name('trips.index');
    Route::get('/trips/create', CreateTrip::class)->name('trips.create');
    Route::get('/trips/{trip}', ViewTrip::class)->name('trips.show');
    Route::get('/trips/{trip}/edit', EditTrip::class)->name('trips.edit');

    Route::post('/cmr/{cargo}/generate', [CmrController::class, 'generateAndSave'])
        ->name('cmr.generate');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('logout');

    Route::view('/offline-admin', 'offline-admin');
});


// === Profile ===
Route::view('/profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// === Breeze ===
require __DIR__.'/auth.php';
