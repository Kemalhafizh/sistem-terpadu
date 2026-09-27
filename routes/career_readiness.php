<?php

use App\Http\Controllers\Student\CollegeCounselingController;
use App\Http\Controllers\Student\InternshipController;
use App\Http\Controllers\Student\MiniBankingController;
use App\Http\Controllers\Student\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('internships')->name('internships.')->group(function () {
        Route::get('/portal', [InternshipController::class, 'portal'])->name('portal');
        Route::get('/monitoring', [InternshipController::class, 'supervisorMonitoring'])->name('monitoring');
    });

    Route::prefix('college-counseling')->name('college_counseling.')->group(function () {
        Route::get('/match-engine', [CollegeCounselingController::class, 'matchEngine'])->name('match_engine');
        Route::get('/tracker', [CollegeCounselingController::class, 'myApplications'])->name('tracker');
    });

    // Galeri Karya Siswa — only gallery and my had routes before; create,
    // store, endorse and the public profile were unreachable.
    Route::prefix('showcase')->name('showcase.')->group(function () {
        Route::get('/gallery', [ShowcaseController::class, 'gallery'])->name('gallery');
        Route::get('/my', [ShowcaseController::class, 'myShowcase'])->name('my');
        Route::get('/create', [ShowcaseController::class, 'create'])->name('create');
        Route::post('/', [ShowcaseController::class, 'store'])->name('store');
        Route::get('/students/{user}', [ShowcaseController::class, 'publicProfile'])->name('profile');
        Route::get('/{showcase}', [ShowcaseController::class, 'show'])->name('show');
        Route::get('/{showcase}/edit', [ShowcaseController::class, 'edit'])->name('edit');
        Route::put('/{showcase}', [ShowcaseController::class, 'update'])->name('update');
        Route::delete('/{showcase}', [ShowcaseController::class, 'destroy'])->name('destroy');
        Route::post('/{showcase}/endorse', [ShowcaseController::class, 'endorse'])->name('endorse')->middleware('throttle:20,1');
    });

    Route::prefix('mini-bank')->name('mini_bank.')->group(function () {
        Route::get('/wallet', [MiniBankingController::class, 'walletView'])->name('wallet');
        Route::get('/teller', [MiniBankingController::class, 'tellerDashboard'])->name('teller');
    });
});
