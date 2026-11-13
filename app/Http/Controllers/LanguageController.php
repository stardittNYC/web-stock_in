<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller untuk mengelola preferensi bahasa (lokalisasi) antarmuka pengguna.
 */
class LanguageController extends Controller
{
    /**
     * Mengatur bahasa aktif aplikasi dan mengalihkan kembali ke halaman sebelumnya.
     */
    public function set(Request $request)
    {
        // Menangkap input pilihan bahasa dengan default bahasa Indonesia ('id')
        $lang = $request->input('language', 'id');

        // Memvalidasi agar kode bahasa yang masuk hanya 'id' atau 'en'
        if (! in_array($lang, ['id', 'en'], true)) {
            $lang = 'id';
        }

        // Menyimpan pilihan bahasa pengguna ke dalam variabel sesi
        $request->session()->put('language', $lang);

        // Menangkap URL halaman asal untuk proses pengalihan kembali
        $redirect = (string) $request->input('redirect', '/');

        // Memvalidasi URL redirect untuk mencegah serangan Open Redirect ke situs luar
        if (
            $redirect === ''
            || str_starts_with($redirect, '//')
            || str_contains($redirect, '://')
            || str_contains($redirect, "\n")
            || str_contains($redirect, "\r")
        ) {
            $redirect = '/';
        }

        // Mengalihkan kembali pengguna ke URL yang dituju
        return redirect()->to($redirect);
    }
}
