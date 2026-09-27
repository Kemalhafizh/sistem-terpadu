<?php

use App\Http\Controllers\Microlearning\DigitalTranscriptController;
use App\Http\Controllers\Microlearning\GamifiedQuizController;
use App\Http\Controllers\Microlearning\MicroLessonController;
use App\Http\Controllers\Microlearning\SpacedRepetitionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('micro-lessons', MicroLessonController::class);
    Route::post('micro-lessons/{id}/publish', [MicroLessonController::class, 'publish'])->name('micro-lessons.publish');

    Route::prefix('gamified-quizzes')->name('gamified-quizzes.')->group(function () {
        Route::get('/', [GamifiedQuizController::class, 'index'])->name('dashboard');
        Route::get('/create', [GamifiedQuizController::class, 'create'])->name('create');
        Route::post('/', [GamifiedQuizController::class, 'store'])->name('store');
        Route::get('/{id}/host', [GamifiedQuizController::class, 'host'])->name('host');
        Route::get('/join', [GamifiedQuizController::class, 'join'])->name('join');
        Route::get('/{id}/live', [GamifiedQuizController::class, 'liveView'])->name('live');
        Route::get('/{id}/results', [GamifiedQuizController::class, 'results'])->name('results');
    });

    Route::prefix('spaced-repetition')->name('spaced-repetition.')->group(function () {
        Route::get('/deck', [SpacedRepetitionController::class, 'deck'])->name('deck');
        Route::get('/review/{id}', [SpacedRepetitionController::class, 'review'])->name('review');
        Route::post('/review/{id}', [SpacedRepetitionController::class, 'submitReview'])->name('submitReview');
        Route::get('/stats', [SpacedRepetitionController::class, 'stats'])->name('stats');
    });

    Route::prefix('transcripts')->name('transcripts.')->group(function () {
        Route::get('/', [DigitalTranscriptController::class, 'index'])->name('index');
        Route::get('/student/{id}', [DigitalTranscriptController::class, 'show'])->name('student');
        Route::get('/badges', [DigitalTranscriptController::class, 'badgeGallery'])->name('badges');
    });
});

Route::get('/transcripts/verify/{hash}', [DigitalTranscriptController::class, 'verify'])->name('transcripts.verify');
