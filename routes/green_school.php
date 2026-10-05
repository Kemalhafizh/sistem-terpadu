<?php

use App\Http\Controllers\Green\CarbonMetricController;
use App\Http\Controllers\Green\EcoChallengeController;
use App\Http\Controllers\Green\EcoProjectController;
use App\Http\Controllers\Green\WasteManagementController;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

// FASE 118: carbon, waste and eco projects are managed by operations staff and
// leadership (change: operations staff and admin); the eco challenges and
// their leaderboard are for everyone in the school.
$greenView = Roles::only('staf_tu', 'waka_sarpras', Roles::PRINCIPAL, 'admin');
$greenChange = Roles::only('staf_tu', 'waka_sarpras', 'admin');

Route::middleware(['auth'])->group(function () use ($greenView, $greenChange) {
    Route::prefix('green-school')->group(function () use ($greenView, $greenChange) {
        // Carbon Metrics
        Route::prefix('carbon')->name('carbon.')->middleware($greenView)->group(function () use ($greenChange) {
            Route::get('/', [CarbonMetricController::class, 'index'])->name('dashboard');
            Route::post('/store', [CarbonMetricController::class, 'storeDailyData'])->name('store')->middleware($greenChange);
            Route::get('/reports', [CarbonMetricController::class, 'reports'])->name('reports');
        });

        // Waste Management
        Route::prefix('waste')->name('waste.')->middleware($greenView)->group(function () use ($greenChange) {
            Route::get('/', [WasteManagementController::class, 'index'])->name('tracker');
            Route::post('/store', [WasteManagementController::class, 'storeLog'])->name('store')->middleware($greenChange);
            Route::get('/bank-sampah', [WasteManagementController::class, 'bankSampahSync'])->name('bank-sampah');
        });

        // Eco Projects
        Route::prefix('eco-projects')->name('eco_projects.')->middleware($greenView)->group(function () {
            Route::get('/', [EcoProjectController::class, 'index'])->name('index');
            Route::get('/sdg', [EcoProjectController::class, 'sdgMapping'])->name('sdg');
        });

        // Eco Challenges
        Route::prefix('eco-challenges')->name('eco_challenges.')->middleware(Roles::only(Roles::MEMBERS))->group(function () {
            Route::get('/', [EcoChallengeController::class, 'index'])->name('index');
            Route::get('/leaderboard', [EcoChallengeController::class, 'leaderboard'])->name('leaderboard');
        });
    });
});
