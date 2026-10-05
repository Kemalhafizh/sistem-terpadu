<?php

use App\Http\Controllers\Islamic\DawahContentController;
use App\Http\Controllers\Islamic\IslamicEventController;
use App\Http\Controllers\Islamic\SpiritualPortfolioController;
use App\Http\Controllers\Islamic\TahsinRecordController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// FASE 118: tahsin records and the spiritual portfolio dashboard are for teachers
// and leadership; the Islamic calendar, prayer times and dawah hub are for
// everyone in the school.
$islamicStaff = Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin');

Route::middleware(['auth'])->group(function () use ($islamicStaff) {
    Route::prefix('tahsin')->name('tahsin.')->middleware($islamicStaff)->group(function () {
        Route::get('/', [TahsinRecordController::class, 'index'])->name('index');
        Route::get('/analytics', [TahsinRecordController::class, 'analytics'])->name('analytics');
    });

    Route::prefix('islamic-events')->name('islamic_events.')->middleware(Roles::only(Roles::MEMBERS))->group(function () {
        Route::get('/calendar', [IslamicEventController::class, 'calendar'])->name('calendar');
        Route::get('/prayer-times', [IslamicEventController::class, 'prayerTimes'])->name('prayer_times');
    });

    Route::prefix('spiritual')->name('spiritual.')->middleware($islamicStaff)->group(function () {
        Route::get('/dashboard', [SpiritualPortfolioController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('dawah')->name('dawah.')->middleware(Roles::only(Roles::MEMBERS))->group(function () {
        Route::get('/hub', [DawahContentController::class, 'hub'])->name('hub');
    });
});
