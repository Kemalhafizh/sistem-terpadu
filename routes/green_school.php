<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Green\CarbonMetricController;
use App\Http\Controllers\Green\WasteManagementController;
use App\Http\Controllers\Green\EcoProjectController;
use App\Http\Controllers\Green\EcoChallengeController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('green-school')->group(function () {
        // Carbon Metrics
        Route::prefix('carbon')->name('carbon.')->group(function () {
            Route::get('/', [CarbonMetricController::class, 'index'])->name('dashboard');
            Route::post('/store', [CarbonMetricController::class, 'storeDailyData'])->name('store');
            Route::get('/reports', [CarbonMetricController::class, 'reports'])->name('reports');
        });

        // Waste Management
        Route::prefix('waste')->name('waste.')->group(function () {
            Route::get('/', [WasteManagementController::class, 'index'])->name('tracker');
            Route::post('/store', [WasteManagementController::class, 'storeLog'])->name('store');
            Route::get('/bank-sampah', [WasteManagementController::class, 'bankSampahSync'])->name('bank-sampah');
        });

        // Eco Projects
        Route::prefix('eco-projects')->name('eco_projects.')->group(function () {
            Route::get('/', [EcoProjectController::class, 'index'])->name('index');
            Route::get('/sdg', [EcoProjectController::class, 'sdgMapping'])->name('sdg');
        });

        // Eco Challenges
        Route::prefix('eco-challenges')->name('eco_challenges.')->group(function () {
            Route::get('/', [EcoChallengeController::class, 'index'])->name('index');
            Route::get('/leaderboard', [EcoChallengeController::class, 'leaderboard'])->name('leaderboard');
        });
    });
});
