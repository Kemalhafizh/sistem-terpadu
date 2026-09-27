<?php

use App\Http\Controllers\DataEngineering\ConditionalReportController;
use App\Http\Controllers\DataEngineering\DataQualityController;
use App\Http\Controllers\DataEngineering\EventStreamingController;
use App\Http\Controllers\DataEngineering\NlqController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/data-engineering/streams', [EventStreamingController::class, 'monitorDashboard'])->name('data_engineering.streams');
    Route::get('/data-engineering/replay', [EventStreamingController::class, 'streamLogs'])->name('data_engineering.replay');

    Route::get('/nlq/search', [NlqController::class, 'searchInterface'])->name('nlq.search');
    Route::post('/nlq/search', [NlqController::class, 'executeQuery']);
    Route::get('/nlq/dictionary', [NlqController::class, 'dictionaryMapping'])->name('nlq.dictionary');

    Route::get('/report-scheduler/manager', [ConditionalReportController::class, 'index'])->name('report_scheduler.manager');
    Route::get('/report-scheduler/create', [ConditionalReportController::class, 'create'])->name('report_scheduler.create');
    Route::post('/report-scheduler/store', [ConditionalReportController::class, 'store'])->name('report_scheduler.store');
    Route::get('/report-scheduler/logs', [ConditionalReportController::class, 'logs'])->name('report_scheduler.logs');
    Route::post('/report-scheduler/{id}/trigger', [ConditionalReportController::class, 'manualTrigger'])->name('report_scheduler.trigger');

    Route::get('/data-quality/health', [DataQualityController::class, 'healthDashboard'])->name('data_quality.health_dashboard');
    Route::get('/data-quality/resolution', [DataQualityController::class, 'resolutionCenter'])->name('data_quality.resolution_center');
    Route::post('/data-quality/anomaly/{id}/resolve', [DataQualityController::class, 'resolveAnomaly'])->name('data_quality.resolve_anomaly');
});
