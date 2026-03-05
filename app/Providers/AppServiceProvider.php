<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Pastikan ini ada

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Kosongkan saja jika belum ada layanan yang didaftarkan
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cukup tulis ini satu kali saja Maang
        Paginator::useBootstrapFive();
    }
}