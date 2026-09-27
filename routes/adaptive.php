<?php

use App\Http\Controllers\Adaptive\CatController;
use App\Http\Controllers\Adaptive\IrtCalibrationController;
use App\Http\Controllers\Adaptive\LearningPathController;
use App\Http\Controllers\Adaptive\MasteryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('adaptive')->name('adaptive.')->group(function () {

    // IRT Calibration
    Route::get('calibration/dashboard', [IrtCalibrationController::class, 'dashboard'])->name('calibration.dashboard');
    Route::post('calibration/batch/{examId}', [IrtCalibrationController::class, 'batchCalibrate'])->name('calibration.batch');
    Route::get('calibration/item/{questionId}', [IrtCalibrationController::class, 'itemDetail'])->name('calibration.item');

    // CAT
    Route::get('cat/start/{examId}', [CatController::class, 'startExam'])->name('cat.start');
    Route::get('cat/take/{sessionId}', [CatController::class, 'takeExam'])->name('cat.take');
    Route::post('cat/process/{sessionId}', [CatController::class, 'processResponse'])->name('cat.process');
    Route::get('cat/result/{sessionId}', [CatController::class, 'result'])->name('cat.result');
    Route::get('cat/admin-sessions', function () {
        return view('adaptive.cat.admin-sessions');
    })->name('cat.admin-sessions');

    // Learning Path
    Route::get('learning-paths', [LearningPathController::class, 'index'])->name('learning-path.index');
    Route::get('learning-paths/{pathId}', [LearningPathController::class, 'show'])->name('learning-path.show');
    Route::post('learning-paths/generate/{studentId}/{subjectId}', [LearningPathController::class, 'generate'])->name('learning-path.generate');

    // Mastery
    Route::get('mastery/heatmap/{classroomId}', [MasteryController::class, 'heatmap'])->name('mastery.heatmap');
    Route::get('mastery/student/{studentId}', [MasteryController::class, 'studentProfile'])->name('mastery.student');
    Route::get('mastery/class/{classroomId}', [MasteryController::class, 'classOverview'])->name('mastery.class');
});
