<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\ExpiringDocumentsTable;
use App\Http\Controllers\CmrController;
use App\Livewire\DriversTable;
use App\Livewire\TrucksTable;
use App\Livewire\TrailersTable;
use App\Livewire\ClientsTable;

use App\Livewire\Stats\TripsStatsTable;

use App\Livewire\Drivers\{ShowDriver, EditDriver, CreateDriver};
use App\Livewire\Trucks\{ShowTruck, EditTruck, CreateTruck};
use App\Livewire\Trailers\{ShowTrailer, EditTrailer, CreateTrailer};
use App\Livewire\Clients\{ShowClient, EditClient, CreateClient};

use App\Livewire\TripsTable;
use App\Livewire\Trips\{CreateTrip, ViewTrip, EditTrip};

use App\Livewire\Invoices\InvoicesTable;
use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Stats\EventsTable;
use App\Livewire\Map\FleetMap;



use App\Notifications\TestPushNotification;

// Главная страница
Route::redirect('/', '/dashboard');
if (app()->environment('local')) {
    Route::get('/_dev/find-odometer', function () {
        if (!app()->environment('local')) {
            abort(404);
        }
        $root = app_path();
        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );
        $matches = [];
        foreach ($rii as $file) {
            if ($file->getFilename() === 'MaponOdometerFetcher.php') {
                $matches[] = $file->getPathname();
            }
        }
        return $matches;
    });
}

// === БЛОК АДМИНА (auth + verified) ===
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/test-push', function () {
        $user = Auth::user();
        $user->notify(new TestPushNotification());
        return "✅ Push sent to {$user->email}";
    })->name('test-push');

    Route::post('/push/subscribe', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'endpoint' => 'required|string',
            'public_key' => 'required|string',
            'auth_token' => 'required|string',
            'content_encoding' => 'required|string',
        ]);
        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['public_key'],
            $data['auth_token'],
            $data['content_encoding']
        );
        return response()->json(['status' => 'subscribed']);
    })->name('push.subscribe');

    Route::get('/dashboard', ExpiringDocumentsTable::class)->name('dashboard');

    // Прокси тайлов OSM — до /map, чтобы не перехватывалось Livewire. OSM требует User-Agent.
    Route::get('/map/tiles/{z}/{x}/{y}.png', function (int $z, int $x, int $y) {
        $z = max(0, min(19, $z));
        $url = 'https://a.tile.openstreetmap.org/' . $z . '/' . $x . '/' . $y . '.png';
        $response = \Illuminate\Support\Facades\Http::timeout(8)
            ->withHeaders([
                'User-Agent' => config('app.name', 'FleetManager') . '/1.0 (+' . config('app.url', '') . ')',
            ])
            ->get($url);
        if (!$response->successful()) {
            abort(404);
        }
        return response($response->body(), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    })->whereNumber(['z', 'x', 'y'])->name('map.tiles');

    Route::get('/map', FleetMap::class)->name('map.index');

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

     // Stats
    Route::get('/stats', TripsStatsTable::class)->name('stats.index');

     Route::get('/stats/events', EventsTable::class)->name('stats.events');

    // CMR generate
    Route::post('/cmr/{cargo}/generate', [CmrController::class, 'generateAndSave'])
        ->name('cmr.generate');

        Route::post('/invoice/{cargo}/generate', [CmrController::class, 'generateInvoiceAndSave'])
    ->name('invoice.generate');

    Route::get('/invoices', InvoicesTable::class)
    ->name('invoices.index');
  Route::get('/invoices/{invoice}/open', function (Invoice $invoice) {
    $invoice->load('trip');
    $user = auth()->user();
    if ($user && !$user->isAdmin() && $user->company_id !== null) {
        abort_if(!$invoice->trip || (int) $invoice->trip->carrier_company_id !== (int) $user->company_id, 403);
    }

    if (!$invoice->pdf_file) {
        abort(404, 'Invoice PDF not found');
    }

    if (!Storage::disk('public')->exists($invoice->pdf_file)) {
        abort(404, 'Invoice PDF file missing in storage');
    }

    return redirect()->away(asset('storage/' . $invoice->pdf_file));

})->name('invoices.open');



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
