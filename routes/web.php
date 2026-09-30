<?php

use App\Http\Controllers\CacheMonitorController;
use App\Http\Controllers\CloudflareCacheController;
use App\Http\Controllers\CloudflareDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('cloudflare.dashboard');
});

Route::prefix('cloudflare')->name('cloudflare.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [
        CloudflareDashboardController::class,
        'index'
    ])->name('dashboard');

    // Cache Management
    Route::get('/cache', [
        CloudflareCacheController::class,
        'index'
    ])->name('cache');

    Route::post('/cache/purge-all', [
        CloudflareCacheController::class,
        'purgeAll'
    ])->name('cache.purge-all');

    Route::post('/cache/purge-url', [
        CloudflareCacheController::class,
        'purgeUrl'
    ])->name('cache.purge-url');

    Route::get('/cache/history', [
        CloudflareCacheController::class,
        'history'
    ])->name('cache.history');

    // URL Monitor
    Route::get('/monitor', [
        CacheMonitorController::class,
        'index'
    ])->name('monitor');

    Route::post('/monitor/test', [
        CacheMonitorController::class,
        'test'
    ])->name('monitor.test');
});