<?php

use App\Http\Controllers\Academic\AcademicCycleController;
use App\Http\Controllers\AcademicEventController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AccountProvisioningController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\AssetMaintenanceTicketController;
use App\Http\Controllers\Admin\FeatureFlagController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\PdplPrivacyController;
use App\Http\Controllers\Admin\PositionCatalogController;
use App\Http\Controllers\Admin\RbacAuditController;
use App\Http\Controllers\Admin\ScheduledReportController;
use App\Http\Controllers\Admin\SchoolBrandingController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SecurityDashboardController;
use App\Http\Controllers\Admin\SecurityTwoFactorController;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnalyticsExportController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetLoanController;
use App\Http\Controllers\AssetMaintenanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\ForceChangePasswordController;
use App\Http\Controllers\Auth\GoogleSsoController;
use App\Http\Controllers\Auth\MobileSsoBridgeController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\Communication\MessagingController;
use App\Http\Controllers\Communication\NotificationCenterController;
use App\Http\Controllers\Communication\ResourceBookingController;
use App\Http\Controllers\Content\ExportEngineController;
use App\Http\Controllers\CounselingController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DailyAssessmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElearningController;
use App\Http\Controllers\ElearningSecureController;
use App\Http\Controllers\EventAttendanceController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\Finance\CharityController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\Financial\BudgetPlanningController;
use App\Http\Controllers\Financial\ScholarshipController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\Growth\P5AssessmentController;
use App\Http\Controllers\Growth\PtcSchedulerController;
use App\Http\Controllers\HRD\EmployeeController;
use App\Http\Controllers\HRD\FingerprintImportController;
use App\Http\Controllers\HRD\MyHRDController;
use App\Http\Controllers\HRD\PayrollController;
use App\Http\Controllers\HumanCapital\Evaluation360Controller;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\IncomingLetterController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LibraryCirculationController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MeetingNoteController;
use App\Http\Controllers\MpkController;
use App\Http\Controllers\MutabaahController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\OsisController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PointCategoryController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ProfileChangeRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpmbController;
use App\Http\Controllers\Student\CertificateController;
use App\Http\Controllers\Student\StudentPortalController;
use App\Http\Controllers\StudentAcademicController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentFinanceController;
use App\Http\Controllers\StudentLeaveController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TahfidzController;
use App\Http\Controllers\TeachingJournalController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TracerStudyController;
use App\Http\Controllers\UksController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\WellKnownController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\WorkflowController;
use App\Models\BudgetPlan;
use App\Models\Evaluation360Cycle;
use App\Models\PtcSession;
use App\Models\TeachingJournal;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;
use Knuckles\Scribe\ScribeServiceProvider;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');

// Scribe API docs. Scribe is a dev dependency, so production (composer
// install --no-dev) serves no docs, same as before.
if (class_exists(ScribeServiceProvider::class)) {
    Route::get('docs', [ApiDocsController::class, 'index'])->name('scribe');
    Route::get('docs.postman', [ApiDocsController::class, 'postman'])->name('scribe.postman');
    Route::get('docs.openapi', [ApiDocsController::class, 'openapi'])->name('scribe.openapi');
}
Route::get('/tentang-kami', [PublicSiteController::class, 'about'])->name('public.about');
Route::get('/kontak', [PublicSiteController::class, 'contact'])->name('public.contact');
Route::get('/fasilitas', [PublicSiteController::class, 'facilities'])->name('public.facilities');
Route::get('/prestasi', [PublicSiteController::class, 'achievements'])->name('public.achievements');
Route::get('/direktori-staff', [PublicSiteController::class, 'staffDirectory'])->name('public.staff-directory');
Route::get('/ppdb-info', [PublicSiteController::class, 'ppdbInfo'])->name('public.ppdb-info');
Route::get('/virtual-tour', [PublicSiteController::class, 'virtualTour'])->name('public.virtual-tour');
Route::get('/page/{slug}', [PublicSiteController::class, 'page'])->name('public.page');
Route::get('/berita', [PublicSiteController::class, 'posts'])->name('public.posts');
Route::get('/berita/{slug}', [PublicSiteController::class, 'post'])->name('public.post');
Route::get('/galeri', [PublicSiteController::class, 'gallery'])->name('public.gallery');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/dashboard/save-layout', [DashboardController::class, 'saveLayout'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.save-layout');

// --- SPMB (Sistem Penerimaan Murid Baru) - PUBLIC AREA ---
Route::prefix('spmb')->middleware('throttle:spmb')->group(function () {
    Route::get('/', function () {
        return view('spmb.welcome');
    })->name('spmb.welcome');
    Route::get('/register', [SpmbController::class, 'create'])->name('spmb.create');
    Route::post('/register', [SpmbController::class, 'store'])->name('spmb.store');
    Route::get('/success', [SpmbController::class, 'success'])->name('spmb.success');
    Route::get('/status', [SpmbController::class, 'checkStatus'])->name('spmb.status');
    Route::get('/announcement', [SpmbController::class, 'publicAnnouncement'])->name('spmb.announcement');
    Route::get('/print-card/{registration}', [SpmbController::class, 'printCard'])->name('spmb.print-card');
    Route::get('/print-card/{registration}/photo', [SpmbController::class, 'printCardPhoto'])->name('spmb.print-card.photo');
});

// Verifikasi QR Surat Keluar Resmi (Publik, Non-PII)
Route::get('letters/verify/{token}', [LetterController::class, 'verify'])->name('letters.verify');

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware('auth')->group(function () {

    // =========================================================
    // UNIVERSAL â€” Semua user yang login bisa akses
    // =========================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // K4: Pembaruan kontak langsung & pengajuan perubahan data identitas terverifikasi
    Route::middleware(Roles::only(Roles::MEMBERS, 'bendahara', 'pustakawan', 'petugas_uks', 'alumni', 'merchant'))->group(function () {
        Route::patch('/profile/contact', [ProfileChangeRequestController::class, 'updateContact'])->name('profile.contact.update');
        Route::get('/profile/change-requests', [ProfileChangeRequestController::class, 'index'])->name('profile.change-requests.index');
        Route::post('/profile/change-requests', [ProfileChangeRequestController::class, 'store'])->name('profile.change-requests.store');
        Route::post('/profile/change-requests/{changeRequest}/cancel', [ProfileChangeRequestController::class, 'cancel'])->name('profile.change-requests.cancel');
        Route::get('/profile/change-requests/{changeRequest}/evidence', [ProfileChangeRequestController::class, 'downloadEvidence'])->name('profile.change-requests.evidence');
    });

    // Rute Kalender Universal & Endpoint JSON FullCalendar API
    Route::get('calendar', [AcademicEventController::class, 'index'])->name('calendar.index');
    Route::get('api/academic-events', [AcademicEventController::class, 'apiEvents'])->name('api.academic-events');

    // Library (read-only catalog untuk semua)
    Route::get('library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('library/{libraryBook}/download', [LibraryController::class, 'download'])->name('library.download');
    Route::get('library/{libraryBook}/read', [LibraryController::class, 'readBook'])->name('library.read');
    Route::get('library/student-card/{student}', [LibraryController::class, 'studentCard'])->name('library.student-card');
    Route::get('library/reading-history', [LibraryController::class, 'studentReadingHistory'])->name('library.reading-history');
    Route::get('library/recommendations', [LibraryController::class, 'recommendations'])->name('library.recommendations');

    // Alumni Directory & Network Map (read-only untuk semua)
    Route::get('alumni/directory', [AlumniController::class, 'directory'])->name('alumni.directory');
    Route::get('alumni/network-map', [AlumniController::class, 'alumniNetworkMap'])->name('alumni.network-map');
    Route::get('alumni/profile/{alumni}', [AlumniController::class, 'show'])->name('alumni.show');

    // Keislaman â€” baca data tahfidz & mutaba'ah (semua user)
    Route::prefix('tahfidz')->name('tahfidz.')->group(function () {
        Route::get('/dashboard', [TahfidzController::class, 'dashboard'])->name('dashboard');
        Route::get('/leaderboard', [TahfidzController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/bulk-input', [TahfidzController::class, 'bulkInputForm'])->name('bulk-input');
        Route::post('/bulk-input', [TahfidzController::class, 'storeBulk'])->name('store-bulk');
        Route::get('/target-setting', [TahfidzController::class, 'targetSettingForm'])->name('target-setting');
        Route::post('/target-setting', [TahfidzController::class, 'storeTargets'])->name('store-targets');
        Route::get('/export-pdf/{student}', [TahfidzController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{student}', [TahfidzController::class, 'show'])->name('show');
    });

    Route::prefix('mutabaah')->name('mutabaah.')->group(function () {
        Route::get('/', [MutabaahController::class, 'index'])->name('index');
        Route::get('/input-harian', [MutabaahController::class, 'inputHarian'])->name('input-harian');
        Route::post('/daily-log', [MutabaahController::class, 'storeDailyLog'])->name('store-daily-log');
        Route::get('/rekap-mingguan', [MutabaahController::class, 'rekapMingguan'])->name('rekap-mingguan');
        Route::get('/rekap-kelas/{classroom}', [MutabaahController::class, 'rekapKelas'])->name('rekap-kelas');
        Route::post('/weekly-comment/{summary}', [MutabaahController::class, 'storeWaliKelasComment'])->name('wali-kelas-comment');
        Route::post('/weekly-acknowledge/{summary}', [MutabaahController::class, 'parentAcknowledge'])->name('parent-acknowledge');
        Route::get('/parent-view/{student}', [MutabaahController::class, 'parentView'])->name('parent-view');
        Route::get('/statistik', [MutabaahController::class, 'statistik'])->name('statistik');
        Route::get('/export-pdf/{classroom}', [MutabaahController::class, 'exportPdf'])->name('export-pdf');
        Route::post('/{student}/verify', [MutabaahController::class, 'verify'])->name('verify');
        Route::post('/toggle', [MutabaahController::class, 'toggle'])->name('toggle');
    });

    // Presensi Kegiatan Sekolah
    Route::prefix('event-attendance')->name('event-attendance.')->group(function () {
        Route::get('/', [EventAttendanceController::class, 'index'])->name('index');
        Route::get('/create', [EventAttendanceController::class, 'create'])->name('create');
        Route::post('/', [EventAttendanceController::class, 'store'])->name('store');
        Route::get('/rekap', [EventAttendanceController::class, 'rekap'])->name('rekap');
        Route::get('/{event}', [EventAttendanceController::class, 'show'])->name('show');
        Route::get('/{event}/input', [EventAttendanceController::class, 'inputAttendance'])->name('input');
        Route::post('/{event}/input', [EventAttendanceController::class, 'storeAttendance'])->name('store-attendance');
        Route::get('/{event}/export-pdf', [EventAttendanceController::class, 'exportPdf'])->name('export-pdf');
    });

    // Impersonate Leave (Accessible by any auth user who is currently impersonating)
    Route::get('admin/impersonate-leave', [ImpersonateController::class, 'leave'])->name('admin.impersonate.leave');

    // Unified Notification Center & Enterprise Inbox
    Route::get('notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::get('notifications/preferences', [NotificationCenterController::class, 'preferences'])->name('notifications.preferences');
    Route::post('notifications/preferences', [NotificationCenterController::class, 'updatePreferences'])->name('notifications.preferences.update');
    Route::post('notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::post('notifications/mark-all-read', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::post('notifications/{id}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/test-dispatch', [NotificationCenterController::class, 'testDispatch'])->name('notifications.test_dispatch');
    Route::get('notifications/unread-count', [NotificationCenterController::class, 'unreadCount'])->name('notifications.unread_count');
    // Internal Messaging & Threaded Discussion System
    Route::get('messages', [MessagingController::class, 'index'])->name('messages.index');
    Route::get('messages/compose', [MessagingController::class, 'compose'])->name('messages.compose');
    Route::post('messages', [MessagingController::class, 'store'])->name('messages.store');
    Route::post('messages/{conversation}/reply', [MessagingController::class, 'reply'])->name('messages.reply');
    Route::get('messages/search-users', [MessagingController::class, 'searchUsers'])->name('messages.search_users');

    // Resource booking (rooms and equipment). removed the second
    // "enterprise" calendar that used to live here: the school has one
    // calendar, `calendar` (AcademicEvent), which the app reads too.
    Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, StudentDataAccess::STAFF_ROLES, Roles::LEADERSHIP, 'admin', 'bendahara', 'pustakawan', 'petugas_uks'))->group(function () {
        Route::get('resource-bookings', [ResourceBookingController::class, 'index'])->name('resource_bookings.index');
        Route::post('resource-bookings', [ResourceBookingController::class, 'createBooking'])->name('resource_bookings.store');
        Route::get('resource-bookings/check', [ResourceBookingController::class, 'checkAvailability'])->name('resource_bookings.check');
    });
    Route::middleware('role:admin,superadmin,waka_sarpras,staf_tu')->group(function () {
        Route::post('resource-bookings/{id}/approve', [ResourceBookingController::class, 'approveBooking'])->name('resource_bookings.approve');
        Route::post('resource-bookings/{id}/reject', [ResourceBookingController::class, 'rejectBooking'])->name('resource_bookings.reject');
    });

    // RKAS / RAPBS: TU drafts, the principal approves. These
    // routes carried no role, so any account could approve the budget.
    Route::middleware(Roles::only(BudgetPlan::READER_ROLES))->group(function () {
        Route::get('financial/budget', [BudgetPlanningController::class, 'dashboard'])->name('financial.budget.dashboard');
        Route::get('financial/budget/{id}', [BudgetPlanningController::class, 'show'])->whereNumber('id')->name('financial.budget.show');
        Route::get('financial/budget/{id}/variance', [BudgetPlanningController::class, 'varianceReport'])->name('financial.budget.variance');
    });
    Route::middleware(Roles::only(BudgetPlan::PREPARER_ROLES))->group(function () {
        Route::get('financial/budget/create', [BudgetPlanningController::class, 'create'])->name('financial.budget.create');
        Route::post('financial/budget', [BudgetPlanningController::class, 'store'])->name('financial.budget.store');
        Route::post('financial/budget/{id}/line-items', [BudgetPlanningController::class, 'storeLineItem'])->name('financial.budget.store_line_item');
        Route::put('financial/budget/{id}/line-items/{item}/realization', [BudgetPlanningController::class, 'recordRealization'])->name('financial.budget.realization');
        Route::post('financial/budget/{id}/submit', [BudgetPlanningController::class, 'submitApproval'])->name('financial.budget.submit');
    });
    Route::post('financial/budget/{id}/approve', [BudgetPlanningController::class, 'approve'])
        ->middleware(Roles::only(BudgetPlan::APPROVER_ROLES))->name('financial.budget.approve');

    // 33.3: Scholarships. : part of the Keuangan menu, and no longer open to
    // every account: staff enter applications (the form takes the student), the
    // principal may approve, and only TU and admin create programmes or pay out.
    $scholarshipReaders = Roles::only('staf_tu', Roles::PRINCIPAL, 'admin');
    $scholarshipWriters = Roles::only('staf_tu', 'admin');
    Route::get('financial/scholarships', [ScholarshipController::class, 'programs'])->name('financial.scholarships.programs')->middleware($scholarshipReaders);
    Route::get('financial/scholarships/create', [ScholarshipController::class, 'createProgram'])->name('financial.scholarships.create_program')->middleware($scholarshipWriters);
    Route::post('financial/scholarships', [ScholarshipController::class, 'storeProgram'])->name('financial.scholarships.store_program')->middleware($scholarshipWriters);
    Route::post('financial/scholarships/{id}/open', [ScholarshipController::class, 'openApplications'])->name('financial.scholarships.open')->middleware($scholarshipWriters);
    Route::get('financial/scholarships/{programId}/apply', [ScholarshipController::class, 'applyForm'])->name('financial.scholarships.apply_form')->middleware($scholarshipWriters);
    Route::post('financial/scholarships/{programId}/apply', [ScholarshipController::class, 'submitApplication'])->name('financial.scholarships.submit_application')->middleware($scholarshipWriters);
    Route::get('financial/scholarships/{programId}/review', [ScholarshipController::class, 'reviewApplications'])->name('financial.scholarships.review')->middleware($scholarshipReaders);
    Route::post('financial/scholarships/applications/{appId}/review', [ScholarshipController::class, 'review'])->name('financial.scholarships.review_action')->middleware($scholarshipReaders);
    Route::get('financial/scholarships/applications/{appId}/disburse', [ScholarshipController::class, 'disbursementForm'])->name('financial.scholarships.disburse_form')->middleware($scholarshipWriters);
    Route::post('financial/scholarships/applications/{appId}/disburse', [ScholarshipController::class, 'disburse'])->name('financial.scholarships.disburse_action')->middleware($scholarshipWriters);
    Route::get('financial/scholarships/report', [ScholarshipController::class, 'report'])->name('financial.scholarships.report')->middleware($scholarshipReaders);

    // 34.2: Positive Behavior & Character Report Card (P5)
    Route::get('growth/character/dimensions', [P5AssessmentController::class, 'dimensions'])->name('growth.character.dimensions');
    Route::get('growth/character/assess', [P5AssessmentController::class, 'assessForm'])->name('growth.character.assess_form');
    Route::post('growth/character/assess', [P5AssessmentController::class, 'storeAssessment'])->name('growth.character.store_assessment');
    Route::get('growth/character/student/{studentId}', [P5AssessmentController::class, 'studentReport'])->name('growth.character.student_report');
    Route::get('growth/character/leaderboard', [P5AssessmentController::class, 'leaderboard'])->name('growth.character.leaderboard');
    Route::post('growth/character/merit', [P5AssessmentController::class, 'awardMerit'])->name('growth.character.award_merit');

    // 34.3: Parent-Teacher Conference (PTC) Scheduler
    // Parent–teacher conferences (119.9): staff open sessions, parents book for their own
    // children, teachers see their bookings. These routes carried no role at all.
    Route::middleware(Roles::only(PtcSession::ORGANIZER_ROLES, PtcSession::HOST_ROLES, StudentDataAccess::PARENT_ROLES))->group(function () {
        Route::get('growth/ptc/sessions', [PtcSchedulerController::class, 'sessions'])->name('growth.ptc.sessions');
        Route::get('growth/ptc/sessions/{sessionId}/slots', [PtcSchedulerController::class, 'availableSlots'])->name('growth.ptc.available_slots');
    });
    Route::post('growth/ptc/sessions', [PtcSchedulerController::class, 'storeSession'])
        ->middleware(Roles::only(PtcSession::ORGANIZER_ROLES))->name('growth.ptc.store_session');
    Route::middleware(Roles::only(StudentDataAccess::PARENT_ROLES))->group(function () {
        Route::post('growth/ptc/slots/{slotId}/book', [PtcSchedulerController::class, 'bookSlot'])->name('growth.ptc.book_slot');
        Route::post('growth/ptc/slots/{slotId}/cancel', [PtcSchedulerController::class, 'cancelBooking'])->name('growth.ptc.cancel_slot');
    });
    Route::get('growth/ptc/teacher-schedule', [PtcSchedulerController::class, 'teacherSchedule'])
        ->middleware(Roles::only(PtcSession::HOST_ROLES))->name('growth.ptc.teacher_schedule');

    // Enterprise Human Capital Excellence -- 360 Evaluation, CPD & Leadership Pipeline
    // 36.1: 360-Degree Teacher Evaluation & EDOM
    // Teacher 360° evaluation (119.9). These routes carried no role: anyone could open a
    // cycle, rate as "supervisor", and read any teacher's results and the ranking.
    Route::middleware(Roles::only(Evaluation360Cycle::EVALUATOR_ROLES))->group(function () {
        Route::get('hc/evaluation/cycles', [Evaluation360Controller::class, 'cycles'])->name('hc.evaluation.cycles');
        Route::get('hc/evaluation/cycles/{cycleId}/fill', [Evaluation360Controller::class, 'fillForm'])->name('hc.evaluation.fill_form');
        Route::post('hc/evaluation/cycles/{cycleId}/fill', [Evaluation360Controller::class, 'submitEvaluation'])->name('hc.evaluation.submit');
        // A teacher's own results; leadership any teacher's (checked in the controller).
        Route::get('hc/evaluation/teacher/{teacherId}/results', [Evaluation360Controller::class, 'results'])->name('hc.evaluation.results');
    });
    Route::middleware(Roles::only(Evaluation360Cycle::MANAGER_ROLES))->group(function () {
        Route::post('hc/evaluation/cycles', [Evaluation360Controller::class, 'storeCycle'])->name('hc.evaluation.store_cycle');
        Route::get('hc/evaluation/ranking', [Evaluation360Controller::class, 'ranking'])->name('hc.evaluation.ranking');
    });

    // Advanced Student Experience Platform
    // 40.1: Student Self-Service Portal
    Route::get('student/portal/dashboard', [StudentPortalController::class, 'dashboard'])->name('student.portal.dashboard');
    Route::get('student/portal/request', [StudentPortalController::class, 'createRequest'])->name('student.portal.create_request');
    Route::post('student/portal/request', [StudentPortalController::class, 'storeRequest'])->name('student.portal.store_request');

    // Student Achievement Certificate Generator
    Route::get('certificates/templates', [CertificateController::class, 'index'])->name('certificates.templates');
    Route::get('certificates/verify/{code}', [CertificateController::class, 'verifyPage'])->name('certificates.verify');

    // Multi-Format Export Engine & Data Interoperability
    Route::middleware('role:staf_tu,waka_kurikulum,waka_sarpras,kepala_sekolah,admin,superadmin')->group(function () {
        Route::get('exports', [ExportEngineController::class, 'selectFormat'])->name('exports.index');
        Route::post('exports/generate', [ExportEngineController::class, 'generateExport'])->name('exports.generate');
        Route::get('exports/{id}/download', [ExportEngineController::class, 'downloadExport'])->whereNumber('id')->name('exports.download');
        Route::get('exports/history', [ExportEngineController::class, 'exportHistory'])->name('exports.history');
    });

    // =========================================================
    // EKSTRAKURIKULER & OSIS â€” Pengembangan Non-Akademik
    // =========================================================
    Route::get('extracurriculars', [ExtracurricularController::class, 'index'])->name('ekskul.index');
    Route::get('extracurriculars/schedule', [ExtracurricularController::class, 'schedule'])->name('ekskul.schedule');
    Route::get('extracurriculars/{extracurricular}', [ExtracurricularController::class, 'show'])->name('ekskul.show');
    Route::post('extracurriculars/{extracurricular}/register', [ExtracurricularController::class, 'register'])->name('ekskul.register');

    Route::middleware(['role:guru,bk,waka_kesiswaan,admin,superadmin'])->prefix('admin/extracurriculars')->name('ekskul.')->group(function () {
        // CRUD Ekskul
        Route::get('create', [ExtracurricularController::class, 'create'])->name('create');
        Route::post('store', [ExtracurricularController::class, 'store'])->name('store');
        Route::get('{extracurricular}/edit', [ExtracurricularController::class, 'edit'])->name('edit');
        Route::put('{extracurricular}', [ExtracurricularController::class, 'update'])->name('update');
        Route::delete('{extracurricular}', [ExtracurricularController::class, 'destroy'])->name('destroy');

        Route::get('manage/list', [ExtracurricularController::class, 'manage'])->name('manage');
        Route::post('attendance', [ExtracurricularController::class, 'storeAttendance'])->name('attendance.store');
        Route::post('activities', [ExtracurricularController::class, 'storeActivity'])->name('activities.store');
        Route::delete('members/{member}/unregister', [ExtracurricularController::class, 'unregister'])->name('members.unregister');

        // Achievements / Lomba
        Route::get('{extracurricular}/achievements', [ExtracurricularController::class, 'achievements'])->name('achievements');
        Route::post('{extracurricular}/achievements', [ExtracurricularController::class, 'storeAchievement'])->name('achievements.store');

        // Rapor Grading
        Route::get('{extracurricular}/grading', [ExtracurricularController::class, 'grading'])->name('grading');
        Route::post('{extracurricular}/grading', [ExtracurricularController::class, 'storeGrades'])->name('grading.store');

        // Reports
        Route::get('{extracurricular}/attendance-report', [ExtracurricularController::class, 'attendanceReport'])->name('attendance-report');
        Route::get('{extracurricular}/semester-report', [ExtracurricularController::class, 'semesterReport'])->name('semester-report');
        Route::get('{extracurricular}/export-pdf', [ExtracurricularController::class, 'exportPdf'])->name('export-pdf');
    });

    Route::prefix('admin/osis')->name('osis.')->group(function () {
        // Staff Only Routes
        Route::middleware(['role:waka_kesiswaan,admin,superadmin'])->group(function () {
            Route::get('/', [OsisController::class, 'dashboard'])->name('dashboard');
            Route::get('export', [OsisController::class, 'exportReport'])->name('export');

            // Kas OSIS
            Route::post('transactions', [OsisController::class, 'storeTransaction'])->name('transactions.store');
            Route::get('transactions/{id}/edit', [OsisController::class, 'editTransaction'])->name('transactions.edit');
            Route::put('transactions/{id}', [OsisController::class, 'updateTransaction'])->name('transactions.update');
            Route::delete('transactions/{id}', [OsisController::class, 'deleteTransaction'])->name('transactions.destroy');

            // Kepengurusan
            Route::get('members', [OsisController::class, 'members'])->name('members');
            Route::post('members', [OsisController::class, 'storeMember'])->name('members.store');
            Route::put('members/{member}', [OsisController::class, 'updateMember'])->name('members.update');
            Route::delete('members/{member}', [OsisController::class, 'removeMember'])->name('members.destroy');

            // Program Kerja
            Route::get('work-programs', [OsisController::class, 'workPrograms'])->name('work-programs');
            Route::post('work-programs', [OsisController::class, 'storeWorkProgram'])->name('work-programs.store');
            Route::put('work-programs/{program}', [OsisController::class, 'updateWorkProgram'])->name('work-programs.update');

            // Pemilihan OSIS Management
            Route::get('elections', [OsisController::class, 'elections'])->name('elections');
            Route::post('elections', [OsisController::class, 'storeElection'])->name('elections.store');
            Route::get('elections/{election}/candidates', [OsisController::class, 'candidates'])->name('elections.candidates');
            Route::post('elections/{election}/candidates', [OsisController::class, 'storeCandidate'])->name('elections.candidates.store');
        });

        // Bilik Suara E-Voting (Siswa & Staff)
        Route::middleware(['role:waka_kesiswaan,admin,superadmin,student,siswa'])->group(function () {
            Route::get('elections/{election}/voting', [OsisController::class, 'votingPage'])->name('elections.voting');
            Route::post('elections/{election}/vote', [OsisController::class, 'castVote'])->name('elections.vote');
            Route::get('elections/{election}/results', [OsisController::class, 'electionResults'])->name('elections.results');
        });
    });

    // =========================================================
    // LEMBAGA LEGISLATIF MPK (MAJELIS PERWAKILAN KELAS)
    // =========================================================
    Route::prefix('admin/mpk')->name('mpk.')->group(function () {
        Route::get('/', [MpkController::class, 'index'])->name('index');
        Route::post('audit', [MpkController::class, 'storeAudit'])->name('audit.store');

        Route::get('lpj', [MpkController::class, 'lpjIndex'])->name('lpj.index');
        Route::post('lpj', [MpkController::class, 'storeLpj'])->name('lpj.store');
        Route::post('lpj/{lpj}/review', [MpkController::class, 'reviewLpj'])->name('lpj.review');

        Route::get('aspirations', [MpkController::class, 'aspirationsIndex'])->name('aspirations.index');
        Route::post('aspirations', [MpkController::class, 'storeAspiration'])->name('aspirations.store');
        Route::post('aspirations/{aspiration}/resolve', [MpkController::class, 'resolveAspiration'])->name('aspirations.resolve');
    });

    // =========================================================
    // JURNAL HARIAN GURU (TEACHING JOURNAL / CLASS DIARY)
    // =========================================================
    Route::get('teaching-journals/export/pdf', [TeachingJournalController::class, 'exportPdf'])->name('teaching-journals.export-pdf');
    Route::post('teaching-journals/{teaching_journal}/review', [TeachingJournalController::class, 'review'])->name('teaching-journals.review');
    Route::resource('teaching-journals', TeachingJournalController::class)
        ->middlewareFor('index', 'can:viewAny,'.TeachingJournal::class);

    // =========================================================
    // STUDENT SELF-SERVICE
    // =========================================================
    // Both spellings of the student role.
    Route::middleware(Roles::only(StudentDataAccess::STUDENT_ROLES))->group(function () {
        Route::get('my-billings', [StudentFinanceController::class, 'index'])->name('finance.student.index');
        Route::get('my-academic', [StudentAcademicController::class, 'index'])->name('student.academic');
        Route::get('my-wadiah', [StudentFinanceController::class, 'wadiahWallet'])->name('student.wadiah.wallet');
        Route::get('my-daily-assessments', [StudentAcademicController::class, 'dailyAssessments'])->name('student.daily-assessments');
        Route::get('my-billings/{billing}/checkout', [StudentFinanceController::class, 'checkout'])->name('finance.student.checkout');
        Route::get('gateway/{billing}/mock', [StudentFinanceController::class, 'mockGateway'])->name('finance.mock-gateway');
        Route::post('gateway/{billing}/callback', [StudentFinanceController::class, 'callback'])->name('finance.mock-callback');

        // Tracer Study (Siswa dengan status alumni)
        Route::get('alumni/tracer-study', [TracerStudyController::class, 'showForm'])->name('alumni.tracer-study.form');
        Route::post('alumni/tracer-study', [TracerStudyController::class, 'submitForm'])->name('alumni.tracer-study.submit');
        Route::get('alumni/tracer-study/edit/{tracerResponse}', [TracerStudyController::class, 'editResponse'])->name('alumni.tracer-study.edit');
        Route::put('alumni/tracer-study/update/{tracerResponse}', [TracerStudyController::class, 'updateResponse'])->name('alumni.tracer-study.update');
    });

    // =========================================================
    // PARENT PORTAL â€” Area Premium Wali Murid
    // =========================================================
    Route::middleware(['role:parent,orang_tua,orangtua,wali_murid'])->prefix('parent-portal')->name('parent-portal.')->group(function () {
        Route::get('/', [ParentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('child/{student}/report-card', [ParentPortalController::class, 'viewReportCard'])->name('report-card');
        Route::get('child/{student}/schedule', [ParentPortalController::class, 'viewSchedule'])->name('schedule');
        Route::get('child/{student}/attendance', [ParentPortalController::class, 'viewAttendanceHistory'])->name('attendance');
        Route::get('child/{student}/billings', [ParentPortalController::class, 'viewBillings'])->name('billings');
        Route::get('child/{student}/daily-assessments', [ParentPortalController::class, 'viewDailyAssessments'])->name('daily-assessments');
        Route::get('child/{student}/wallet', [ParentPortalController::class, 'viewWadiahWallet'])->name('wallet');
        Route::post('child/{student}/wallet/limit', [ParentPortalController::class, 'setWadiahLimit'])->name('wallet.limit');

        // Fitur Pembayaran / Billings
        Route::get('billing/{billing}/checkout', [ParentPortalController::class, 'checkout'])->name('billings.checkout');
        Route::get('gateway/{billing}/mock', [ParentPortalController::class, 'mockGateway'])->name('billings.mock-gateway');
        Route::post('gateway/{billing}/callback', [ParentPortalController::class, 'callback'])->name('billings.callback');

        // Fitur Pesan / Obrolan dengan Wali Kelas
        Route::get('messages', [ParentPortalController::class, 'messages'])->name('messages');
        Route::post('messages', [ParentPortalController::class, 'sendMessage'])->name('messages.send');

        // Rute warisan mutaba'ah
        Route::post('mutabaah/{student}/verify', [MutabaahController::class, 'verify'])->name('mutabaah.verify');

        // C3: Izin / Sakit Siswa dari Orang Tua
        Route::get('leave-requests', [StudentLeaveController::class, 'parentIndex'])->name('leave-requests.index');
        Route::post('leave-requests', [StudentLeaveController::class, 'store'])->name('leave-requests.store');
        Route::post('leave-requests/{leaveRequest}/cancel', [StudentLeaveController::class, 'cancel'])->name('leave-requests.cancel');
    });

    // C3: Persetujuan Izin / Sakit Siswa oleh Wali Kelas / Waka Kesiswaan & Lampiran Privat
    Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kesiswaan', 'admin'))
        ->prefix('teacher/leave-requests')
        ->name('teacher.leave-requests.')
        ->group(function () {
            Route::get('/', [StudentLeaveController::class, 'teacherIndex'])->name('index');
            Route::post('{leaveRequest}/approve', [StudentLeaveController::class, 'approve'])->name('approve');
            Route::post('{leaveRequest}/reject', [StudentLeaveController::class, 'reject'])->name('reject');
            Route::post('{leaveRequest}/revoke', [StudentLeaveController::class, 'revoke'])->name('revoke');
        });

    Route::get('student-leave-requests/{leaveRequest}/attachment', [StudentLeaveController::class, 'downloadAttachment'])
        ->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES, StudentDataAccess::PARENT_ROLES, StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kesiswaan', Roles::PRINCIPAL, 'staf_tu', 'bk', 'petugas_uks', 'admin'))
        ->name('student-leave-requests.attachment');

    // =========================================================
    // E-RAPOR & LEDGER NILAI
    // =========================================================
    Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kurikulum', Roles::PRINCIPAL, 'staf_tu', 'admin'))->group(function () {
        Route::get('report-cards', [ReportCardController::class, 'index'])->name('report-cards.index');
        Route::get('report-cards/completeness', [ReportCardController::class, 'completeness'])->name('report-cards.completeness');
        Route::get('report-cards/export-erapor', [ReportCardController::class, 'exportERapor'])->name('report-cards.export-erapor');
        Route::post('report-cards/upload-official-pdfs', [ReportCardController::class, 'uploadOfficialPdfs'])->name('report-cards.upload-official-pdfs');
        Route::post('report-cards/sync', [ReportCardController::class, 'sync'])->name('report-cards.sync');
        Route::post('report-cards/notes', [ReportCardController::class, 'storeNotes'])->name('report-cards.store-notes');
    });

    Route::post('report-cards/publish-batch', [ReportCardController::class, 'publishBatch'])
        ->middleware(Roles::only('waka_kurikulum', Roles::PRINCIPAL, 'admin'))
        ->name('report-cards.publish-batch');

    Route::get('report-cards/files/{pdf}', [ReportCardController::class, 'downloadFile'])
        ->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES, StudentDataAccess::PARENT_ROLES, StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kurikulum', Roles::PRINCIPAL, 'staf_tu', 'admin'))
        ->name('report-cards.files.show');

    Route::get('report-cards/{student}/pdf', [ReportCardController::class, 'printPdf'])
        ->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES, StudentDataAccess::PARENT_ROLES, StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kurikulum', Roles::PRINCIPAL, 'staf_tu', 'admin'))
        ->name('report-cards.pdf');

    // =========================================================
    // GURU â€” Fitur Akademik Pengajar
    // =========================================================
    Route::middleware('role:guru,teacher,bk,admin,superadmin')->group(function () {
        // Absensi
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
        Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');

        // Input Nilai
        Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
        Route::get('grades/create', [GradeController::class, 'create'])->name('grades.create');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');

        // Tahfidz (store â€” guru input setoran)
        Route::post('tahfidz', [TahfidzController::class, 'store'])->name('tahfidz.store');

        // Penilaian Harian & Remedial
        Route::get('daily-assessments', [DailyAssessmentController::class, 'index'])->name('daily-assessments.index');
        Route::get('daily-assessments/create', [DailyAssessmentController::class, 'create'])->name('daily-assessments.create');
        Route::post('daily-assessments', [DailyAssessmentController::class, 'store'])->name('daily-assessments.store');
        Route::get('daily-assessments/remedial', [DailyAssessmentController::class, 'remedialList'])->name('daily-assessments.remedial');
        Route::get('daily-assessments/remedial/{score}/input', [DailyAssessmentController::class, 'inputRemedialScore'])->name('daily-assessments.input-remedial');
        Route::post('daily-assessments/remedial/{score}/store', [DailyAssessmentController::class, 'storeRemedialScore'])->name('daily-assessments.store-remedial');
        Route::get('daily-assessments/classroom/{classroom}/analytics', [DailyAssessmentController::class, 'analytics'])->name('daily-assessments.analytics');
        Route::get('daily-assessments/{daily_assessment}', [DailyAssessmentController::class, 'show'])->name('daily-assessments.show');
        Route::get('daily-assessments/{daily_assessment}/input-scores', [DailyAssessmentController::class, 'inputScores'])->name('daily-assessments.input-scores');
        Route::post('daily-assessments/{daily_assessment}/store-scores', [DailyAssessmentController::class, 'storeScores'])->name('daily-assessments.store-scores');
        Route::get('daily-assessments/{daily_assessment}/export-pdf', [DailyAssessmentController::class, 'exportPdf'])->name('daily-assessments.pdf');
    });

    // =========================================================
    // KESISWAAN â€” BK & Poin Kedisiplinan
    // =========================================================
    Route::middleware('role:bk,waka_kesiswaan,guru,admin,superadmin')->group(function () {
        Route::get('points', [PointController::class, 'index'])->name('points.index');
        Route::get('points/{student}', [PointController::class, 'show'])->name('points.show');
        Route::post('points', [PointController::class, 'store'])->name('points.store');
        Route::delete('points/{log}', [PointController::class, 'destroy'])->name('points.destroy');

        // Kategori Pelanggaran / Prestasi (Master Data)
        Route::get('point-categories', [PointCategoryController::class, 'index'])->name('point-categories.index');
        Route::post('point-categories', [PointCategoryController::class, 'store'])->name('point-categories.store');
        Route::put('point-categories/{pointCategory}', [PointCategoryController::class, 'update'])->name('point-categories.update');
        Route::delete('point-categories/{pointCategory}', [PointCategoryController::class, 'destroy'])->name('point-categories.destroy');
    });

    // =========================================================
    // ADMINISTRASI â€” Keuangan (TU & Admin)
    // =========================================================
    Route::middleware('role:staf_tu,admin,superadmin')->group(function () {
        Route::get('finance/billings', [FinanceController::class, 'index'])->name('finance.billings.index');
        Route::get('finance/billings/export', [FinanceController::class, 'export'])->name('finance.billings.export');
        Route::post('finance/billings', [FinanceController::class, 'store'])->name('finance.billings.store');
        Route::get('finance/billings/{billing}/edit', [FinanceController::class, 'editBilling'])->name('finance.billings.edit');
        Route::put('finance/billings/{billing}', [FinanceController::class, 'updateBilling'])->name('finance.billings.update');
        Route::post('finance/billings/bulk', [FinanceController::class, 'bulkGenerate'])->name('finance.billings.bulk');
        Route::post('finance/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');
        Route::get('finance/payments/{payment}/receipt', [FinanceController::class, 'printReceipt'])->name('finance.payments.receipt');
        Route::post('finance/billings/{billing}/sync', [FinanceController::class, 'sync'])->name('finance.billings.sync');
        Route::post('finance/categories', [FinanceController::class, 'storeCategory'])->name('finance.categories.store');
        Route::post('finance/billings/{billing}/whatsapp-reminder', [FinanceController::class, 'sendWhatsappReminder'])->name('finance.billings.whatsapp-reminder');
    });

    // =========================================================
    // EXECUTIVE â€” Laporan Keuangan (Kepsek & Admin)
    // =========================================================
    Route::middleware('role:staf_tu,kepala_sekolah,admin,superadmin')->group(function () {
        Route::get('finance/reports/executive', [FinancialReportController::class, 'index'])->name('finance.reports.executive');
        Route::get('finance/reports/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('finance.reports.income');
        Route::get('finance/reports/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('finance.reports.balance');
        Route::get('finance/reports/general-ledger', [FinancialReportController::class, 'generalLedger'])->name('finance.reports.ledger');
        Route::get('finance/reports/arrears', [FinancialReportController::class, 'arrearsReport'])->name('finance.reports.arrears');
        Route::get('finance/reports/projection', [FinancialReportController::class, 'revenueProjection'])->name('finance.reports.projection');
        Route::get('finance/reports/monthly', [FinancialReportController::class, 'monthlySummary'])->name('finance.reports.monthly');
        Route::get('finance/reports/export', [FinancialReportController::class, 'exportReport'])->name('finance.reports.export');
        Route::get('finance/billings/{billing}/invoice', [FinancialReportController::class, 'generateInvoice'])->name('finance.billings.invoice');

        // Advanced Executive Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('attendance', [AnalyticsController::class, 'attendance'])->name('attendance');
            Route::get('academic', [AnalyticsController::class, 'academic'])->name('academic');
            Route::get('financial', [AnalyticsController::class, 'financial'])->name('financial');
            Route::get('islamic', [AnalyticsController::class, 'islamic'])->name('islamic');
            Route::get('student-risk', [AnalyticsController::class, 'studentRisk'])->name('student-risk');
            Route::get('comparison', [AnalyticsController::class, 'comparison'])->name('comparison');
            Route::get('heatmap', [AnalyticsController::class, 'heatmap'])->name('heatmap');
            Route::get('custom-report', [AnalyticsController::class, 'customReport'])->name('custom-report');

            // Exports
            Route::get('export/attendance', [AnalyticsExportController::class, 'exportAttendancePdf'])->name('export.attendance');
            Route::get('export/grades', [AnalyticsExportController::class, 'exportGradesPdf'])->name('export.grades');
            Route::get('export/finance', [AnalyticsExportController::class, 'exportFinancePdf'])->name('export.finance');
            Route::get('export/all', [AnalyticsExportController::class, 'exportAllExcel'])->name('export.all');
        });
    });

    // =========================================================
    // ADMINISTRASI — Manajemen Surat Keluar, Surat Masuk & Disposisi
    // =========================================================
    Route::middleware('role:staf_tu,admin,superadmin,kepala_sekolah')->group(function () {
        // Halaman Kelola Template
        Route::get('letter-templates', [LetterController::class, 'manageTemplates'])->name('letters.templates.index');
        Route::post('letter-templates', [LetterController::class, 'storeTemplate'])->name('letters.templates.store');

        // Pembuatan Massal (Bulk)
        Route::get('letters/bulk', [LetterController::class, 'showBulkForm'])->name('letters.bulk');
        Route::post('letters/bulk', [LetterController::class, 'bulkStore'])->name('letters.bulk.store');

        // Operasi CRUD & Transisi Status Surat Keluar (J1)
        Route::resource('letters', LetterController::class);
        Route::post('letters/{letter}/submit-approval', [LetterController::class, 'submitApproval'])->name('letters.submit-approval');
        Route::post('letters/{letter}/issue', [LetterController::class, 'issue'])->name('letters.issue');
        Route::post('letters/{letter}/revoke', [LetterController::class, 'revoke'])->name('letters.revoke');
        Route::get('letters/{letter}/pdf', [LetterController::class, 'downloadPdf'])->name('letters.pdf');
    });

    // Surat Masuk & Disposisi (J2)
    Route::middleware(Roles::only('staf_tu', 'admin'))->group(function () {
        Route::get('incoming-letters/create', [IncomingLetterController::class, 'create'])->name('incoming-letters.create');
        Route::post('incoming-letters', [IncomingLetterController::class, 'store'])->name('incoming-letters.store');
    });
    Route::middleware(Roles::only('staf_tu', Roles::PRINCIPAL, 'admin'))->group(function () {
        Route::post('incoming-letters/{incomingLetter}/archive', [IncomingLetterController::class, 'archive'])->name('incoming-letters.archive');
    });
    Route::middleware(Roles::only(Roles::PRINCIPAL, 'admin'))->group(function () {
        Route::post('incoming-letters/{incomingLetter}/dispose', [IncomingLetterController::class, 'dispose'])->name('incoming-letters.dispose');
    });
    Route::middleware(Roles::only('staf_tu', Roles::LEADERSHIP, StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'bk', 'bendahara', 'pustakawan', 'petugas_uks', 'admin'))->group(function () {
        Route::get('incoming-letters', [IncomingLetterController::class, 'index'])->name('incoming-letters.index');
        Route::get('incoming-letters/{incomingLetter}', [IncomingLetterController::class, 'show'])->name('incoming-letters.show');
        Route::get('incoming-letters/{incomingLetter}/attachment', [IncomingLetterController::class, 'downloadAttachment'])->name('incoming-letters.attachment');
        Route::post('letter-dispositions/{disposition}/follow-up', [IncomingLetterController::class, 'updateDisposition'])->name('letter-dispositions.follow-up');
    });

    // =========================================================
    // KESISWAAN â€” Unit Kesehatan Sekolah (UKS) Digital
    // =========================================================
    Route::prefix('uks')->name('uks.')->group(function () {
        // Akses Terbuka (Policy Guard di Controller)
        Route::get('student/{student}/history', [UksController::class, 'studentHistory'])->name('student-history');

        // Khusus Guru, BK, Staf TU, Waka, Admin
        Route::middleware('role:guru,bk,staf_tu,waka_kesiswaan,admin,superadmin')->group(function () {
            Route::get('/', [UksController::class, 'dashboard'])->name('dashboard');

            // Kunjungan CRUD & Edit
            Route::get('visits', [UksController::class, 'visitsIndex'])->name('visits.index');
            Route::get('visits/create', [UksController::class, 'visitsCreate'])->name('visits.create');
            Route::post('visits', [UksController::class, 'visitsStore'])->name('visits.store');
            Route::get('visits/{visit}', [UksController::class, 'visitsShow'])->name('visits.show');
            Route::get('visits/{visit}/edit', [UksController::class, 'editVisit'])->name('visits.edit');
            Route::put('visits/{visit}', [UksController::class, 'updateVisit'])->name('visits.update');
            Route::delete('visits/{visit}', [UksController::class, 'visitsDestroy'])->name('visits.destroy');

            // Inventaris Obat
            Route::get('medicines', [UksController::class, 'medicinesIndex'])->name('medicines.index');
            Route::post('medicines', [UksController::class, 'medicinesStore'])->name('medicines.store');
            Route::put('medicines/{medicine}', [UksController::class, 'medicinesUpdate'])->name('medicines.update');
            Route::delete('medicines/{medicine}', [UksController::class, 'medicinesDestroy'])->name('medicines.destroy');

            // Rekam Medis Siswa
            Route::get('health-records', [UksController::class, 'healthRecords'])->name('health-records.index');
            Route::get('health-records/{student}', [UksController::class, 'showHealthRecord'])->name('health-records.show');
            Route::post('health-records', [UksController::class, 'updateHealthRecord'])->name('health-records.update');

            // Health Screening
            Route::get('screenings', [UksController::class, 'screeningIndex'])->name('screenings.index');
            Route::post('screenings', [UksController::class, 'storeScreening'])->name('screenings.store');
            Route::get('screenings/{screening}/results', [UksController::class, 'inputScreeningResults'])->name('screenings.results');
            Route::post('screenings/results', [UksController::class, 'storeScreeningResults'])->name('screenings.results.store');
            Route::get('screenings/{screening}/report', [UksController::class, 'screeningReport'])->name('screenings.report');
            Route::get('screenings/{screening}/pdf', [UksController::class, 'exportScreeningPdf'])->name('screenings.export-pdf');

            // Log Obat & Restock
            Route::get('medicines/dispense-log', [UksController::class, 'medicineDispenseLog'])->name('medicines.dispense-log');
            Route::get('medicines/restock-log', [UksController::class, 'restockLog'])->name('medicines.restock-log');
            Route::post('medicines/restock', [UksController::class, 'storeRestock'])->name('medicines.restock.store');

            // Ekspor Rekap Bulanan PDF
            Route::get('export/monthly', [UksController::class, 'exportMonthlyReport'])->name('export.monthly');
        });
    });

    // =========================================================
    // KESISWAAN â€” Bimbingan & Konseling (BK Online)
    // =========================================================
    Route::prefix('counseling')->name('counseling.')->group(function () {
        // Akses Terbuka (Policy Guard di Controller)
        Route::get('student/{student}/profile', [CounselingController::class, 'studentProfile'])->name('student-profile');

        // Permohonan Konseling untuk Siswa/Orang Tua/BK
        Route::get('request', [CounselingController::class, 'requestForm'])->name('request-form');
        Route::post('request', [CounselingController::class, 'requestSession'])->name('request');

        // Batalkan Sesi Konseling (Akses Terbuka untuk BK, dan Terbatas untuk Siswa/Orang Tua di Controller)
        Route::post('sessions/{session}/cancel', [CounselingController::class, 'cancelSession'])->name('cancel');

        // Unduh Berita Acara Konseling PDF Resmi (Policy Guard di Controller)
        Route::get('sessions/{session}/report', [CounselingController::class, 'exportSessionReport'])->name('student-report');

        // Khusus Guru BK, Waka, Admin
        Route::middleware('role:bk,waka_kesiswaan,admin,superadmin')->group(function () {
            Route::get('/', [CounselingController::class, 'dashboard'])->name('dashboard');
            Route::get('sessions', [CounselingController::class, 'index'])->name('index');
            Route::post('sessions/{session}/schedule', [CounselingController::class, 'schedule'])->name('schedule');
            Route::post('sessions/{session}/start', [CounselingController::class, 'startSession'])->name('start');
            Route::get('sessions/{session}/interact', [CounselingController::class, 'session'])->name('session');
            Route::post('sessions/{session}/complete', [CounselingController::class, 'completeSession'])->name('complete');
        });
    });

    // =========================================================
    // KURIKULUM â€” Master Data Akademik
    // =========================================================
    Route::middleware('role:waka_kurikulum,staf_tu,admin,superadmin,kepala_sekolah')->group(function () {
        // Kalender Akademik CRUD
        // Events are created/edited from the calendar page itself (modal + JSON).
        Route::resource('calendar/events', AcademicEventController::class)->only(['store', 'update', 'destroy']);

        // Tahun Ajaran
        Route::post('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
        Route::resource('academic-years', AcademicYearController::class)->except(['show']);

        // Mapel, Kelas, Siswa
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('classrooms', ClassroomController::class)->except(['show']);
        Route::get('students/{student}/pdf', [StudentController::class, 'exportPdf'])->name('students.pdf');
        Route::resource('students', StudentController::class)->except(['show']);
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');

        // Penjadwalan
        Route::get('schedules/presets', [ScheduleController::class, 'index'])->name('schedules.presets.index');
        Route::post('schedules/presets', [ScheduleController::class, 'storePreset'])->name('schedules.presets.store');
        Route::post('schedules/presets/{preset}/activate', [ScheduleController::class, 'activatePreset'])->name('schedules.presets.activate');
        Route::post('schedules/presets/{preset}/times', [ScheduleController::class, 'storeTime'])->name('schedules.presets.times.store');
        Route::get('schedules/teacher', [ScheduleController::class, 'teacherSchedule'])->name('schedules.teacher');
        Route::get('schedules/room', [ScheduleController::class, 'roomSchedule'])->name('schedules.room');
        Route::get('schedules/conflicts', [ScheduleController::class, 'detectConflicts'])->name('schedules.conflicts');
        Route::get('schedules/print/class/{classroom}', [ScheduleController::class, 'printClassSchedule'])->name('schedules.print.class');
        Route::get('schedules/print/teacher/{teacher}', [ScheduleController::class, 'printTeacherSchedule'])->name('schedules.print.teacher');
        Route::get('schedules/print/master', [ScheduleController::class, 'printMasterSchedule'])->name('schedules.print.master');
        Route::get('schedules/grid', [ScheduleController::class, 'showGrid'])->name('schedules.grid');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        // Mutasi & Alumni
        Route::resource('mutations', MutationController::class)->only(['index', 'create', 'store']);
        Route::get('mutations/{mutation}/document', [MutationController::class, 'downloadDocument'])->name('mutations.document');
    });

    // K2: Buku Induk Siswa (Visibilitas diperiksa via Student::isVisibleTo di StudentController)
    Route::middleware(Roles::only(
        StudentDataAccess::STUDENT_ROLES,
        StudentDataAccess::PARENT_ROLES,
        StudentDataAccess::TEACHER_ROLES,
        'wali_kelas',
        'bk',
        'waka_kurikulum',
        'waka_kesiswaan',
        'staf_tu',
        Roles::PRINCIPAL,
        'admin'
    ))->group(function () {
        Route::get('students/{student}/ledger', [StudentController::class, 'bukuInduk'])->name('students.ledger');
        Route::get('students/{student}/ledger/pdf', [StudentController::class, 'bukuIndukPdf'])->name('students.ledger.pdf');
    });

    // =========================================================
    // INVENTARIS & PEMINJAMAN ASET LANJUTAN (MODUL 3)
    // =========================================================
    Route::middleware(['auth'])->prefix('assets')->name('assets.')->group(function () {
        // Peminjaman (Umum - Guru/Staf & Admin)
        Route::get('loans', [AssetLoanController::class, 'index'])->name('loans.index');
        Route::get('loans/create', [AssetLoanController::class, 'create'])->name('loans.create');
        Route::post('loans', [AssetLoanController::class, 'store'])->name('loans.store');
        Route::get('loans/{loan}', [AssetLoanController::class, 'show'])->name('loans.show');

        // Khusus Waka Sarpras, Staf TU, Kepala Sekolah, & Admin/Superadmin
        Route::middleware(['role:waka_sarpras,staf_tu,admin,superadmin,kepala_sekolah'])->group(function () {
            Route::post('loans/{loan}/approve', [AssetLoanController::class, 'approve'])->name('loans.approve');
            Route::post('loans/{loan}/return', [AssetLoanController::class, 'returnAsset'])->name('loans.return');
            Route::get('reports/depreciation', [AssetLoanController::class, 'depreciationReport'])->name('depreciation');
            Route::get('print/qr-labels', [AssetLoanController::class, 'qrLabelsSheet'])->name('print.qr-labels');

            // Perawatan (Maintenance)
            Route::resource('maintenances', AssetMaintenanceController::class)->only(['index', 'store', 'update']);
        });
    });

    // =========================================================
    // SARPRAS â€” Sarana Prasarana
    // =========================================================
    Route::middleware('role:waka_sarpras,admin,superadmin')->group(function () {
        Route::resource('assets', AssetController::class);
        Route::resource('asset-categories', AssetCategoryController::class)->only(['index', 'store', 'destroy']);
        Route::resource('rooms', RoomController::class)->only(['index', 'store', 'destroy']);
    });

    // =========================================================
    // HUMAS â€” Pengumuman, Tracer Study Analytics & Career Management
    // =========================================================
    Route::middleware('role:waka_humas,admin,superadmin,kepala_sekolah')->group(function () {
        Route::resource('announcements', AnnouncementController::class)->except(['show']);
        Route::post('announcements/{announcement}/pin', [AnnouncementController::class, 'pin'])->name('announcements.pin');
        Route::get('announcements/{announcement}/receipts', [AnnouncementController::class, 'receipts'])->name('announcements.receipts');

        // Alumni & Tracer Study Advanced Admin Panel
        Route::get('alumni/dashboard', [AlumniController::class, 'dashboard'])->name('alumni.dashboard');
        Route::get('alumni/bulk-graduate', [AlumniController::class, 'showBulkGraduateForm'])->name('alumni.bulk-graduate');
        Route::post('alumni/bulk-graduate', [AlumniController::class, 'bulkGraduate'])->name('alumni.bulk-graduate.store');
        Route::get('alumni/export/excel', [AlumniController::class, 'exportExcel'])->name('alumni.export.excel');
        Route::get('alumni/export/pdf/{year}', [AlumniController::class, 'exportPdf'])->name('alumni.export.pdf');

        // Full CRUD for Alumni
        Route::resource('alumni', AlumniController::class)->except(['show']);

        // Advanced Tracer Study Control
        Route::get('alumni/tracer-study/analytics', [TracerStudyController::class, 'analytics'])->name('alumni.tracer-study.analytics');
        Route::get('alumni/tracer-study/comparison', [TracerStudyController::class, 'comparison'])->name('alumni.tracer-study.comparison');
        Route::get('alumni/tracer-study/invitation', [TracerStudyController::class, 'invitation'])->name('alumni.tracer-study.invitation');
        Route::get('alumni/tracer-study/export/excel', [TracerStudyController::class, 'exportExcel'])->name('alumni.tracer-study.export.excel');

    });

    // Announcements (Universal access, placed after resource registration to prevent conflict with /create)
    Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::post('announcements/{announcement}/acknowledge', [AnnouncementController::class, 'acknowledge'])->name('announcements.acknowledge');

    // =========================================================
    // HUMAS â€” Content Management System (CMS)
    // =========================================================
    Route::middleware('role:waka_humas,admin,superadmin')->prefix('cms')->name('cms.')->group(function () {
        Route::get('dashboard', [CmsController::class, 'dashboard'])->name('dashboard');

        // Pages CRUD
        Route::get('pages', [CmsController::class, 'pagesIndex'])->name('pages.index');
        Route::get('pages/create', [CmsController::class, 'pagesCreate'])->name('pages.create');
        Route::post('pages', [CmsController::class, 'pagesStore'])->name('pages.store');
        Route::get('pages/{page}/edit', [CmsController::class, 'pagesEdit'])->name('pages.edit');
        Route::put('pages/{page}', [CmsController::class, 'pagesUpdate'])->name('pages.update');
        Route::delete('pages/{page}', [CmsController::class, 'pagesDestroy'])->name('pages.destroy');

        // Posts CRUD
        Route::get('posts', [CmsController::class, 'postsIndex'])->name('posts.index');
        Route::get('posts/create', [CmsController::class, 'postsCreate'])->name('posts.create');
        Route::post('posts', [CmsController::class, 'postsStore'])->name('posts.store');
        Route::get('posts/{post}/edit', [CmsController::class, 'postsEdit'])->name('posts.edit'); // Keep it clean
        Route::put('posts/{post}', [CmsController::class, 'postsUpdate'])->name('posts.update');
        Route::delete('posts/{post}', [CmsController::class, 'postsDestroy'])->name('posts.destroy');

        // Galleries CRUD
        Route::get('galleries', [CmsController::class, 'galleriesIndex'])->name('galleries.index');
        Route::post('galleries', [CmsController::class, 'galleriesStore'])->name('galleries.store');
        Route::put('galleries/{gallery}', [CmsController::class, 'galleriesUpdate'])->name('galleries.update');
        Route::delete('galleries/{gallery}', [CmsController::class, 'galleriesDestroy'])->name('galleries.destroy');

        // Testimonials CRUD
        Route::get('testimonials', [CmsController::class, 'testimonialsIndex'])->name('testimonials.index');
        Route::post('testimonials', [CmsController::class, 'testimonialsStore'])->name('testimonials.store');
        Route::put('testimonials/{testimonial}', [CmsController::class, 'testimonialsUpdate'])->name('testimonials.update');
        Route::delete('testimonials/{testimonial}', [CmsController::class, 'testimonialsDestroy'])->name('testimonials.destroy');
        // Editor uploads
        Route::post('upload-image', [CmsController::class, 'uploadImage'])->name('upload-image');

        // New administrative endpoints
        Route::post('posts/{id}/schedule', [CmsController::class, 'schedulePost'])->name('posts.schedule');
        Route::match(['get', 'post'], 'tags', [CmsController::class, 'tagManagement'])->name('tags.index');
        Route::match(['get', 'post'], 'media', [CmsController::class, 'mediaLibrary'])->name('media.index');
        Route::match(['get', 'post'], 'seo', [CmsController::class, 'seoSettings'])->name('seo.index');
    });

    // =========================================================
    // HRD & KEPEGAWAIAN â€” Manajemen Staf dan Guru
    // =========================================================
    Route::middleware('role:admin,superadmin,kepala_sekolah')->prefix('hrd')->name('hrd.')->group(function () {
        // Dashboard & Reports
        Route::get('dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
        Route::get('org-chart', [EmployeeController::class, 'orgChart'])->name('org-chart');
        Route::get('attendance-report', [EmployeeController::class, 'attendanceReport'])->name('attendance-report');
        Route::get('attendance-report/export', [EmployeeController::class, 'exportAttendanceCsv'])->name('attendance-report.export');
        Route::get('certification-tracker', [EmployeeController::class, 'certificationTracker'])->name('certification-tracker');
        Route::get('evaluations/report', [EmployeeController::class, 'evaluationReport'])->name('evaluations.report');

        Route::resource('employees', EmployeeController::class);

        // Manage Employee Documents
        Route::get('employees/{employee}/documents', [EmployeeController::class, 'documents'])->name('employees.documents');
        Route::get('employees/{employee}/documents/{document}/file', [EmployeeController::class, 'downloadDocument'])->name('employees.documents.show');
        Route::post('employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])->name('employees.documents.store');
        Route::delete('employees/{employee}/documents/{document}', [EmployeeController::class, 'deleteDocument'])->name('employees.documents.destroy');

        // Manage Employee Trainings
        Route::get('employees/{employee}/trainings', [EmployeeController::class, 'trainings'])->name('employees.trainings');
        Route::post('employees/{employee}/trainings', [EmployeeController::class, 'storeTraining'])->name('employees.trainings.store');

        // Manage Employee Evaluations
        Route::get('employees/{employee}/evaluation', [EmployeeController::class, 'evaluation'])->name('employees.evaluation');
        Route::post('employees/{employee}/evaluation', [EmployeeController::class, 'storeEvaluation'])->name('employees.evaluation.store');
        Route::get('employees/{employee}/id-card', [EmployeeController::class, 'exportEmployeeCard'])->name('employees.id-card');

        // Admin Payroll Management
        Route::post('payrolls/generate', [PayrollController::class, 'store'])->name('payrolls.generate');
        Route::post('payrolls/approve-bulk', [PayrollController::class, 'approveBulk'])->name('payrolls.approve-bulk');
        Route::post('payrolls/pay-bulk', [PayrollController::class, 'payBulk'])->name('payrolls.pay-bulk');
        Route::post('payrolls/rules', [PayrollController::class, 'updateRules'])->name('payrolls.rules');
        Route::post('payrolls/{payroll}/approve', [PayrollController::class, 'approve'])->name('payrolls.approve');
        Route::post('payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');
        Route::resource('payrolls', PayrollController::class)->except(['store', 'edit']);
    });

    // staff attendance comes from the fingerprint machine; TU uploads its log.
    Route::middleware(['auth', Roles::only('staf_tu', Roles::PRINCIPAL, 'admin')])->prefix('hrd/fingerprint-import')->name('hrd.fingerprint-import.')->group(function () {
        Route::get('/', [FingerprintImportController::class, 'index'])->name('index');
        Route::post('/', [FingerprintImportController::class, 'store'])->name('store');
    });

    // =========================================================
    // SELF-SERVICE HRD (Semua Pegawai/Staf/Guru)
    // =========================================================
    Route::middleware('auth')->prefix('my-hrd')->name('my-hrd.')->group(function () {
        Route::get('portal', [MyHRDController::class, 'index'])->name('portal');
        Route::post('leaves', [MyHRDController::class, 'storeLeaveRequest'])->name('leaves');

        // Employee Payslip Download
        Route::get('payslips/{payroll}/pdf', [MyHRDController::class, 'downloadPayslipPdf'])->name('payslips.pdf');
    });

    // =========================================================
    // LIBRARY MANAGEMENT â€” Kelola Perpustakaan
    // =========================================================
    Route::middleware('role:guru,staf_tu,admin,superadmin')->group(function () {
        Route::get('library/manage', [LibraryController::class, 'manage'])->name('library.manage');
        Route::get('library/create', [LibraryController::class, 'create'])->name('library.create');
        Route::post('library', [LibraryController::class, 'store'])->name('library.store');
        Route::get('library/{libraryBook}/edit', [LibraryController::class, 'edit'])->name('library.edit');
        Route::put('library/{libraryBook}', [LibraryController::class, 'update'])->name('library.update');
        Route::delete('library/{libraryBook}', [LibraryController::class, 'destroy'])->name('library.destroy');

        // Sirkulasi Peminjaman & Pengembalian Fisik
        Route::get('library/circulation', [LibraryCirculationController::class, 'index'])->name('library.circulation.index');
        Route::post('library/circulation/scan', [LibraryCirculationController::class, 'scan'])->name('library.circulation.scan');
        Route::post('library/circulation/borrow', [LibraryCirculationController::class, 'borrow'])->name('library.circulation.borrow');
        Route::post('library/circulation/return/{loan}', [LibraryCirculationController::class, 'returnBook'])->name('library.circulation.return');
        Route::post('library/circulation/pay-fine/{loan}', [LibraryCirculationController::class, 'payFine'])->name('library.circulation.pay-fine');

        // Barcode & NFC Sirkulasi Cepat
        Route::get('library/circulation/quick-terminal', [LibraryCirculationController::class, 'quickCirculationTerminal'])->name('library.circulation.quick-terminal');
        Route::post('library/circulation/barcode-borrow', [LibraryCirculationController::class, 'processBarcodeBorrow'])->name('library.circulation.barcode-borrow');
        Route::post('library/circulation/barcode-return', [LibraryCirculationController::class, 'processBarcodeReturn'])->name('library.circulation.barcode-return');
        Route::get('library/books/print-barcodes', [LibraryCirculationController::class, 'printBarcodeLabels'])->name('library.books.print-barcodes');

        // Statistik, Overdue & WhatsApp Reminders
        Route::get('library/statistics', [LibraryController::class, 'statistics'])->name('library.statistics');
        Route::get('library/overdue-report', [LibraryController::class, 'overdueReport'])->name('library.overdue-report');
        Route::post('library/loans/{loan}/remind', [LibraryController::class, 'sendOverdueReminder'])->name('library.loans.remind');
        Route::get('library/reading-history/{student}', [LibraryController::class, 'studentReadingHistory'])->name('library.reading-history.detail');

    });

    // K5: Manajemen Akun Siswa/Wali per Rombel, Reset Password & Lembar Kredensial PDF
    Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'staf_tu', Roles::PRINCIPAL, 'admin'))->group(function () {
        Route::get('student-accounts', [AccountProvisioningController::class, 'studentAccounts'])->name('student-accounts.index');
        Route::post('users/{user}/reset-password', [AccountProvisioningController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('classrooms/{classroom}/credentials-pdf', [AccountProvisioningController::class, 'classroomCredentialSheet'])->name('classrooms.credentials-pdf');
    });

    // K5 & K4 Operator Routes (TU & Admin)
    Route::middleware('role:admin,superadmin,staf_tu')->group(function () {
        Route::post('users/bulk-provision-parents', [AccountProvisioningController::class, 'bulkProvisionParents'])->name('users.bulk-provision-parents');
        Route::get('users/import-credentials/{token}', [AccountProvisioningController::class, 'downloadBatchCredentials'])->name('users.import-credentials.download');

        Route::prefix('admin/profile-change-requests')->name('admin.profile-change-requests.')->group(function () {
            Route::get('/', [ProfileChangeRequestController::class, 'adminIndex'])->name('index');
            Route::post('/bulk-approve', [ProfileChangeRequestController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/{changeRequest}/approve', [ProfileChangeRequestController::class, 'approve'])->name('approve');
            Route::post('/{changeRequest}/reject', [ProfileChangeRequestController::class, 'reject'])->name('reject');
        });
    });

    // =========================================================
    // ADMIN & SUPERADMIN ONLY — Manajemen Sistem
    // =========================================================
    Route::middleware('role:admin,superadmin,kepala_sekolah')->group(function () {
        // User Management
        Route::resource('users', UserController::class)->except('show');
        Route::get('logs', [ActivityLogController::class, 'index'])->name('admin.logs');

        // System Settings
        Route::get('settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('admin.settings.update');

        Route::get('admin/impersonate/{user}', [ImpersonateController::class, 'take'])->name('admin.impersonate.take');

        // WhatsApp Gateway Simulator & Settings
        Route::get('admin/whatsapp', [WhatsappController::class, 'index'])->name('admin.whatsapp.index');
        Route::post('admin/whatsapp/send-test', [WhatsappController::class, 'sendTest'])->name('admin.whatsapp.send-test');
        Route::post('admin/whatsapp/settings', [WhatsappController::class, 'updateSettings'])->name('admin.whatsapp.settings-update');
        Route::post('admin/whatsapp/resend/{log}', [WhatsappController::class, 'resend'])->name('admin.whatsapp.resend');

        // WhatsApp Gateway Enhancements
        Route::get('admin/whatsapp/dashboard', [WhatsappController::class, 'dashboard'])->name('admin.whatsapp.dashboard');
        Route::get('admin/whatsapp/logs', [WhatsappController::class, 'logs'])->name('admin.whatsapp.logs');

        // Templates CRUD
        Route::get('admin/whatsapp/templates', [WhatsappController::class, 'templates'])->name('admin.whatsapp.templates.index');
        Route::get('admin/whatsapp/templates/create', [WhatsappController::class, 'createTemplate'])->name('admin.whatsapp.templates.create');
        Route::post('admin/whatsapp/templates', [WhatsappController::class, 'storeTemplate'])->name('admin.whatsapp.templates.store');
        Route::get('admin/whatsapp/templates/{id}/edit', [WhatsappController::class, 'editTemplate'])->name('admin.whatsapp.templates.edit');
        Route::put('admin/whatsapp/templates/{id}', [WhatsappController::class, 'updateTemplate'])->name('admin.whatsapp.templates.update');
        Route::delete('admin/whatsapp/templates/{id}', [WhatsappController::class, 'deleteTemplate'])->name('admin.whatsapp.templates.delete');

        // Broadcasts CRUD
        Route::get('admin/whatsapp/broadcasts', [WhatsappController::class, 'broadcasts'])->name('admin.whatsapp.broadcasts.index');
        Route::get('admin/whatsapp/broadcasts/create', [WhatsappController::class, 'createBroadcast'])->name('admin.whatsapp.broadcasts.create');
        Route::post('admin/whatsapp/broadcasts', [WhatsappController::class, 'storeBroadcast'])->name('admin.whatsapp.broadcasts.store');
        Route::get('admin/whatsapp/broadcasts/{id}', [WhatsappController::class, 'showBroadcast'])->name('admin.whatsapp.broadcasts.show');
        Route::post('admin/whatsapp/broadcasts/{id}/retry', [WhatsappController::class, 'retryFailed'])->name('admin.whatsapp.broadcasts.retry');

        // Automations Toggle/CRUD
        Route::get('admin/whatsapp/automations', [WhatsappController::class, 'automations'])->name('admin.whatsapp.automations.index');
        Route::post('admin/whatsapp/automations', [WhatsappController::class, 'storeAutomation'])->name('admin.whatsapp.automations.store');
        Route::post('admin/whatsapp/automations/{id}/toggle', [WhatsappController::class, 'toggleAutomation'])->name('admin.whatsapp.automations.toggle');
        Route::delete('admin/whatsapp/automations/{id}', [WhatsappController::class, 'deleteAutomation'])->name('admin.whatsapp.automations.delete');

        // SPMB (Manajemen Pendaftaran)
        Route::get('/spmb-admin/dashboard', [SpmbController::class, 'adminDashboard'])->name('spmb.admin.dashboard');
        Route::get('/spmb-admin/periods', [SpmbController::class, 'periods'])->name('spmb.admin.periods');
        Route::post('/spmb-admin/periods', [SpmbController::class, 'storePeriod'])->name('spmb.admin.periods.store');
        Route::get('/spmb-admin/waves', [SpmbController::class, 'waves'])->name('spmb.admin.waves');
        Route::post('/spmb-admin/waves', [SpmbController::class, 'storeWave'])->name('spmb.admin.waves.store');
        Route::get('/spmb-admin/tracks', [SpmbController::class, 'tracks'])->name('spmb.admin.tracks');
        Route::post('/spmb-admin/tracks', [SpmbController::class, 'storeTrack'])->name('spmb.admin.tracks.store');
        Route::post('/spmb-admin/documents/{document}/verify', [SpmbController::class, 'verifyDocument'])->name('spmb.admin.documents.verify');
        Route::get('/spmb-admin/documents/{document}/file', [SpmbController::class, 'showDocumentFile'])->name('spmb.admin.documents.file');
        Route::get('/spmb-admin/scoring/{wave}', [SpmbController::class, 'scoring'])->name('spmb.admin.scoring');
        Route::post('/spmb-admin/scoring/{wave}', [SpmbController::class, 'storeScoring'])->name('spmb.admin.scoring.store');
        Route::get('/spmb-admin/selection/{wave}', [SpmbController::class, 'selection'])->name('spmb.admin.selection');
        Route::post('/spmb-admin/selection/{wave}/run', [SpmbController::class, 'runSelection'])->name('spmb.admin.selection.run');
        Route::get('/spmb-admin/reregistration/{wave}', [SpmbController::class, 'reregistration'])->name('spmb.admin.reregistration');
        Route::post('/spmb-admin/reregistration/{registration}/confirm', [SpmbController::class, 'confirmReregistered'])->name('spmb.admin.reregistration.confirm');
        Route::post('/spmb-admin/bulk-convert/{wave}', [SpmbController::class, 'bulkConvert'])->name('spmb.admin.bulk-convert');

        Route::get('/spmb-admin', [SpmbController::class, 'index'])->name('spmb.admin.index');
        Route::get('/spmb-admin/{registration}/photo', [SpmbController::class, 'adminPhoto'])->name('spmb.admin.photo');
        Route::get('/spmb-admin/{registration}', [SpmbController::class, 'show'])->name('spmb.admin.show');
        Route::post('/spmb-admin/{registration}/status', [SpmbController::class, 'updateStatus'])->name('spmb.admin.status');

        // Security Monitoring & Audit Trail (SIEM)
        Route::middleware('role:admin,superadmin')->prefix('admin/security')->name('admin.security.')->group(function () {
            Route::get('/dashboard', [SecurityController::class, 'dashboard'])->name('dashboard');
            Route::get('/audit', [SecurityController::class, 'audit'])->name('audit');
            Route::get('/sessions', [SecurityController::class, 'sessions'])->name('sessions');
            Route::delete('/sessions/{session}', [SecurityController::class, 'terminateSession'])->name('sessions.terminate');
        });
    });

    // =========================================================
    // E-LEARNING PORTAL & PWA
    // =========================================================
    Route::get('elearning/install', [ElearningController::class, 'install'])->name('elearning.install');
    Route::get('elearning', [ElearningController::class, 'index'])->name('elearning.index');
    Route::get('elearning/class/{elearningClass}', [ElearningController::class, 'showClass'])->name('elearning.class.show');
    Route::get('elearning/class/{elearningClass}/material/{material}', [ElearningController::class, 'showMaterial'])->name('elearning.material.show');
    Route::post('elearning/material/{material}/comment', [ElearningController::class, 'storeComment'])->name('elearning.comment.store');
    Route::delete('elearning/material/{material}/comment/{comment}', [ElearningController::class, 'deleteComment'])->name('elearning.comment.destroy');

    // Student Actions
    Route::middleware('role:student,siswa')->group(function () {
        Route::get('elearning/assignment/{elearningAssignment}', [ElearningController::class, 'showAssignment'])->name('elearning.assignment.show');
        Route::get('elearning/assignment/{elearningAssignment}/secure', [ElearningSecureController::class, 'secureView'])->name('elearning.assignment.secure');
        Route::post('elearning/assignment/{elearningAssignment}/submit', [ElearningController::class, 'submitAssignment'])->name('elearning.assignment.submit');
        Route::post('elearning/assignment/{elearningAssignment}/log-violation', [ElearningSecureController::class, 'logViolation'])->name('elearning.assignment.violation');
    });

    // Teacher & Admin Actions
    Route::middleware('role:guru,teacher,staf_tu,admin,superadmin')->group(function () {
        Route::post('elearning', [ElearningController::class, 'storeClass'])->name('elearning.store');
        Route::post('elearning/class/{elearningClass}/material', [ElearningController::class, 'storeMaterial'])->name('elearning.material.store');
        Route::post('elearning/class/{elearningClass}/assignment', [ElearningController::class, 'storeAssignment'])->name('elearning.assignment.store');
        Route::get('elearning/assignment/{elearningAssignment}/submissions', [ElearningController::class, 'viewSubmissions'])->name('elearning.assignment.submissions');
        Route::post('elearning/submission/{elearningSubmission}/grade', [ElearningController::class, 'gradeSubmission'])->name('elearning.submission.grade');

        // Virtual Class Management (Archive, Duplicate)
        Route::post('elearning/class/{elearningClass}/archive', [ElearningController::class, 'archiveClass'])->name('elearning.class.archive');
        Route::post('elearning/class/{elearningClass}/unarchive', [ElearningController::class, 'unarchiveClass'])->name('elearning.class.unarchive');
        Route::post('elearning/class/{elearningClass}/duplicate', [ElearningController::class, 'duplicateClass'])->name('elearning.class.duplicate');

        // Announcements
        Route::post('elearning/class/{elearningClass}/announcement', [ElearningController::class, 'storeAnnouncement'])->name('elearning.announcement.store');
        Route::delete('elearning/class/{elearningClass}/announcement/{announcement}', [ElearningController::class, 'deleteAnnouncement'])->name('elearning.announcement.destroy');

        // Progress Tracking
        Route::get('elearning/class/{elearningClass}/progress', [ElearningController::class, 'classProgress'])->name('elearning.class.progress');
        Route::get('elearning/class/{elearningClass}/progress/student/{student}', [ElearningController::class, 'studentProgress'])->name('elearning.class.student-progress');
    });

    // =========================================================
    // SURVEI & KUESIONER DIGITAL (MODUL 2)
    // =========================================================
    Route::get('surveys', [SurveyController::class, 'index'])->name('surveys.index');
    Route::get('surveys/fill/{survey}', [SurveyController::class, 'show'])->name('surveys.show');
    Route::post('surveys/fill/{survey}/respond', [SurveyController::class, 'respond'])->name('surveys.respond');

    Route::middleware('role:waka_kurikulum,waka_kesiswaan,waka_humas,waka_sarpras,admin,superadmin,kepala_sekolah')->group(function () {
        Route::get('surveys/create', [SurveyController::class, 'create'])->name('surveys.create');
        Route::post('surveys', [SurveyController::class, 'store'])->name('surveys.store');
        Route::get('surveys/results/{survey}', [SurveyController::class, 'results'])->name('surveys.results');
        Route::post('surveys/{survey}/activate', [SurveyController::class, 'activate'])->name('surveys.activate');
        Route::post('surveys/{survey}/close', [SurveyController::class, 'close'])->name('surveys.close');
        Route::get('surveys/{survey}/export/pdf', [SurveyController::class, 'exportPdf'])->name('surveys.export.pdf');
        Route::get('surveys/{survey}/export/excel', [SurveyController::class, 'exportExcel'])->name('surveys.export.excel');
        Route::delete('surveys/{survey}', [SurveyController::class, 'destroy'])->name('surveys.destroy');
    });

    // =========================================================
    // ðŸ“š MANAJEMEN KURIKULUM & SILABUS DIGITAL
    // =========================================================
    Route::prefix('curriculum')->name('curriculum.')->group(function () {
        // Dashboard & PDF Report
        Route::get('/', [CurriculumController::class, 'index'])->name('index');
        Route::get('/report', [CurriculumController::class, 'completionReport'])->name('report');

        // Bank Silabus
        Route::get('/syllabi', [CurriculumController::class, 'syllabiIndex'])->name('syllabi.index');
        Route::post('/syllabi', [CurriculumController::class, 'syllabusStore'])->name('syllabi.store');
        Route::post('/syllabi/{syllabus}/review', [CurriculumController::class, 'syllabusReview'])->name('syllabi.review');
        Route::get('/syllabi/{syllabus}/download', [CurriculumController::class, 'syllabusDownload'])->name('syllabi.download');
        Route::delete('/syllabi/{syllabus}', [CurriculumController::class, 'syllabusDestroy'])->name('syllabi.destroy');

        // Rencana Pelaksanaan Pembelajaran (RPP)
        Route::get('/lesson-plans', [CurriculumController::class, 'lessonPlansIndex'])->name('lesson-plans.index');
        Route::get('/lesson-plans/create', [CurriculumController::class, 'lessonPlanCreate'])->name('lesson-plans.create');
        Route::post('/lesson-plans', [CurriculumController::class, 'lessonPlanStore'])->name('lesson-plans.store');
        Route::get('/lesson-plans/{lessonPlan}', [CurriculumController::class, 'lessonPlanShow'])->name('lesson-plans.show');
        Route::get('/lesson-plans/{lessonPlan}/edit', [CurriculumController::class, 'lessonPlanEdit'])->name('lesson-plans.edit');
        Route::put('/lesson-plans/{lessonPlan}', [CurriculumController::class, 'lessonPlanUpdate'])->name('lesson-plans.update');
        Route::delete('/lesson-plans/{lessonPlan}', [CurriculumController::class, 'lessonPlanDestroy'])->name('lesson-plans.destroy');
        Route::post('/lesson-plans/{lessonPlan}/submit', [CurriculumController::class, 'lessonPlanSubmit'])->name('lesson-plans.submit');
        Route::post('/lesson-plans/{lessonPlan}/review', [CurriculumController::class, 'lessonPlanReview'])->name('lesson-plans.review');
        Route::get('/lesson-plans/{lessonPlan}/pdf', [CurriculumController::class, 'lessonPlanPdf'])->name('lesson-plans.pdf');
    });

    // =========================================================
    // ðŸŽ« SISTEM HELPDESK INTERNAL
    // =========================================================
    Route::prefix('helpdesk')->name('helpdesk.')->group(function () {
        // Universal support ticket routes
        Route::resource('tickets', TicketController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('tickets/{ticket}/comment', [TicketController::class, 'comment'])->name('tickets.comment');

        // Administrative support handling routes
        Route::middleware('role:staf_tu,waka_sarpras,admin,superadmin')->group(function () {
            Route::get('dashboard', [TicketController::class, 'dashboard'])->name('dashboard');
            Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
            Route::post('tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
            Route::post('tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
        });
    });

    // =========================================================
    // ðŸ“ UJIAN ONLINE / CBT (COMPUTER-BASED TEST)
    // =========================================================
    Route::prefix('cbt')->name('cbt.')->group(function () {
        // Bank Soal — Guru, Waka Kurikulum, Staf TU & Admin
        Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'waka_kurikulum', 'staf_tu', 'admin'))->group(function () {
            Route::resource('question-banks', QuestionBankController::class);
        });

        // Exam Authoring & Editing (register `create` before `exams/{exam}`)
        Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'waka_kurikulum', 'admin'))->group(function () {
            Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
            Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
            Route::get('exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
            Route::match(['put', 'patch'], 'exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
            Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
        });

        // Exam Listing & Results — Guru, Siswa, Waka Kurikulum, Kepala Sekolah & Admin
        Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, StudentDataAccess::STUDENT_ROLES, 'waka_kurikulum', Roles::PRINCIPAL, 'admin'))->group(function () {
            Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
            Route::get('exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
            Route::get('exams/{exam}/results', [ExamController::class, 'results'])->name('exams.results');
        });

        // Proctor Monitor & Item Analysis — Guru, Waka Kurikulum, Kepala Sekolah & Admin
        Route::middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'waka_kurikulum', Roles::PRINCIPAL, 'admin'))->group(function () {
            Route::get('exams/{exam}/monitor', [ExamController::class, 'monitor'])->name('exams.monitor');
            Route::get('exams/{exam}/analysis', [ExamController::class, 'itemAnalysis'])->name('exams.analysis');
            Route::get('exams/{exam}/analysis/pdf', [ExamController::class, 'exportAnalysisPdf'])->name('exams.analysis.pdf');
        });

        // Exam Taking — Siswa
        Route::middleware(Roles::only(StudentDataAccess::STUDENT_ROLES))->group(function () {
            Route::get('exams/{exam}/take', [ExamController::class, 'take'])->name('exams.take');
            Route::post('exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
            Route::post('exams/{exam}/save-answers', [ExamController::class, 'saveAnswers'])->name('exams.save-answers');
            Route::post('exams/{exam}/log-violation', [ExamController::class, 'logViolation'])->name('exams.log-violation');
            Route::post('exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');
        });
    });

    // =========================================================
    // DIGITAL APPROVAL WORKFLOW ENGINE & UNIFIED INBOX
    // =========================================================
    Route::middleware(Roles::only(Roles::LEADERSHIP, StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'bk', 'staf_tu', 'bendahara', 'pustakawan', 'petugas_uks', 'admin'))
        ->prefix('workflows')
        ->name('workflows.')
        ->group(function () {
            Route::middleware('role:admin,superadmin,kepala_sekolah')->group(function () {
                Route::get('/dashboard', [WorkflowController::class, 'dashboard'])->name('dashboard');
                Route::get('/definitions', [WorkflowController::class, 'definitions'])->name('definitions');
                Route::post('/definitions', [WorkflowController::class, 'storeDefinition'])->name('definitions.store');
                Route::put('/definitions/{id}', [WorkflowController::class, 'updateDefinition'])->name('definitions.update');
                Route::delete('/definitions/{id}', [WorkflowController::class, 'deleteDefinition'])->name('definitions.delete');
                Route::get('/analytics', [WorkflowController::class, 'analytics'])->name('analytics');
            });

            Route::get('/history', [WorkflowController::class, 'history'])->name('history');
            Route::get('/pending', [WorkflowController::class, 'pending'])->name('pending');
            Route::get('/my-requests', [WorkflowController::class, 'myRequests'])->name('my-requests');

            Route::get('/{workflowRequest}', [WorkflowController::class, 'show'])->name('show');
            Route::post('/{workflowRequest}/approve', [WorkflowController::class, 'approve'])->name('approve');
            Route::post('/{workflowRequest}/reject', [WorkflowController::class, 'reject'])->name('reject');
            Route::post('/{workflowRequest}/revise', [WorkflowController::class, 'revise'])->name('revise');
            Route::post('/{workflowRequest}/resubmit', [WorkflowController::class, 'resubmit'])->name('resubmit');
            Route::post('/{workflowRequest}/delegate', [WorkflowController::class, 'delegate'])->name('delegate');
        });

    // =========================================================
    // ðŸ“¥ DATA IMPORT/EXPORT HUB UNIVERSAL (MODUL 7)
    // =========================================================
    Route::middleware('role:admin,superadmin,staf_tu')->prefix('import-export')->name('import-export.')->group(function () {
        Route::get('/', [ImportExportController::class, 'index'])->name('index');
        Route::post('upload', [ImportExportController::class, 'upload'])->name('upload');
        Route::post('execute', [ImportExportController::class, 'execute'])->name('execute');
        Route::post('export', [ImportExportController::class, 'exportData'])->name('export');
        Route::get('history', [ImportExportController::class, 'history'])->name('history');
        Route::post('rollback/{log}', [ImportExportController::class, 'rollback'])->name('rollback');
    });

    // =========================================================
    // ðŸ“§ SCHEDULED REPORTING & EMAIL DIGEST (MODUL 8)
    // =========================================================
    Route::middleware('role:admin,superadmin,staf_tu')->prefix('admin/scheduled-reports')->name('admin.scheduled-reports.')->group(function () {
        Route::get('/', [ScheduledReportController::class, 'index'])->name('index');
        Route::post('/', [ScheduledReportController::class, 'store'])->name('store');
        Route::put('/{report}', [ScheduledReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [ScheduledReportController::class, 'destroy'])->name('destroy');
        Route::post('/{report}/trigger', [ScheduledReportController::class, 'triggerImmediate'])->name('trigger');
    });

    // =========================================================
    // ðŸ”— INTEGRASI DAPODIK & SISTEM PEMERINTAH (MODUL 2)
    // =========================================================
    Route::middleware('role:admin,superadmin,staf_tu')->prefix('admin/integrations')->name('admin.integrations.')->group(function () {
        Route::get('/', [IntegrationController::class, 'index'])->name('index');
        Route::get('/dapodik', [IntegrationController::class, 'configDapodik'])->name('dapodik');
        Route::post('/dapodik/import', [IntegrationController::class, 'importDapodik'])->name('dapodik.import');
        Route::post('/dapodik/export', [IntegrationController::class, 'exportDapodik'])->name('dapodik.export');
        Route::get('/dapodik/sync-queue', [IntegrationController::class, 'dapodikSyncQueue'])->name('dapodik.sync-queue');
        Route::post('/dapodik/sync-queue/bulk-sync', [IntegrationController::class, 'bulkSyncDapodikItems'])->name('dapodik.sync-queue.bulk');
        Route::post('/dapodik/sync-queue/{item}/synced', [IntegrationController::class, 'markDapodikSyncItem'])->name('dapodik.sync-queue.mark');
        Route::post('/dapodik/sync-queue/{item}/dismiss', [IntegrationController::class, 'dismissDapodikSyncItem'])->name('dapodik.sync-queue.dismiss');
        Route::get('/emis', [IntegrationController::class, 'configEmis'])->name('emis');
        Route::post('/emis/export', [IntegrationController::class, 'exportEmis'])->name('emis.export');
        Route::get('/webhooks', [IntegrationController::class, 'webhooks'])->name('webhooks.index');
        Route::post('/webhooks', [IntegrationController::class, 'storeWebhook'])->name('webhooks.store');
        Route::put('/webhooks/{webhook}', [IntegrationController::class, 'updateWebhook'])->name('webhooks.update');
        Route::delete('/webhooks/{webhook}', [IntegrationController::class, 'destroyWebhook'])->name('webhooks.destroy');
        Route::post('/webhooks/{webhook}/test', [IntegrationController::class, 'testWebhook'])->name('webhooks.test');
        Route::get('/logs', [IntegrationController::class, 'logs'])->name('logs');
    });

    // =========================================================
    // ðŸŽ¨ KUSTOMISASI TAMPILAN SEKOLAH (MODUL 6)
    // =========================================================
    Route::middleware('role:admin,superadmin')->prefix('admin/appearance')->name('admin.appearance.')->group(function () {
        Route::get('/', [AppearanceController::class, 'index'])->name('index');
        Route::put('/', [AppearanceController::class, 'update'])->name('update');
        Route::post('/reset', [AppearanceController::class, 'reset'])->name('reset');
    });

    // =========================================================
    // ðŸ“‹ MEMO & NOTULENSI RAPAT DIGITAL (MODUL 15.1)
    // =========================================================
    Route::middleware('role:admin,superadmin,staf_tu,kepala_sekolah,guru')->group(function () {
        Route::get('meeting-notes', [MeetingNoteController::class, 'index'])->name('meeting-notes.index');
        Route::get('meeting-notes/create', [MeetingNoteController::class, 'create'])->name('meeting-notes.create');
        Route::post('meeting-notes', [MeetingNoteController::class, 'store'])->name('meeting-notes.store');
        Route::get('meeting-notes-tracker', [MeetingNoteController::class, 'actionItemTracker'])->name('meeting-notes.tracker');
        Route::get('meeting-notes/{meeting_note}', [MeetingNoteController::class, 'show'])->name('meeting-notes.show');
        Route::get('meeting-notes/{meeting_note}/edit', [MeetingNoteController::class, 'edit'])->name('meeting-notes.edit');
        Route::put('meeting-notes/{meeting_note}', [MeetingNoteController::class, 'update'])->name('meeting-notes.update');
        Route::post('meeting-notes/{meeting_note}/finalize', [MeetingNoteController::class, 'finalize'])->name('meeting-notes.finalize');
        Route::get('meeting-notes/{meeting_note}/pdf', [MeetingNoteController::class, 'exportPdf'])->name('meeting-notes.pdf');
        Route::post('meeting-notes/action-items/{meeting_note}/{itemId}/toggle', [MeetingNoteController::class, 'toggleActionItem'])->name('meeting-notes.action-items.toggle');
    });

});

// Force Change Password (for users with default/temporary password)
Route::middleware('auth')->group(function () {
    Route::get('/force-change-password', [ForceChangePasswordController::class, 'create'])->name('password.force-change');
    Route::post('/force-change-password', [ForceChangePasswordController::class, 'store'])->name('password.force-change.update');

    // Two Factor Authentication
    Route::get('verify-2fa', [TwoFactorController::class, 'index'])->name('verify-2fa.index');
    Route::post('verify-2fa', [TwoFactorController::class, 'store'])->name('verify-2fa.store');
    Route::get('verify-2fa/resend', [TwoFactorController::class, 'resend'])->name('verify-2fa.resend');
});

// --- Google SSO & Mobile App SSO Bridge (Single Sign-On) ---
Route::get('sso/auto-login', [MobileSsoBridgeController::class, 'autoLogin'])->name('sso.auto_login');
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [GoogleSsoController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleSsoController::class, 'handleGoogleCallback']);
});

// --- User Preferences (i18n & Theme Switches) ---
Route::match(['get', 'post'], 'preferences/locale/{locale}', [UserPreferenceController::class, 'setLocale'])->name('preferences.locale');
Route::match(['get', 'post'], 'preferences/theme/{theme}', [UserPreferenceController::class, 'setTheme'])->name('preferences.theme');

// =========================================================
// FINANCE â€” Tabungan Wadiah & Cashless Kantin (Admin/TU/Merchant)
// =========================================================
Route::middleware(['auth'])->group(function () {

    Route::middleware('role:staf_tu,admin,superadmin')->prefix('admin/finance/charity')->name('admin.finance.charity.')->group(function () {
        Route::get('/', [CharityController::class, 'index'])->name('index');
        Route::get('create', [CharityController::class, 'create'])->name('create');
        Route::post('store', [CharityController::class, 'store'])->name('store');
        Route::get('{id}/edit', [CharityController::class, 'edit'])->name('edit');
        Route::put('{id}/update', [CharityController::class, 'update'])->name('update');
        Route::get('{campaignId}/donors', [CharityController::class, 'campaignDonors'])->name('donors');
        Route::post('donations/{id}/verify', [CharityController::class, 'verify'])->name('verify');
        Route::post('donations/{id}/cancel', [CharityController::class, 'cancel'])->name('cancel');
    });

    // Any school member may give by transfer or cash; only students and their parents
    // can pay from wadiah savings (CharityService). Used to be `student,parent` only,
    // which locked out `siswa`/`orang_tua` accounts and staff.
    Route::middleware(Roles::only(Roles::MEMBERS))->prefix('portal/charity')->name('portal.charity.')->group(function () {
        Route::get('/', [CharityController::class, 'portal'])->name('portal');
        Route::get('donate/{campaignId}', [CharityController::class, 'donateForm'])->name('donate');
        Route::post('donate', [CharityController::class, 'processDonate'])->name('donate.store');
        Route::get('history', [CharityController::class, 'history'])->name('history');
        Route::get('donations/{id}/certificate', [CharityController::class, 'downloadCertificate'])->name('certificate');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/security')->name('admin.security.')->group(function () {
        Route::get('/', [SecurityDashboardController::class, 'dashboard'])->name('index');
        Route::get('logs', [SecurityDashboardController::class, 'threatLogs'])->name('logs');
        Route::post('lockdown', [SecurityDashboardController::class, 'toggleLockdown'])->name('lockdown');
        Route::post('block-ip', [SecurityDashboardController::class, 'blockIp'])->name('block-ip');
        Route::delete('unblock-ip/{id}', [SecurityDashboardController::class, 'unblockIp'])->name('unblock-ip');
        Route::post('resolve-threat/{id}', [SecurityDashboardController::class, 'resolveThreat'])->name('resolve-threat');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/school')->name('admin.school.')->group(function () {
        Route::get('branding', [SchoolBrandingController::class, 'index'])->name('branding.index');
        Route::post('branding', [SchoolBrandingController::class, 'update'])->name('branding.update');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/security/rbac')->name('admin.security.rbac.')->group(function () {
        Route::get('/', [RbacAuditController::class, 'index'])->name('index');
        Route::post('verify-ledger', [RbacAuditController::class, 'verifyLedgerIntegrity'])->name('verify-ledger');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/security/privacy')->name('admin.security.privacy.')->group(function () {
        Route::get('/', [PdplPrivacyController::class, 'index'])->name('index');
        Route::post('preview', [PdplPrivacyController::class, 'anonymizePreview'])->name('preview');
        Route::post('right-to-be-forgotten', [PdplPrivacyController::class, 'executeRightToBeForgotten'])->name('right-to-be-forgotten');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/system/feature-flags')->name('admin.system.feature-flags.')->group(function () {
        Route::get('/', [FeatureFlagController::class, 'index'])->name('index');
        Route::post('toggle/{flag}', [FeatureFlagController::class, 'toggle'])->name('toggle');
        Route::post('update/{flag}', [FeatureFlagController::class, 'update'])->name('update');
    });

    Route::middleware(['auth'])->prefix('admin/security/2fa')->name('admin.security.2fa.')->group(function () {
        Route::get('/', [SecurityTwoFactorController::class, 'index'])->name('index');
        Route::post('confirm', [SecurityTwoFactorController::class, 'confirm'])->name('confirm');
        Route::post('disable', [SecurityTwoFactorController::class, 'disable'])->name('disable');
        Route::get('elevate', [SecurityTwoFactorController::class, 'showElevateForm'])->name('elevate_form');
        Route::post('elevate', [SecurityTwoFactorController::class, 'elevate'])->name('elevate');
    });

    Route::middleware('role:staf_tu,admin,superadmin')->prefix('admin/integration/sync')->name('admin.integration.sync.')->group(function () {
        Route::get('dashboard', [SyncController::class, 'dashboard'])->name('dashboard');
        Route::get('mapping/{target}/{table}', [SyncController::class, 'mappingForm'])->name('mapping');
        Route::post('mapping', [SyncController::class, 'saveMapping'])->name('mapping.store');
        Route::get('validation/{target}/{table}', [SyncController::class, 'runValidation'])->name('validation');
        Route::get('export/{target}/{table}', [SyncController::class, 'exportData'])->name('export');
        Route::get('history', [SyncController::class, 'syncHistory'])->name('history');
    });

    Route::get('assets/maintenance/scan/{uuid}', [AssetMaintenanceTicketController::class, 'scanQr'])->name('admin.assets.maintenance.scan');
    Route::post('assets/maintenance/ticket', [AssetMaintenanceTicketController::class, 'storeTicket'])->name('admin.assets.maintenance.store');

    Route::middleware('role:staf_tu,admin,superadmin')->prefix('admin/assets/maintenance')->name('admin.assets.maintenance.')->group(function () {
        Route::get('/', [AssetMaintenanceTicketController::class, 'index'])->name('index');
        Route::get('tickets/{id}', [AssetMaintenanceTicketController::class, 'showTicket'])->name('show');
        Route::post('tickets/{id}/status', [AssetMaintenanceTicketController::class, 'updateTicketStatus'])->name('update-status');
        Route::get('print-qr', [AssetMaintenanceTicketController::class, 'generateQrLabels'])->name('print-qr');
    });

    // Katalog Jabatan Dinamis & Penetapan Pejabat (L1–L2)
    Route::middleware('can:sistem.kelola_jabatan')->prefix('admin/positions')->name('admin.positions.')->group(function () {
        Route::get('/', [PositionCatalogController::class, 'index'])->name('index');
        Route::post('/', [PositionCatalogController::class, 'store'])->name('store');
        Route::put('{position}', [PositionCatalogController::class, 'update'])->name('update');
        Route::delete('{position}', [PositionCatalogController::class, 'destroy'])->name('destroy');
        Route::post('assignments', [PositionCatalogController::class, 'storeAssignment'])->name('assignments.store');
        Route::delete('assignments/{assignment}', [PositionCatalogController::class, 'destroyAssignment'])->name('assignments.destroy');
    });

    // Siklus Tahun Ajaran, Kalender Akademik, Survei Peminatan & Kenaikan Kelas (A0–A4)
    Route::prefix('academic/cycle')->name('academic.cycle.')->group(function () {
        Route::get('/', [AcademicCycleController::class, 'index'])
            ->middleware('role:waka_kurikulum,kepala_sekolah,guru,teacher,wali_kelas,admin,superadmin')
            ->name('index');

        Route::middleware('can:akademik.kelola_struktur')->group(function () {
            Route::post('school-years', [AcademicCycleController::class, 'storeSchoolYear'])->name('school-years.store');
            Route::post('surveys', [AcademicCycleController::class, 'storeSurvey'])->name('surveys.store');
            Route::put('classrooms/{classroom}/electives', [AcademicCycleController::class, 'configureClassroomElectives'])->name('classrooms.electives');
            Route::post('placements', [AcademicCycleController::class, 'placeStudents'])->name('placements.store');
            Route::post('surveys/{survey}/announce', [AcademicCycleController::class, 'announceSurvey'])->name('surveys.announce');
            Route::post('promotion-batches', [AcademicCycleController::class, 'createPromotionBatch'])->name('promotion-batches.store');
            Route::post('promotion-batches/{batch}/decide', [AcademicCycleController::class, 'decideBatch'])->name('promotion-batches.decide');
            Route::post('promotion-batches/{batch}/execute', [AcademicCycleController::class, 'executeBatch'])->name('promotion-batches.execute');
        });

        Route::middleware('can:pimpinan.mengesahkan')->group(function () {
            Route::post('calendars/{calendar}/approve', [AcademicCycleController::class, 'approveCalendar'])->name('calendars.approve');
            Route::post('promotion-batches/{batch}/hold', [AcademicCycleController::class, 'holdBatch'])->name('promotion-batches.hold');
        });

        Route::post('promotion-decisions/{decision}/review', [AcademicCycleController::class, 'reviewDecision'])
            ->middleware('role:guru,teacher,wali_kelas,waka_kurikulum,admin,superadmin')
            ->name('promotion-decisions.review');
    });
});

require __DIR__.'/treasury.php';

require __DIR__.'/tahsin.php';
require __DIR__.'/auth.php';

require __DIR__.'/bullying.php';

require __DIR__.'/inclusive_education.php';

// --- Mobile Universal Links & Android App Links (.well-known) ---
Route::get('/.well-known/assetlinks.json', [WellKnownController::class, 'assetLinks'])->name('well-known.assetlinks');
Route::get('/.well-known/apple-app-site-association', [WellKnownController::class, 'appleAppSiteAssociation'])->name('well-known.apple-app-site-association');

// --- PWA (Progressive Web App) Routes ---
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('pwa.service-worker');
Route::post('/pwa/subscribe', [PwaController::class, 'subscribe'])->middleware('auth')->name('pwa.subscribe');
