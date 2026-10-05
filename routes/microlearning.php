<?php

use App\Http\Controllers\Microlearning\DigitalTranscriptController;
use App\Http\Controllers\Microlearning\GamifiedQuizController;
use App\Http\Controllers\Microlearning\MicroLessonController;
use App\Http\Controllers\Microlearning\SpacedRepetitionController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// FASE 118: split per audience. Teachers author lessons and host quizzes;
// students study, join quizzes and review their own deck.
$microStaff = Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin');
$microLearners = Roles::only(StudentDataAccess::STUDENT_ROLES, StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin');

Route::middleware(['auth'])->group(function () use ($microStaff, $microLearners) {
    Route::resource('micro-lessons', MicroLessonController::class)->except(['index', 'show'])->middleware($microStaff);
    Route::resource('micro-lessons', MicroLessonController::class)->only(['index', 'show'])->middleware($microLearners);
    Route::post('micro-lessons/{id}/publish', [MicroLessonController::class, 'publish'])->name('micro-lessons.publish')->middleware($microStaff);

    Route::prefix('gamified-quizzes')->name('gamified-quizzes.')->group(function () use ($microStaff, $microLearners) {
        Route::get('/', [GamifiedQuizController::class, 'index'])->name('dashboard')->middleware($microStaff);
        Route::get('/create', [GamifiedQuizController::class, 'create'])->name('create')->middleware($microStaff);
        Route::post('/', [GamifiedQuizController::class, 'store'])->name('store')->middleware($microStaff);
        Route::get('/{id}/host', [GamifiedQuizController::class, 'host'])->name('host')->middleware($microStaff);
        Route::get('/join', [GamifiedQuizController::class, 'join'])->name('join')->middleware($microLearners);
        Route::get('/{id}/live', [GamifiedQuizController::class, 'liveView'])->name('live')->middleware($microLearners);
        Route::get('/{id}/results', [GamifiedQuizController::class, 'results'])->name('results')->middleware($microStaff);
    });

    Route::prefix('spaced-repetition')->name('spaced-repetition.')->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES))->group(function () {
        Route::get('/deck', [SpacedRepetitionController::class, 'deck'])->name('deck');
        Route::get('/review/{id}', [SpacedRepetitionController::class, 'review'])->name('review');
        Route::post('/review/{id}', [SpacedRepetitionController::class, 'submitReview'])->name('submitReview');
        Route::get('/stats', [SpacedRepetitionController::class, 'stats'])->name('stats');
    });

    Route::prefix('transcripts')->name('transcripts.')->group(function () {
        Route::get('/', [DigitalTranscriptController::class, 'index'])->name('index')
            ->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'bk', 'staf_tu', Roles::LEADERSHIP, 'admin'));
        // One student's transcript: staff, the student and their parent (ownership in the controller).
        Route::get('/student/{id}', [DigitalTranscriptController::class, 'show'])->name('student')
            ->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'bk', 'staf_tu', Roles::LEADERSHIP, 'admin', StudentDataAccess::STUDENT_ROLES, StudentDataAccess::PARENT_ROLES));
        Route::get('/badges', [DigitalTranscriptController::class, 'badgeGallery'])->name('badges')->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES));
    });
});

// Public verification of a transcript hash (no account needed).
Route::get('/transcripts/verify/{hash}', [DigitalTranscriptController::class, 'verify'])->name('transcripts.verify');
