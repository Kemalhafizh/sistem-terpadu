<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Alumni\AlumniProfileController;
use App\Http\Controllers\Alumni\CrowdfundCampaignController;
use App\Http\Controllers\Alumni\AlumniAchievementController;
use App\Http\Controllers\Alumni\EndowmentController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('alumni')->name('alumni.')->group(function () {
        Route::get('/', [AlumniProfileController::class, 'index'])->name('index');
        Route::get('/reunion-planner', [AlumniProfileController::class, 'reunionPlanner'])->name('reunionPlanner');
    });

    Route::prefix('crowdfunding')->name('crowdfunding.')->group(function () {
        Route::get('/', [CrowdfundCampaignController::class, 'index'])->name('index');
        Route::get('/donor-report', [CrowdfundCampaignController::class, 'donorReport'])->name('donorReport');
    });

    Route::prefix('hall-of-fame')->name('hall_of_fame.')->group(function () {
        Route::get('/', [AlumniAchievementController::class, 'hallOfFame'])->name('index');
        Route::get('/manager', [AlumniAchievementController::class, 'manager'])->name('manager');
        Route::get('/create', [AlumniAchievementController::class, 'create'])->name('create');
    });

    Route::prefix('endowment')->name('endowment.')->group(function () {
        Route::get('/', [EndowmentController::class, 'dashboard'])->name('dashboard');
        Route::get('/manager', [EndowmentController::class, 'pledgeManager'])->name('pledgeManager');
        Route::get('/recognition', [EndowmentController::class, 'recognition'])->name('recognition');
    });
});
