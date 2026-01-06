<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Yajra\DataTables\Html\Builder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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
        Builder::useVite();

        RateLimiter::for('sap-api', function (Request $request) {

            return Limit::perMinute(30) // 30 request / menit
                ->by(
                    optional($request->user())->id
                    ?: $request->header('X-Device-ID')
                    ?: $request->ip()
                );
        });
    }
}
