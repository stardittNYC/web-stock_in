<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes (Rute Perintah Konsol Artisan)
|--------------------------------------------------------------------------
|
| File ini digunakan untuk mendefinisikan perintah berbasis closure pada Artisan CLI.
|
*/

// Perintah konsol untuk menampilkan kutipan motivasi yang menginspirasi
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
