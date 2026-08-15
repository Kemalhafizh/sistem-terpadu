<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\InternshipController;
use App\Http\Controllers\Student\CollegeCounselingController;
use App\Http\Controllers\Student\ShowcaseController;
use App\Http\Controllers\Student\MiniBankingController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('internships')->name('internships.')->group(function () {
        Route::get('/portal', [InternshipController::class, 'portal'])->name('portal');
        Route::get('/monitoring', [InternshipController::class, 'supervisorMonitoring'])->name('monitoring');
    });

    Route::prefix('college-counseling')->name('college_counseling.')->group(function () {
        Route::get('/match-engine', [CollegeCounselingController::class, 'matchEngine'])->name('match_engine');
        Route::get('/tracker', [CollegeCounselingController::class, 'myApplications'])->name('tracker');
    });

    Route::prefix('showcase')->name('showcase.')->group(function () {
        Route::get('/gallery', [ShowcaseController::class, 'gallery'])->name('gallery');
        Route::get('/my', [ShowcaseController::class, 'myShowcase'])->name('my');
    });

    Route::prefix('mini-bank')->name('mini_bank.')->group(function () {
        Route::get('/wallet', [MiniBankingController::class, 'walletView'])->name('wallet');
        Route::get('/teller', [MiniBankingController::class, 'tellerDashboard'])->name('teller');
    });
});
