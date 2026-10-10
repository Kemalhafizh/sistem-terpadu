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
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// FASE 118: split per prefix. Each page goes to the role it was built for;
// executive dashboards to leadership only.
$diamondStaff = Roles::only(StudentDataAccess::TEACHER_ROLES, StudentDataAccess::STAFF_ROLES, Roles::LEADERSHIP, 'admin');
$diamondTeachers = Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin');
$diamondPrincipal = Roles::only(Roles::PRINCIPAL, 'admin');

Route::middleware(['auth', 'verified'])->group(function () use ($diamondStaff, $diamondTeachers) {

    // === FASE 63: Mobile-First Experience ===
    Route::prefix('mobile')->name('mobile.')->group(function () {
        Route::get('teacher', [MobileTeacherDashboardController::class, 'index'])->name('teacher.dashboard')->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES));
        Route::get('student', [MobileStudentHubController::class, 'index'])->name('student.hub')->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES));
        Route::get('parent', [MobileParentActionController::class, 'index'])->name('parent.actions')->middleware(Roles::only(StudentDataAccess::PARENT_ROLES));
        Route::post('parent/report-absence', [MobileParentActionController::class, 'reportAbsence'])->name('parent.report-absence')->middleware(Roles::only(StudentDataAccess::PARENT_ROLES));
    });

    // === FASE 64: Advanced Frontend Components & Interactive UI ===
    Route::prefix('interactive-ui')->name('interactive.')->group(function () use ($diamondStaff, $diamondTeachers) {
        Route::get('schedule-builder', [ScheduleBuilderController::class, 'index'])->name('schedule.index')->middleware(Roles::only('waka_kurikulum', Roles::PRINCIPAL, 'admin'));
        Route::post('schedule-builder', [ScheduleBuilderController::class, 'store'])->name('schedule.store')->middleware(Roles::only('waka_kurikulum', Roles::PRINCIPAL, 'admin'));
        Route::post('schedule-builder/validate', [ScheduleBuilderController::class, 'validateDraft'])->name('schedule.validate')->middleware(Roles::only('waka_kurikulum', Roles::PRINCIPAL, 'admin'));
        Route::post('schedule-builder/{draft}/publish', [ScheduleBuilderController::class, 'publish'])->name('schedule.publish')->middleware(Roles::only('waka_kurikulum', Roles::PRINCIPAL, 'admin'));

        Route::get('seating-chart', [ClassroomLayoutController::class, 'index'])->name('seating.index')->middleware($diamondTeachers);
        Route::post('seating-chart', [ClassroomLayoutController::class, 'store'])->name('seating.store')->middleware($diamondTeachers);
        Route::post('seating-chart/assign', [ClassroomLayoutController::class, 'assignSeat'])->name('seating.assign')->middleware($diamondTeachers);
        Route::get('seating-chart/{layout}/heatmap', [ClassroomLayoutController::class, 'heatmap'])->name('seating.heatmap')->middleware($diamondTeachers);

        Route::get('kanban', [KanbanBoardController::class, 'index'])->name('kanban.index')->middleware($diamondStaff);
        Route::post('kanban/boards', [KanbanBoardController::class, 'storeBoard'])->name('kanban.store-board')->middleware($diamondStaff);
        Route::post('kanban/tasks', [KanbanBoardController::class, 'storeTask'])->name('kanban.store-task')->middleware($diamondStaff);
        Route::post('kanban/tasks/{task}/move', [KanbanBoardController::class, 'moveTask'])->name('kanban.move-task')->middleware($diamondStaff);

        Route::get('whiteboard', [WhiteboardSessionController::class, 'index'])->name('whiteboard.index')->middleware($diamondTeachers);
        Route::post('whiteboard/sessions', [WhiteboardSessionController::class, 'createSession'])->name('whiteboard.create')->middleware($diamondTeachers);
        Route::post('whiteboard/sessions/{session}/save', [WhiteboardSessionController::class, 'saveCanvasState'])->name('whiteboard.save')->middleware($diamondTeachers);
    });

    // === FASE 65: Advanced Dashboard & Data Visualization ===
    Route::prefix('dashboards')->name('dashboards.')->group(function () {
        Route::get('executive-command-center', [ExecutiveCommandCenterController::class, 'index'])->name('executive.index')->middleware(Roles::only(Roles::LEADERSHIP, 'admin'));
        Route::post('executive-command-center/refresh', [ExecutiveCommandCenterController::class, 'refresh'])->name('executive.refresh')->middleware(Roles::only(Roles::LEADERSHIP, 'admin'));

        Route::get('teacher-360', [Teacher360DashboardController::class, 'index'])->name('teacher-360.index')->middleware(Roles::only(Roles::PRINCIPAL, 'waka_kurikulum', 'admin'));
        Route::get('teacher-360/{teacher}', [Teacher360DashboardController::class, 'showTeacher'])->name('teacher-360.show')->middleware(Roles::only(Roles::PRINCIPAL, 'waka_kurikulum', 'admin'));

        Route::get('student-progress-cockpit', [StudentProgressCockpitController::class, 'index'])->name('student-cockpit.index')->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'bk', Roles::LEADERSHIP, 'admin'));
        Route::get('student-progress-cockpit/{student}', [StudentProgressCockpitController::class, 'showStudent'])->name('student-cockpit.show')->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'bk', Roles::LEADERSHIP, 'admin'));

        Route::get('financial-health-scoreboard', [FinancialHealthScoreboardController::class, 'index'])->name('financial-scoreboard.index')->middleware(Roles::only(Roles::PRINCIPAL, 'staf_tu', 'admin'));
    });

    // === FASE 68: Accessibility, Localization & Universal Design ===
    Route::prefix('accessibility')->name('accessibility.')->group(function () {
        Route::get('wcag-scan', [WcagComplianceController::class, 'index'])->name('wcag.index')->middleware(Roles::only('admin'));
        Route::post('wcag-scan', [WcagComplianceController::class, 'scan'])->name('wcag.scan')->middleware(Roles::only('admin'));

        Route::get('translations', [LocalizationController::class, 'index'])->name('translations.index')->middleware(Roles::only('admin'));
        Route::post('translations', [LocalizationController::class, 'store'])->name('translations.store')->middleware(Roles::only('admin'));

        Route::get('voice-commands', [VoiceNavigationController::class, 'index'])->name('voice.index')->middleware(Roles::only(Roles::MEMBERS));

        Route::get('theme-settings', [ThemePreferenceController::class, 'index'])->name('theme.index')->middleware(Roles::only(Roles::MEMBERS));
        Route::post('theme-settings', [ThemePreferenceController::class, 'update'])->name('theme.update')->middleware(Roles::only(Roles::MEMBERS));
    });
});
