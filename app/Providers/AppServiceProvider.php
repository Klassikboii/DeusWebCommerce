<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <--- WAJIB IMPORT
use Illuminate\Support\Facades\URL; // Jangan lupa import ini di atas

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
        Paginator::useBootstrapFive();
        // Paksa Laravel menggunakan HTTPS untuk semua link/asset jika di production
         if (env('APP_ENV') !== 'local') {
        URL::forceScheme('https');
    }
    }
}
