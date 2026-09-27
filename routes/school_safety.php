<?php

use App\Http\Controllers\Safety\CctvIncidentController;
use App\Http\Controllers\Safety\EmergencyAlertController;
use App\Http\Controllers\Safety\GeoFencingController;
use App\Http\Controllers\Safety\SafetyDrillController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('surveillance')->name('cctv.')->group(function () {
        Route::get('/', [CctvIncidentController::class, 'index'])->name('index');
    });
    Route::prefix('emergency-alerts')->name('emergency_alerts.')->group(function () {
        Route::get('/', [EmergencyAlertController::class, 'index'])->name('index');
    });
    Route::prefix('safety-drills')->name('safety_drills.')->group(function () {
        Route::get('/', [SafetyDrillController::class, 'index'])->name('index');
    });
    Route::prefix('geofencing')->name('geofencing.')->group(function () {
        Route::get('/', [GeoFencingController::class, 'index'])->name('index');
    });
});
