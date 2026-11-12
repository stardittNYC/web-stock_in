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

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::post('/set-language', [LanguageController::class, 'set'])->name('language.set');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/account/delete', [AuthController::class, 'deleteAccount'])->name('account.delete');

    Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
    Route::get('/businesses/create', [BusinessController::class, 'create'])->name('businesses.create');
    Route::post('/businesses', [BusinessController::class, 'store'])->name('businesses.store');
    Route::post('/businesses/delete', [BusinessController::class, 'destroy'])->name('businesses.destroy');
});

Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'business'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::get('/products', [ProductController::class, 'index'])->name('products');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/delete', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');

    Route::get('/stock-in', [StockInController::class, 'index'])->name('stock-in');
    Route::post('/stock-in', [StockInController::class, 'store'])->name('stock-in.store');

    Route::get('/stock-out', [StockOutController::class, 'index'])->name('stock-out');
    Route::post('/stock-out', [StockOutController::class, 'store'])->name('stock-out.store');

    Route::get('/stock-audit', [StockAuditController::class, 'index'])->name('stock-audit');
    Route::post('/stock-audit', [StockAuditController::class, 'store'])->name('stock-audit.store');

    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/analytics', [ReportController::class, 'analytics'])->name('reports.analytics');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
