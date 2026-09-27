<?php

use App\Http\Controllers\Foundation\AccreditationTrackerController;
use App\Http\Controllers\Foundation\FoundationController;
use App\Http\Controllers\Foundation\FoundationKpiController;
use App\Http\Controllers\Foundation\FoundationMeetingController;
use App\Http\Controllers\Foundation\FoundationReportController;
use App\Http\Controllers\Foundation\FoundationUnitController;
use App\Http\Controllers\Foundation\StaffRotationController;
use App\Http\Controllers\Foundation\WakafAssetController;
use Illuminate\Support\Facades\Route;

// Foundation (yayasan) governance: school leadership only. `->names()` gives
// foundation.units.index etc. — the old ['as' => 'foundation.units'] produced
// foundation.units.units.index, so every redirect after store() was a 500.
// The controllers implement index/create/store/show only.
Route::middleware(['auth', 'role:kepala_sekolah'])->prefix('foundation')->name('foundation.')->group(function () {
    Route::get('dashboard', [FoundationController::class, 'dashboard'])->name('dashboard');
    Route::get('kpi/scorecard', [FoundationKpiController::class, 'scorecard'])->name('kpi.scorecard');
    Route::get('wakaf/{wakaf}/utilization', [WakafAssetController::class, 'utilization'])->name('wakaf.utilization');

    $only = ['index', 'create', 'store', 'show'];
    Route::resource('units', FoundationUnitController::class)->only($only);
    Route::resource('reports', FoundationReportController::class)->only($only);
    Route::resource('meetings', FoundationMeetingController::class)->only($only);
    Route::resource('wakaf', WakafAssetController::class)->only($only);
    Route::resource('rotation', StaffRotationController::class)->only($only);
    Route::resource('accreditation', AccreditationTrackerController::class)->only($only);
});
