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

Route::middleware(['auth'])->group(function () {
    Route::get('/foundation/dashboard', [FoundationController::class, 'dashboard'])->name('foundation.dashboard');
    Route::resource('/foundation/units', FoundationUnitController::class, ['as' => 'foundation.units']);
    Route::resource('/foundation/reports', FoundationReportController::class, ['as' => 'foundation.reports']);
    Route::resource('/foundation/meetings', FoundationMeetingController::class, ['as' => 'foundation.meetings']);
    Route::get('/foundation/wakaf/{wakaf}/utilization', [WakafAssetController::class, 'utilization'])->name('foundation.wakaf.utilization');
    Route::resource('/foundation/wakaf', WakafAssetController::class, ['as' => 'foundation.wakaf']);
    Route::resource('/foundation/rotation', StaffRotationController::class, ['as' => 'foundation.rotation']);
    Route::get('/foundation/kpi/scorecard', [FoundationKpiController::class, 'scorecard'])->name('foundation.kpi.scorecard');
    Route::resource('/foundation/accreditation', AccreditationTrackerController::class, ['as' => 'foundation.accreditation']);
});
