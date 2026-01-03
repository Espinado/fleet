<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
      if (request()->getHost() === 'driver.fleet.test') {
        config(['session.cookie' => 'fleet_driver_session']);
    } else {
        config(['session.cookie' => 'fleet_admin_session']);
    }
    }
}
