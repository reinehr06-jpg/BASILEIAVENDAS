<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Força HTTPS para evitar erros de Mixed Content e falhas de POST via túneis (localtunnel/ngrok)
        if (request()->header('x-forwarded-proto') === 'https' || str_contains(config('app.url'), 'https://') || env('APP_ENV') == 'local') {
            URL::forceScheme('https');
        }
    }
}
