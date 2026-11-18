<?php

use App\Http\Controllers\Inclusive\AccommodationRegistryController;
use App\Http\Controllers\Inclusive\DifferentiatedPlanController;
use App\Http\Controllers\Inclusive\DisabilityScreeningController;
use App\Http\Controllers\Inclusive\IepController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// data on students with special needs. View: counselling, teachers
// and leadership (a teacher only for the classes they teach, see the
// controllers); change: counselling, student affairs and admin.
$inclusiveChange = Roles::only('bk', 'waka_kesiswaan', 'admin');

Route::middleware(['auth', Roles::only('bk', StudentDataAccess::TEACHER_ROLES, 'waka_kesiswaan', 'waka_kurikulum', Roles::PRINCIPAL, 'admin')])->group(function () use ($inclusiveChange) {
    Route::prefix('iep')->name('iep.')->group(function () use ($inclusiveChange) {
        Route::get('/', [IepController::class, 'index'])->name('index');
        Route::get('/create', [IepController::class, 'create'])->name('create')->middleware($inclusiveChange);
        Route::post('/', [IepController::class, 'store'])->name('store')->middleware($inclusiveChange);
        Route::get('/{iep}', [IepController::class, 'show'])->name('show');
        Route::get('/{iep}/edit', [IepController::class, 'edit'])->name('edit')->middleware($inclusiveChange);
        Route::put('/{iep}', [IepController::class, 'update'])->name('update')->middleware($inclusiveChange);
        Route::delete('/{iep}', [IepController::class, 'destroy'])->name('destroy')->middleware($inclusiveChange);
        Route::get('/{iep}/progress', [IepController::class, 'progress'])->name('progress');
    });

    Route::prefix('screening')->name('screening.')->group(function () use ($inclusiveChange) {
        Route::get('/', [DisabilityScreeningController::class, 'dashboard'])->name('dashboard');
        Route::get('/conduct', [DisabilityScreeningController::class, 'conductScreening'])->name('conduct')->middleware($inclusiveChange);
        Route::post('/', [DisabilityScreeningController::class, 'storeResult'])->name('store')->middleware($inclusiveChange);
        Route::get('/{result}', [DisabilityScreeningController::class, 'showResult'])->name('show');
    });

    Route::prefix('differentiation')->name('differentiation.')->group(function () use ($inclusiveChange) {
        Route::get('/', [DifferentiatedPlanController::class, 'index'])->name('index');
        Route::get('/library', [DifferentiatedPlanController::class, 'library'])->name('library');
        Route::get('/create', [DifferentiatedPlanController::class, 'create'])->name('create')->middleware($inclusiveChange);
        Route::post('/', [DifferentiatedPlanController::class, 'store'])->name('store')->middleware($inclusiveChange);
        Route::get('/{plan}', [DifferentiatedPlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [DifferentiatedPlanController::class, 'edit'])->name('edit')->middleware($inclusiveChange);
        Route::put('/{plan}', [DifferentiatedPlanController::class, 'update'])->name('update')->middleware($inclusiveChange);
    });

    Route::prefix('accommodations')->name('accommodations.')->group(function () use ($inclusiveChange) {
        Route::get('/', [AccommodationRegistryController::class, 'index'])->name('registry');
        Route::post('/', [AccommodationRegistryController::class, 'store'])->name('store')->middleware($inclusiveChange);
        Route::put('/{accommodation}', [AccommodationRegistryController::class, 'update'])->name('update')->middleware($inclusiveChange);
        Route::delete('/{accommodation}', [AccommodationRegistryController::class, 'destroy'])->name('destroy')->middleware($inclusiveChange);
        Route::get('/student/{student}', [AccommodationRegistryController::class, 'studentProfile'])->name('studentProfile');
    });
});
