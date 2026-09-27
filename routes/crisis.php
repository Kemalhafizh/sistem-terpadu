<?php

use App\Http\Controllers\Crisis\AntiBullyingController;
use App\Http\Controllers\Crisis\CrisisController;
use App\Http\Controllers\Crisis\EmergencyBroadcastController;
use App\Http\Controllers\Crisis\HeadcountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('crisis')->name('crisis.')->group(function () {
    Route::get('/command-center', [CrisisController::class, 'commandCenter'])->name('command_center');
    Route::get('/declare', [CrisisController::class, 'declareIncident'])->name('declare');
    Route::post('/declare', [CrisisController::class, 'storeIncident'])->name('store');
    Route::get('/incident/{id}', [CrisisController::class, 'showIncident'])->name('show');
    Route::post('/incident/{id}/response', [CrisisController::class, 'storeResponse'])->name('store_response');
    Route::post('/incident/{id}/resolve', [CrisisController::class, 'resolveIncident'])->name('resolve');
    Route::get('/incident/{id}/post-mortem', [CrisisController::class, 'postMortemReport'])->name('post_mortem');

    Route::get('/protocols', [CrisisController::class, 'protocols'])->name('protocols.index');
    Route::get('/protocols/create', [CrisisController::class, 'createProtocol'])->name('protocols.create');
    Route::post('/protocols', [CrisisController::class, 'storeProtocol'])->name('protocols.store');

    Route::get('/broadcast', [EmergencyBroadcastController::class, 'index'])->name('broadcast.index');
    Route::get('/broadcast/create', [EmergencyBroadcastController::class, 'create'])->name('broadcast.create');
    Route::post('/broadcast', [EmergencyBroadcastController::class, 'store'])->name('broadcast.store');
    Route::get('/broadcast/{id}', [EmergencyBroadcastController::class, 'show'])->name('broadcast.show');

    Route::get('/incident/{incident}/headcount', [HeadcountController::class, 'dashboard'])->name('headcount.dashboard');
    Route::post('/incident/{incident}/headcount', [HeadcountController::class, 'storeHeadcount'])->name('headcount.store');

    Route::get('/bullying/report', [AntiBullyingController::class, 'reportForm'])->name('bullying.report');
    Route::post('/bullying/report', [AntiBullyingController::class, 'storeReport'])->name('bullying.store');
    Route::get('/bullying', [AntiBullyingController::class, 'index'])->name('bullying.index');
    Route::get('/bullying/statistics', [AntiBullyingController::class, 'statistics'])->name('bullying.statistics');
    Route::get('/bullying/{id}', [AntiBullyingController::class, 'show'])->name('bullying.show');
});
