<?php

use App\Http\Controllers\Crisis\AntiBullyingController;
use App\Http\Controllers\Crisis\CrisisController;
use App\Http\Controllers\Crisis\EmergencyBroadcastController;
use App\Http\Controllers\Crisis\HeadcountController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// FASE 118: every route here was open to any signed-in account, bullying
// reports (victims' identities) included.
$crisisLeaders = [Roles::PRINCIPAL, 'waka_kesiswaan', 'waka_sarpras', 'admin'];

Route::middleware(['auth'])->prefix('crisis')->name('crisis.')->group(function () use ($crisisLeaders) {
    // Command center, incidents, protocols, emergency broadcast: crisis leadership.
    Route::middleware(Roles::only($crisisLeaders))->group(function () {
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
    });

    // Headcount during an incident: teachers count their class, leadership watches.
    Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin'))->group(function () {
        Route::get('/incident/{incident}/headcount', [HeadcountController::class, 'dashboard'])->name('headcount.dashboard');
        Route::post('/incident/{incident}/headcount', [HeadcountController::class, 'storeHeadcount'])->name('headcount.store');
    });

    // Anyone signed in may report bullying (listed as open_to_all in config/route_access.php).
    Route::get('/bullying/report', [AntiBullyingController::class, 'reportForm'])->name('bullying.report');
    Route::post('/bullying/report', [AntiBullyingController::class, 'storeReport'])->name('bullying.store');

    // Reading the reports is for counselling and student affairs.
    Route::middleware(Roles::only('bk', 'waka_kesiswaan', Roles::PRINCIPAL, 'admin'))->group(function () {
        Route::get('/bullying', [AntiBullyingController::class, 'index'])->name('bullying.index');
        Route::get('/bullying/statistics', [AntiBullyingController::class, 'statistics'])->name('bullying.statistics');
        Route::get('/bullying/{id}', [AntiBullyingController::class, 'show'])->name('bullying.show');
    });
});
