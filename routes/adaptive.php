<?php

use App\Http\Controllers\Adaptive\CatController;
use App\Http\Controllers\Adaptive\IrtCalibrationController;
use App\Http\Controllers\Adaptive\LearningPathController;
use App\Http\Controllers\Adaptive\MasteryController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// FASE 118: split per audience. Students take the adaptive test and see their
// own path; teachers and leadership calibrate items and read mastery.
$adaptiveStaff = Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin');

Route::middleware(['auth'])->prefix('adaptive')->name('adaptive.')->group(function () use ($adaptiveStaff) {
    // IRT Calibration
    Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'waka_kurikulum', Roles::PRINCIPAL, 'admin'))->group(function () {
        Route::get('calibration/dashboard', [IrtCalibrationController::class, 'dashboard'])->name('calibration.dashboard');
        Route::post('calibration/batch/{examId}', [IrtCalibrationController::class, 'batchCalibrate'])->name('calibration.batch');
        Route::get('calibration/item/{questionId}', [IrtCalibrationController::class, 'itemDetail'])->name('calibration.item');
    });

    // CAT: the student taking the test
    Route::middleware(Roles::only(StudentDataAccess::STUDENT_ROLES))->group(function () {
        Route::get('cat/start/{examId}', [CatController::class, 'startExam'])->name('cat.start');
        Route::get('cat/take/{sessionId}', [CatController::class, 'takeExam'])->name('cat.take');
        Route::post('cat/process/{sessionId}', [CatController::class, 'processResponse'])->name('cat.process');
        Route::get('cat/result/{sessionId}', [CatController::class, 'result'])->name('cat.result');
    });
    Route::get('cat/admin-sessions', function () {
        return view('adaptive.cat.admin-sessions');
    })->name('cat.admin-sessions')->middleware($adaptiveStaff);

    // Learning Path
    Route::middleware(Roles::only(StudentDataAccess::STUDENT_ROLES, StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin'))->group(function () {
        Route::get('learning-paths', [LearningPathController::class, 'index'])->name('learning-path.index');
        Route::get('learning-paths/{pathId}', [LearningPathController::class, 'show'])->name('learning-path.show');
    });
    Route::post('learning-paths/generate/{studentId}/{subjectId}', [LearningPathController::class, 'generate'])->name('learning-path.generate')->middleware($adaptiveStaff);

    // Mastery: class views for staff; one student's profile also for that student and their parent
    Route::get('mastery/heatmap/{classroomId}', [MasteryController::class, 'heatmap'])->name('mastery.heatmap')->middleware($adaptiveStaff);
    Route::get('mastery/student/{studentId}', [MasteryController::class, 'studentProfile'])->name('mastery.student')
        ->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin', StudentDataAccess::STUDENT_ROLES, StudentDataAccess::PARENT_ROLES));
    Route::get('mastery/class/{classroomId}', [MasteryController::class, 'classOverview'])->name('mastery.class')->middleware($adaptiveStaff);
});
