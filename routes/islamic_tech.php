<?php

use App\Http\Controllers\Islamic\TahsinRecordController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// Tahsin records are for teachers and leadership (FASE 118). FASE 119 removed
// the rest of this file (Islamic events, dawah hub, spiritual portfolio).
$islamicStaff = Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin');

Route::middleware(['auth'])->group(function () use ($islamicStaff) {
    Route::prefix('tahsin')->name('tahsin.')->middleware($islamicStaff)->group(function () {
        Route::get('/', [TahsinRecordController::class, 'index'])->name('index');
        Route::get('/analytics', [TahsinRecordController::class, 'analytics'])->name('analytics');
    });
});
