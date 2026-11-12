<?php

/*
|--------------------------------------------------------------------------
| Application Helpers
|--------------------------------------------------------------------------
|
| Global helper functions used across the StockIn application. They mirror
| the behaviour of the original procedural helpers (includes/language.php
| and includes/currency.php).
|
*/

if (! function_exists('t')) {
    /**
     * Translate a StockIn language key (group "app").
     *
     * Falls back to the raw key when no translation is found so untranslated
     * strings never render as "app.something".
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
     * Format a numeric amount as a currency string (same rules as the
     * original formatMoneyPHP helper).
     */
    function format_money($amount, $currency = 'IDR'): string
    {
        $formats = [
            'IDR' => ['decimal' => ',', 'thousands' => '.'],
            'USD' => ['decimal' => '.', 'thousands' => ','],
            'EUR' => ['decimal' => ',', 'thousands' => '.'],
            'JPY' => ['decimal' => '.', 'thousands' => ','],
            'GBP' => ['decimal' => '.', 'thousands' => ','],
            'CNY' => ['decimal' => '.', 'thousands' => ','],
        ];

        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'JPY' => '¥',
            'GBP' => '£',
            'CNY' => 'CN¥',
        ];

        $currency = strtoupper((string) $currency);

        $format = $formats[$currency] ?? $formats['IDR'];
        $symbol = $symbols[$currency] ?? 'Rp';

        $formattedAmount = number_format(
            (float) $amount,
            2,
            $format['decimal'],
            $format['thousands']
        );

        return $symbol.' '.$formattedAmount;
    }
}

if (! function_exists('upload_url')) {
    /**
     * Build a public URL for a file stored under public/uploads.
     */
    function upload_url(string $path): string
    {
        return asset('uploads/'.ltrim($path, '/'));
    }
}
