<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Islamic\TahsinRecordController;
use App\Http\Controllers\Islamic\IslamicEventController;
use App\Http\Controllers\Islamic\SpiritualPortfolioController;
use App\Http\Controllers\Islamic\DawahContentController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('tahsin')->name('tahsin.')->group(function () {
        Route::get('/', [TahsinRecordController::class, 'index'])->name('index');
        Route::get('/analytics', [TahsinRecordController::class, 'analytics'])->name('analytics');
    });

    Route::prefix('islamic-events')->name('islamic_events.')->group(function () {
        Route::get('/calendar', [IslamicEventController::class, 'calendar'])->name('calendar');
        Route::get('/prayer-times', [IslamicEventController::class, 'prayerTimes'])->name('prayer_times');
    });

    Route::prefix('spiritual')->name('spiritual.')->group(function () {
        Route::get('/dashboard', [SpiritualPortfolioController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('dawah')->name('dawah.')->group(function () {
        Route::get('/hub', [DawahContentController::class, 'hub'])->name('hub');
    });
});