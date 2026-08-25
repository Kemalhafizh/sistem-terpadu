<?php

use App\Http\Controllers\Accessibility\LocalizationController;
use App\Http\Controllers\Accessibility\ThemePreferenceController;
use App\Http\Controllers\Accessibility\VoiceNavigationController;
use App\Http\Controllers\Accessibility\WcagComplianceController;
use App\Http\Controllers\Dashboard\ExecutiveCommandCenterController;
use App\Http\Controllers\Dashboard\FinancialHealthScoreboardController;
use App\Http\Controllers\Dashboard\StudentProgressCockpitController;
use App\Http\Controllers\Dashboard\Teacher360DashboardController;
use App\Http\Controllers\InteractiveUi\ClassroomLayoutController;
use App\Http\Controllers\InteractiveUi\KanbanBoardController;
use App\Http\Controllers\InteractiveUi\ScheduleBuilderController;
use App\Http\Controllers\InteractiveUi\WhiteboardSessionController;
use App\Http\Controllers\Mobile\MobileParentActionController;
use App\Http\Controllers\Mobile\MobileStudentHubController;
use App\Http\Controllers\Mobile\MobileTeacherDashboardController;
use App\Http\Controllers\Publishing\DataStoryController;
use App\Http\Controllers\Publishing\DigitalMagazineController;
use App\Http\Controllers\Publishing\PodcastController;
use App\Http\Controllers\Publishing\VideoStudioController;
use App\Http\Controllers\Workflow\ApprovalChainController;
use App\Http\Controllers\Workflow\ProcessMiningController;
use App\Http\Controllers\Workflow\SlaDashboardController;
use App\Http\Controllers\Workflow\WorkflowDesignerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    // === FASE 63: Mobile-First Experience ===
    Route::prefix('mobile')->name('mobile.')->group(function () {
        Route::get('teacher', [MobileTeacherDashboardController::class, 'index'])->name('teacher.dashboard');
        Route::get('student', [MobileStudentHubController::class, 'index'])->name('student.hub');
        Route::get('parent', [MobileParentActionController::class, 'index'])->name('parent.actions');
        Route::post('parent/report-absence', [MobileParentActionController::class, 'reportAbsence'])->name('parent.report-absence');
    });

    // === FASE 64: Advanced Frontend Components & Interactive UI ===
    Route::prefix('interactive-ui')->name('interactive.')->group(function () {
        Route::get('schedule-builder', [ScheduleBuilderController::class, 'index'])->name('schedule.index');
        Route::post('schedule-builder', [ScheduleBuilderController::class, 'store'])->name('schedule.store');
        Route::post('schedule-builder/validate', [ScheduleBuilderController::class, 'validateDraft'])->name('schedule.validate');
        Route::post('schedule-builder/{draft}/publish', [ScheduleBuilderController::class, 'publish'])->name('schedule.publish');

        Route::get('seating-chart', [ClassroomLayoutController::class, 'index'])->name('seating.index');
        Route::post('seating-chart', [ClassroomLayoutController::class, 'store'])->name('seating.store');
        Route::post('seating-chart/assign', [ClassroomLayoutController::class, 'assignSeat'])->name('seating.assign');
        Route::get('seating-chart/{layout}/heatmap', [ClassroomLayoutController::class, 'heatmap'])->name('seating.heatmap');

        Route::get('kanban', [KanbanBoardController::class, 'index'])->name('kanban.index');
        Route::post('kanban/boards', [KanbanBoardController::class, 'storeBoard'])->name('kanban.store-board');
        Route::post('kanban/tasks', [KanbanBoardController::class, 'storeTask'])->name('kanban.store-task');
        Route::post('kanban/tasks/{task}/move', [KanbanBoardController::class, 'moveTask'])->name('kanban.move-task');

        Route::get('whiteboard', [WhiteboardSessionController::class, 'index'])->name('whiteboard.index');
        Route::post('whiteboard/sessions', [WhiteboardSessionController::class, 'createSession'])->name('whiteboard.create');
        Route::post('whiteboard/sessions/{session}/save', [WhiteboardSessionController::class, 'saveCanvasState'])->name('whiteboard.save');
    });

    // === FASE 65: Advanced Dashboard & Data Visualization ===
    Route::prefix('dashboards')->name('dashboards.')->group(function () {
        Route::get('executive-command-center', [ExecutiveCommandCenterController::class, 'index'])->name('executive.index');
        Route::post('executive-command-center/refresh', [ExecutiveCommandCenterController::class, 'refresh'])->name('executive.refresh');

        Route::get('teacher-360', [Teacher360DashboardController::class, 'index'])->name('teacher-360.index');
        Route::get('teacher-360/{teacher}', [Teacher360DashboardController::class, 'showTeacher'])->name('teacher-360.show');

        Route::get('student-progress-cockpit', [StudentProgressCockpitController::class, 'index'])->name('student-cockpit.index');
        Route::get('student-progress-cockpit/{student}', [StudentProgressCockpitController::class, 'showStudent'])->name('student-cockpit.show');

        Route::get('financial-health-scoreboard', [FinancialHealthScoreboardController::class, 'index'])->name('financial-scoreboard.index');
    });

    // === FASE 66: Content Delivery & Digital Publishing ===
    Route::prefix('publishing')->name('publishing.')->group(function () {
        Route::get('magazines', [DigitalMagazineController::class, 'index'])->name('magazines.index');
        Route::post('magazines', [DigitalMagazineController::class, 'store'])->name('magazines.store');
        Route::post('magazines/subscribe', [DigitalMagazineController::class, 'subscribe'])->name('magazines.subscribe');

        Route::get('video-studio', [VideoStudioController::class, 'index'])->name('video.index');
        Route::post('video-studio/upload', [VideoStudioController::class, 'upload'])->name('video.upload');

        Route::get('podcasts', [PodcastController::class, 'index'])->name('podcasts.index');
        Route::post('podcasts', [PodcastController::class, 'store'])->name('podcasts.store');

        Route::get('data-stories', [DataStoryController::class, 'index'])->name('stories.index');
        Route::post('data-stories', [DataStoryController::class, 'store'])->name('stories.store');
    });

    // === FASE 67: Advanced Workflow Automation & Process Mining ===
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('designer', [WorkflowDesignerController::class, 'index'])->name('designer.index');
        Route::post('designer', [WorkflowDesignerController::class, 'store'])->name('designer.store');

        Route::get('approvals', [ApprovalChainController::class, 'index'])->name('approvals.index');
        Route::post('approvals/{approvalRequest}/decision', [ApprovalChainController::class, 'submitDecision'])->name('approvals.decision');

        Route::get('process-mining', [ProcessMiningController::class, 'index'])->name('mining.index');
        Route::get('sla-dashboard', [SlaDashboardController::class, 'index'])->name('sla.index');
    });

    // === FASE 68: Accessibility, Localization & Universal Design ===
    Route::prefix('accessibility')->name('accessibility.')->group(function () {
        Route::get('wcag-scan', [WcagComplianceController::class, 'index'])->name('wcag.index');
        Route::post('wcag-scan', [WcagComplianceController::class, 'scan'])->name('wcag.scan');

        Route::get('translations', [LocalizationController::class, 'index'])->name('translations.index');
        Route::post('translations', [LocalizationController::class, 'store'])->name('translations.store');

        Route::get('voice-commands', [VoiceNavigationController::class, 'index'])->name('voice.index');

        Route::get('theme-settings', [ThemePreferenceController::class, 'index'])->name('theme.index');
        Route::post('theme-settings', [ThemePreferenceController::class, 'update'])->name('theme.update');
    });
});
