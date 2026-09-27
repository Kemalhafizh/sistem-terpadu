<?php

use App\Http\Controllers\Knowledge\KnowledgeArticleController;
use App\Http\Controllers\Knowledge\LessonStudyController;
use App\Http\Controllers\Knowledge\PlcDiscussionController;
use App\Http\Controllers\Knowledge\PlcGroupController;
use App\Http\Controllers\Knowledge\SharedResourceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('knowledge')->name('knowledge.')->group(function () {
    Route::resource('articles', KnowledgeArticleController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('articles/{id}/like', [KnowledgeArticleController::class, 'toggleLike'])->name('articles.like');
    Route::post('articles/{id}/comment', [KnowledgeArticleController::class, 'storeComment'])->name('articles.comment');

    Route::resource('lesson-study', LessonStudyController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('lesson-study/{id}/observe', [LessonStudyController::class, 'submitObservation'])->name('lesson-study.observe');
    Route::post('lesson-study/{id}/advance', [LessonStudyController::class, 'advancePhase'])->name('lesson-study.advance');

    Route::resource('plc', PlcGroupController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('plc/{groupId}/discussions/{discussionId}', [PlcDiscussionController::class, 'show'])->name('plc.discussions.show');
    Route::post('plc/{groupId}/discussions', [PlcDiscussionController::class, 'store'])->name('plc.discussions.store');
    Route::post('plc/discussions/{discussionId}/reply', [PlcDiscussionController::class, 'reply'])->name('plc.discussions.reply');

    Route::resource('resources', SharedResourceController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('resources/{id}/rate', [SharedResourceController::class, 'rate'])->name('resources.rate');
});
