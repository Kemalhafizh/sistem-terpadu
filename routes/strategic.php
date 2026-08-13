<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Strategic\{StrategicPlanController, KpiController, AnnualWorkplanController, SatisfactionSurveyController, SwotAnalysisController};

Route::prefix('strategic')->name('strategic.')->group(function () {
    Route::get('dashboard', [StrategicPlanController::class, 'dashboard'])->name('dashboard');
    Route::resource('plan', StrategicPlanController::class);
    
    Route::post('kpi/{id}/measure', [KpiController::class, 'storeMeasurement'])->name('kpi.measure');
    Route::get('kpi/{id}/report', [KpiController::class, 'report'])->name('kpi.report');
    
    Route::resource('workplan', AnnualWorkplanController::class);
    
    Route::get('survey/{id}/respond', [SatisfactionSurveyController::class, 'respond'])->name('survey.respond');
    Route::post('survey/{id}/respond', [SatisfactionSurveyController::class, 'storeResponse'])->name('survey.storeResponse');
    Route::get('survey/{id}/results', [SatisfactionSurveyController::class, 'results'])->name('survey.results');
    Route::resource('survey', SatisfactionSurveyController::class);
    
    Route::resource('swot', SwotAnalysisController::class);
});
