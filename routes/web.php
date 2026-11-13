<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockAuditController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Rute Web Aplikasi StockIn)
|--------------------------------------------------------------------------
|
| File ini mendefinisikan seluruh rute web yang digunakan oleh aplikasi StockIn,
| mencakup halaman publik, autentikasi pengguna, manajemen bisnis,
| serta fitur-fitur dashboard inventaris.
|
*/

// ==========================================
// RUTE PUBLIK & PENGATURAN BAHASA
// ==========================================

// Halaman landing page utama
Route::get('/', [LandingController::class, 'index'])->name('home');

// Mengubah preferensi bahasa antarmuka (ID / EN)
Route::post('/set-language', [LanguageController::class, 'set'])->name('language.set');

// ==========================================
// RUTE TAMU (HANYA PENGGUNA BELUM LOGIN)
// ==========================================
Route::middleware('guest')->group(function () {
    // Menampilkan halaman formulir login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Proses autentikasi login pengguna
    Route::post('/login', [AuthController::class, 'login']);

    // Menampilkan halaman pendaftaran akun baru
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // Proses penyimpanan data pendaftaran pengguna baru
    Route::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// RUTE PENGGUNA TERAUTENTIKASI (LOGIN)
// ==========================================
Route::middleware('auth')->group(function () {
    // Mengakhiri sesi pengguna (logout)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Menghapus akun pengguna beserta datanya
    Route::post('/account/delete', [AuthController::class, 'deleteAccount'])->name('account.delete');

    // Menampilkan daftar bisnis milik pengguna aktif
    Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
    // Menampilkan formulir pendaftaran bisnis baru
    Route::get('/businesses/create', [BusinessController::class, 'create'])->name('businesses.create');
    // Menyimpan data bisnis baru
    Route::post('/businesses', [BusinessController::class, 'store'])->name('businesses.store');
    // Menghapus entitas bisnis tertentu
    Route::post('/businesses/delete', [BusinessController::class, 'destroy'])->name('businesses.destroy');
});

// ==========================================
// RUTE DASHBOARD BISNIS INVENTARIS
// Membutuhkan autentikasi pengguna & validasi akses ke bisnis aktif
// ==========================================
Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'business'])->group(function () {
    // Ringkasan metrik statistik harian dan produk terlaris
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // Manajemen Produk (Katalog, Tambah, Ambil Data, Perbarui, Hapus)
    Route::get('/products', [ProductController::class, 'index'])->name('products');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/delete', [ProductController::class, 'destroy'])->name('products.destroy');

    // Tambah kategori produk baru
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');

    // Barang Masuk (Stock In)
    Route::get('/stock-in', [StockInController::class, 'index'])->name('stock-in');
    Route::post('/stock-in', [StockInController::class, 'store'])->name('stock-in.store');

    // Barang Keluar / Penjualan (Stock Out)
    Route::get('/stock-out', [StockOutController::class, 'index'])->name('stock-out');
    Route::post('/stock-out', [StockOutController::class, 'store'])->name('stock-out.store');

    // Audit Stok Fisik (Stock Opname)
    Route::get('/stock-audit', [StockAuditController::class, 'index'])->name('stock-audit');
    Route::post('/stock-audit', [StockAuditController::class, 'store'])->name('stock-audit.store');

    // Riwayat Seluruh Transaksi Inventaris
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Laporan Mutasi Barang & Laba
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    // Analitik Visual Grafik (Top 10 Produk Terjual)
    Route::get('/reports/analytics', [ReportController::class, 'analytics'])->name('reports.analytics');
    // Cetak dan Ekspor Halaman PDF Laporan
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    // Pengaturan Profil & Bisnis
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
