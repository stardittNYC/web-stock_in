<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider utama untuk bootstrap aplikasi StockIn.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan binding layanan aplikasi ke service container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Mem-bootstrap konfigurasi atau layanan global aplikasi saat inisialisasi.
     */
    public function boot(): void
    {
        //
    }
}
