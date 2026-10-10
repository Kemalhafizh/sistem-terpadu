<?php

use App\Http\Controllers\Alumni\AlumniAchievementController;
use App\Http\Controllers\Alumni\AlumniProfileController;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

// FASE 118: alumni relations are run by public relations and the office; the
// `alumni` role is not given access yet. FASE 119 removed fundraising and the
// endowment.
Route::middleware(['auth', Roles::only('waka_humas', 'staf_tu', Roles::PRINCIPAL, 'admin')])->group(function () {
    Route::prefix('alumni')->name('alumni.')->group(function () {
        Route::get('/', [AlumniProfileController::class, 'index'])->name('index');
        Route::get('/reunion-planner', [AlumniProfileController::class, 'reunionPlanner'])->name('reunionPlanner');
    });

    Route::prefix('hall-of-fame')->name('hall_of_fame.')->group(function () {
        Route::get('/', [AlumniAchievementController::class, 'hallOfFame'])->name('index');
        Route::get('/manager', [AlumniAchievementController::class, 'manager'])->name('manager');
        Route::get('/create', [AlumniAchievementController::class, 'create'])->name('create');
    });
});
