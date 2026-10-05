<?php

use App\Http\Controllers\Family\ClassFeedController;
use App\Http\Controllers\Family\FamilyLearningPlanController;
use App\Http\Controllers\Family\HomeworkCopilotController;
use App\Http\Controllers\Family\ParentSkillController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// FASE 118: split per audience. Parents work on their own children's tasks
// and plans; teachers and leadership manage the class side.
$familyParents = Roles::only(StudentDataAccess::PARENT_ROLES);
$familyStaff = Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin');

Route::middleware(['auth'])->group(function () use ($familyParents, $familyStaff) {
    Route::prefix('homework-copilot')->name('homework_copilot.')->group(function () use ($familyParents, $familyStaff) {
        Route::get('/', [HomeworkCopilotController::class, 'index'])->name('dashboard')->middleware($familyParents);
        Route::get('/{id}', [HomeworkCopilotController::class, 'showTaskDetails'])->name('task_guide')->middleware($familyParents);
        Route::post('/{id}/review', [HomeworkCopilotController::class, 'markAsReviewed'])->name('mark_reviewed')->middleware($familyParents);
        Route::post('/{id}/feedback', [HomeworkCopilotController::class, 'submitParentFeedback'])->name('submit_feedback')->middleware($familyParents);
        Route::get('/teacher/view', [HomeworkCopilotController::class, 'teacherView'])->name('teacher_view')->middleware($familyStaff);
    });
    Route::prefix('class-feed')->name('class_feed.')->group(function () use ($familyParents, $familyStaff) {
        Route::get('/', [ClassFeedController::class, 'timeline'])->name('timeline')
            ->middleware(Roles::only(StudentDataAccess::PARENT_ROLES, StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin'));
        Route::get('/create', [ClassFeedController::class, 'postCreator'])->name('post_creator')->middleware($familyStaff);
        Route::post('/', [ClassFeedController::class, 'storePost'])->name('store_post')->middleware($familyStaff);
        Route::post('/{id}/acknowledge', [ClassFeedController::class, 'acknowledgePost'])->name('acknowledge_post')->middleware($familyParents);
    });
    Route::prefix('family-plan')->name('family_plan.')->group(function () use ($familyParents) {
        Route::get('/', [FamilyLearningPlanController::class, 'planBuilder'])->name('builder')->middleware($familyParents);
        Route::post('/', [FamilyLearningPlanController::class, 'storePlan'])->name('store')->middleware($familyParents);
        Route::post('/{id}/progress', [FamilyLearningPlanController::class, 'updateProgress'])->name('update_progress')->middleware($familyParents);
        Route::get('/term-review', [FamilyLearningPlanController::class, 'termReview'])->name('term_review')
            ->middleware(Roles::only(StudentDataAccess::PARENT_ROLES, StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin'));
    });
    // Parents offer their skills; public relations and leadership coordinate.
    Route::prefix('parent-skills')->name('parent_skills.')->middleware(Roles::only(StudentDataAccess::PARENT_ROLES, 'waka_humas', Roles::PRINCIPAL, 'admin'))->group(function () {
        Route::get('/', [ParentSkillController::class, 'directory'])->name('directory');
        Route::get('/registration', [ParentSkillController::class, 'registration'])->name('registration');
        Route::post('/search', [ParentSkillController::class, 'searchDirectory'])->name('search');
        Route::get('/scheduler', [ParentSkillController::class, 'sessionScheduler'])->name('session_scheduler');
    });
});
