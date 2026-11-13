<?php

/*
|--------------------------------------------------------------------------
| Application Helpers (Fungsi Pembantu Aplikasi)
|--------------------------------------------------------------------------
|
| Kumpulan fungsi helper global yang digunakan di seluruh aplikasi StockIn.
| Berfungsi menerjemahkan teks, memformat mata uang, dan menyusun URL berkas unggahan.
|
*/

if (! function_exists('t')) {
    /**
     * Menerjemahkan kunci bahasa aplikasi dari grup terjemahan 'app'.
     * Jika terjemahan tidak ditemukan, kembalikan teks kunci asli agar tidak muncul 'app.kunci'.
     */
    function t(?string $key, array $replace = []): string
    {
        if ($key === null || $key === '') {
            return '';
        }

        $full = 'app.'.$key;
        $value = trans($full, $replace);

        return $value === $full ? $key : $value;
    }
}

if (! function_exists('format_money')) {
    /**
     * Memformat angka nominal menjadi string mata uang lengkap dengan simbol dan pemisah ribuan.
     * Mengikuti format yang sama seperti fungsi formatMoneyPHP pada versi web sebelumnya.
     */
    function format_money($amount, $currency = 'IDR'): string
    {
        // Pemisah desimal dan ribuan untuk tiap jenis mata uang
        $formats = [
            'IDR' => ['decimal' => ',', 'thousands' => '.'],
            'USD' => ['decimal' => '.', 'thousands' => ','],
            'EUR' => ['decimal' => ',', 'thousands' => '.'],
            'JPY' => ['decimal' => '.', 'thousands' => ','],
            'GBP' => ['decimal' => '.', 'thousands' => ','],
            'CNY' => ['decimal' => '.', 'thousands' => ','],
        ];

        // Pemetaan simbol prefix mata uang internasional
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'JPY' => '¥',
            'GBP' => '£',
            'CNY' => 'CN¥',
        ];

        // Memastikan kode mata uang berformat huruf kapital
        $currency = strtoupper((string) $currency);

        // Menentukan aturan format dan simbol sesuai mata uang atau default IDR
        $format = $formats[$currency] ?? $formats['IDR'];
        $symbol = $symbols[$currency] ?? 'Rp';

        // Memformat angka dengan 2 digit desimal serta pemisah ribuan
        $formattedAmount = number_format(
            (float) $amount,
            2,
            $format['decimal'],
            $format['thousands']
        );

        // Mengembalikan hasil kombinasi simbol mata uang dan angka terformat
        return $symbol.' '.$formattedAmount;
    }
}

if (! function_exists('upload_url')) {
    /**
     * Membangun URL publik lengkap untuk berkas yang tersimpan di bawah folder public/uploads.
     */
    function upload_url(string $path): string
    {
        return asset('uploads/'.ltrim($path, '/'));
    }
}
