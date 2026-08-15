<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HR\RecruitmentController;
use App\Http\Controllers\HR\CompensationController;
use App\Http\Controllers\HR\WellnessController;
use App\Http\Controllers\HR\TurnoverController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('recruitment')->name('recruitment.')->group(function () {
        Route::get('/kanban', [RecruitmentController::class, 'kanban'])->name('kanban');
        Route::get('/vacancies', [RecruitmentController::class, 'vacancies'])->name('vacancies');
    });

    Route::prefix('compensation')->name('compensation.')->group(function () {
        Route::get('/dashboard', [CompensationController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('wellness')->name('wellness.')->group(function () {
        Route::get('/', [WellnessController::class, 'index'])->name('index');
    });

    Route::prefix('turnover')->name('turnover.')->group(function () {
        Route::get('/dashboard', [TurnoverController::class, 'dashboard'])->name('dashboard');
    });
});