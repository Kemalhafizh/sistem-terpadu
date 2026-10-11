<?php

use App\Http\Controllers\Crisis\AntiBullyingController;
use App\Models\BullyingReport;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

// Only the anti-bullying reports remain here (FASE 119 removed the crisis
// command center, broadcasts and headcounts). FASE 118: the reports carry
// victims' identities, so reading them is for counselling and leadership.
Route::middleware(['auth'])->prefix('crisis')->name('crisis.')->group(function () {
    // Anyone signed in may report bullying (listed as open_to_all in config/route_access.php).
    Route::get('/bullying/report', [AntiBullyingController::class, 'reportForm'])->name('bullying.report');
    Route::post('/bullying/report', [AntiBullyingController::class, 'storeReport'])->name('bullying.store');

    // Reading the reports is for counselling and student affairs.
    Route::middleware(Roles::only(BullyingReport::READER_ROLES))->group(function () {
        Route::get('/bullying', [AntiBullyingController::class, 'index'])->name('bullying.index');
        Route::get('/bullying/statistics', [AntiBullyingController::class, 'statistics'])->name('bullying.statistics');
        Route::get('/bullying/{id}', [AntiBullyingController::class, 'show'])->name('bullying.show');
    });
});
