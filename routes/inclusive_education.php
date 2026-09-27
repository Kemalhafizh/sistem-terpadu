<?php

use App\Http\Controllers\Inclusive\AccommodationRegistryController;
use App\Http\Controllers\Inclusive\DifferentiatedPlanController;
use App\Http\Controllers\Inclusive\DisabilityScreeningController;
use App\Http\Controllers\Inclusive\IepController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('iep')->name('iep.')->group(function () {
        Route::get('/', [IepController::class, 'index'])->name('index');
        Route::get('/create', [IepController::class, 'create'])->name('create');
        Route::post('/', [IepController::class, 'store'])->name('store');
        Route::get('/{iep}', [IepController::class, 'show'])->name('show');
        Route::get('/{iep}/edit', [IepController::class, 'edit'])->name('edit');
        Route::put('/{iep}', [IepController::class, 'update'])->name('update');
        Route::delete('/{iep}', [IepController::class, 'destroy'])->name('destroy');
        Route::get('/{iep}/progress', [IepController::class, 'progress'])->name('progress');
    });

    Route::prefix('screening')->name('screening.')->group(function () {
        Route::get('/', [DisabilityScreeningController::class, 'dashboard'])->name('dashboard');
        Route::get('/conduct', [DisabilityScreeningController::class, 'conductScreening'])->name('conduct');
        Route::post('/', [DisabilityScreeningController::class, 'storeResult'])->name('store');
        Route::get('/{result}', [DisabilityScreeningController::class, 'showResult'])->name('show');
    });

    Route::prefix('differentiation')->name('differentiation.')->group(function () {
        Route::get('/', [DifferentiatedPlanController::class, 'index'])->name('index');
        Route::get('/library', [DifferentiatedPlanController::class, 'library'])->name('library');
        Route::get('/create', [DifferentiatedPlanController::class, 'create'])->name('create');
        Route::post('/', [DifferentiatedPlanController::class, 'store'])->name('store');
        Route::get('/{plan}', [DifferentiatedPlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [DifferentiatedPlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [DifferentiatedPlanController::class, 'update'])->name('update');
    });

    Route::prefix('accommodations')->name('accommodations.')->group(function () {
        Route::get('/', [AccommodationRegistryController::class, 'index'])->name('registry');
        Route::post('/', [AccommodationRegistryController::class, 'store'])->name('store');
        Route::put('/{accommodation}', [AccommodationRegistryController::class, 'update'])->name('update');
        Route::delete('/{accommodation}', [AccommodationRegistryController::class, 'destroy'])->name('destroy');
        Route::get('/student/{student}', [AccommodationRegistryController::class, 'studentProfile'])->name('studentProfile');
    });
});
