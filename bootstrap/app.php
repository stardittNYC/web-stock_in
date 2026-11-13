<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Inisialisasi dan Konfigurasi Aplikasi Laravel
|--------------------------------------------------------------------------
|
| Mengonfigurasi rute web, rute konsol, health check, middleware global,
| serta penanganan pengecualian aplikasi StockIn.
|
*/

return Application::configure(basePath: dirname(__DIR__))
    // Konfigurasi routing aplikasi
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Konfigurasi middleware aplikasi
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan middleware penentu bahasa (locale) ke grup 'web'
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Daftarkan alias middleware 'business' untuk validasi hak akses bisnis aktif
        $middleware->alias([
            'business' => \App\Http\Middleware\EnsureBusinessAccess::class,
        ]);
    })
    // Konfigurasi penanganan exception
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
