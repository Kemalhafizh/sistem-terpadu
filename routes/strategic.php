<?php

use App\Http\Controllers\Strategic\AnnualWorkplanController;
use App\Http\Controllers\Strategic\KpiController;
use App\Http\Controllers\Strategic\SatisfactionSurveyController;
use App\Http\Controllers\Strategic\StrategicPlanController;
use App\Http\Controllers\Strategic\SwotAnalysisController;
use Illuminate\Support\Facades\Route;

// These routes used to have no `auth` at all: anyone could read and create
// strategic plans, KPIs, workplans, SWOT analyses and surveys.
Route::middleware('auth')->prefix('strategic')->name('strategic.')->group(function () {
    // Anyone signed in (teachers, parents, students) may fill in a satisfaction survey.
    Route::get('survey/{id}/respond', [SatisfactionSurveyController::class, 'respond'])->name('survey.respond');
    Route::post('survey/{id}/respond', [SatisfactionSurveyController::class, 'storeResponse'])->name('survey.storeResponse');

    // Planning itself is for school leadership (admin/superadmin always pass RoleMiddleware).
    Route::middleware('role:kepala_sekolah,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas')->group(function () {
        Route::get('dashboard', [StrategicPlanController::class, 'dashboard'])->name('dashboard');
        Route::resource('plan', StrategicPlanController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('plan/{plan}/objectives', [StrategicPlanController::class, 'storeObjective'])->name('plan.objectives.store');
        Route::post('objectives/{objective}/kpis', [StrategicPlanController::class, 'storeKpi'])->name('objectives.kpis.store');

        Route::post('kpi/{id}/measure', [KpiController::class, 'storeMeasurement'])->name('kpi.measure');
        Route::get('kpi/{id}/report', [KpiController::class, 'report'])->name('kpi.report');

        Route::resource('workplan', AnnualWorkplanController::class)->only(['index', 'create', 'store', 'show']);

        Route::get('survey/{id}/results', [SatisfactionSurveyController::class, 'results'])->name('survey.results');
        Route::resource('survey', SatisfactionSurveyController::class)->only(['index', 'create', 'store', 'show']);

        Route::resource('swot', SwotAnalysisController::class)->only(['index', 'create', 'store', 'show']);
    });
});
