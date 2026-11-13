<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk mengatur bahasa aplikasi (lokalisasi) berdasarkan pilihan yang tersimpan di sesi.
 */
class SetLocale
{
    /**
     * Menerapkan locale aktif ('id' atau 'en') dan membagikannya ke template Blade.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil pilihan bahasa dari sesi pengguna, dengan default bahasa Indonesia ('id')
        $lang = $request->session()->get('language', 'id');

        // Validasi agar pilihan bahasa terbatas hanya 'id' atau 'en'
        if (! in_array($lang, ['id', 'en'], true)) {
            $lang = 'id';
        }

        // Terapkan locale ke aplikasi Laravel
        App::setLocale($lang);

        // Bagikan variabel bahasa aktif ke seluruh template Blade
        View::share('lang', $lang);

        // Lanjutkan request
        return $next($request);
    }
}
