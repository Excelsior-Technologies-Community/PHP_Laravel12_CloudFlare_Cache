<?php

use App\Http\Controllers\CacheMonitorController;
use App\Http\Controllers\CloudflareCacheController;
use App\Http\Controllers\CloudflareDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('cloudflare.dashboard');
});

Route::prefix('cloudflare')->name('cloudflare.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [
        CloudflareDashboardController::class,
        'index'
    ])->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Cache Management
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Purge History
    |--------------------------------------------------------------------------
    */

    Route::get('/cache/history', [
        CloudflareCacheController::class,
        'history'
    ])->name('cache.history');

    Route::get('/cache/history/export', [
        CloudflareCacheController::class,
        'exportHistory'
    ])->name('cache.history.export');


    /*
    |--------------------------------------------------------------------------
    | Cache Entry Management
    |--------------------------------------------------------------------------
    */

    Route::get('/cache/entries', [
        CloudflareCacheController::class,
        'entries'
    ])->name('cache.entries');

    Route::delete('/cache/entries', [
        CloudflareCacheController::class,
        'deleteEntries'
    ])->name('cache.entries.delete');


    /*
    |--------------------------------------------------------------------------
    | URL Monitor
    |--------------------------------------------------------------------------
    */

    Route::get('/monitor', [
        CacheMonitorController::class,
        'index'
    ])->name('monitor');

    Route::post('/monitor/test', [
        CacheMonitorController::class,
        'test'
    ])->name('monitor.test');

    Route::get('/monitor/export', [
        CacheMonitorController::class,
        'export'
    ])->name('monitor.export');
});