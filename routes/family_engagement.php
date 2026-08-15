<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Family\HomeworkCopilotController;
use App\Http\Controllers\Family\ClassFeedController;
use App\Http\Controllers\Family\FamilyLearningPlanController;
use App\Http\Controllers\Family\ParentSkillController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('homework-copilot')->name('homework_copilot.')->group(function () {
        Route::get('/', [HomeworkCopilotController::class, 'index'])->name('dashboard');
        Route::get('/{id}', [HomeworkCopilotController::class, 'showTaskDetails'])->name('task_guide');
        Route::post('/{id}/review', [HomeworkCopilotController::class, 'markAsReviewed'])->name('mark_reviewed');
        Route::post('/{id}/feedback', [HomeworkCopilotController::class, 'submitParentFeedback'])->name('submit_feedback');
        Route::get('/teacher/view', [HomeworkCopilotController::class, 'teacherView'])->name('teacher_view');
    });

    Route::prefix('class-feed')->name('class_feed.')->group(function () {
        Route::get('/', [ClassFeedController::class, 'timeline'])->name('timeline');
        Route::get('/create', [ClassFeedController::class, 'postCreator'])->name('post_creator');
        Route::post('/', [ClassFeedController::class, 'storePost'])->name('store_post');
        Route::post('/{id}/acknowledge', [ClassFeedController::class, 'acknowledgePost'])->name('acknowledge_post');
    });

    Route::prefix('family-plan')->name('family_plan.')->group(function () {
        Route::get('/', [FamilyLearningPlanController::class, 'planBuilder'])->name('builder');
        Route::post('/', [FamilyLearningPlanController::class, 'storePlan'])->name('store');
        Route::post('/{id}/progress', [FamilyLearningPlanController::class, 'updateProgress'])->name('update_progress');
        Route::get('/term-review', [FamilyLearningPlanController::class, 'termReview'])->name('term_review');
    });

    Route::prefix('parent-skills')->name('parent_skills.')->group(function () {
        Route::get('/', [ParentSkillController::class, 'directory'])->name('directory');
        Route::get('/registration', [ParentSkillController::class, 'registration'])->name('registration');
        Route::post('/search', [ParentSkillController::class, 'searchDirectory'])->name('search');
        Route::get('/scheduler', [ParentSkillController::class, 'sessionScheduler'])->name('session_scheduler');
    });
});
