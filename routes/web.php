<?php

use App\Http\Controllers\Academic\AcademicPathwayController;
use App\Http\Controllers\Academic\AiCopilotController;
use App\Http\Controllers\Academic\CompetencyMappingController;
use App\Http\Controllers\Academic\ScormController;
use App\Http\Controllers\Academic\VirtualClassroomController;
use App\Http\Controllers\AcademicEventController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\ApiGatewayMonitorController;
use App\Http\Controllers\Admin\ApmObservabilityController;
use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\AssetMaintenanceTicketController;
use App\Http\Controllers\Admin\BpaRuleEngineController;
use App\Http\Controllers\Admin\CacheMonitorController;
use App\Http\Controllers\Admin\DeveloperPortalController;
use App\Http\Controllers\Admin\DisasterRecoveryController;
use App\Http\Controllers\Admin\EdwOlapController;
use App\Http\Controllers\Admin\FeatureFlagController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\ObservabilityController;
use App\Http\Controllers\Admin\PdplPrivacyController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\Admin\RbacAuditController;
use App\Http\Controllers\Admin\ReportBuilderController;
use App\Http\Controllers\Admin\ScheduledReportController;
use App\Http\Controllers\Admin\SchoolBrandingController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SecurityDashboardController;
use App\Http\Controllers\Admin\SecurityTwoFactorController;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Admin\UatFeedbackController;
use App\Http\Controllers\Admin\VendorSlaController;
use App\Http\Controllers\Admin\WebhookHubController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\Analytics\AcademicBenchmarkController;
use App\Http\Controllers\Analytics\FinancialForecastingController;
use App\Http\Controllers\Analytics\ParentEngagementController;
use App\Http\Controllers\Analytics\PredictivePerformanceController;
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
use App\Http\Controllers\CareerOpportunityController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\Communication\CalendarController;
use App\Http\Controllers\Communication\DocumentSignatureController;
use App\Http\Controllers\Communication\MessagingController;
use App\Http\Controllers\Communication\NotificationCenterController;
use App\Http\Controllers\Communication\ResourceBookingController;
use App\Http\Controllers\Community\MentorshipController;
use App\Http\Controllers\Community\MobileAppGatewayController;
use App\Http\Controllers\Community\PartnershipController;
use App\Http\Controllers\Community\VolunteerController;
use App\Http\Controllers\Compliance\AuditConsoleController;
use App\Http\Controllers\Compliance\DapodikExportController;
use App\Http\Controllers\Compliance\DataRetentionController;
use App\Http\Controllers\Compliance\LegalContractController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\Content\ExportEngineController;
use App\Http\Controllers\Content\MediaAssetController;
use App\Http\Controllers\Content\OcrDocumentController;
use App\Http\Controllers\CounselingController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DailyAssessmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Deployment\E2eTestController;
use App\Http\Controllers\Deployment\EnvironmentConfigController;
use App\Http\Controllers\Deployment\ReleaseManagerController;
use App\Http\Controllers\EdsController;
use App\Http\Controllers\ElearningController;
use App\Http\Controllers\ElearningSecureController;
use App\Http\Controllers\EventAttendanceController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\Finance\CanteenController;
use App\Http\Controllers\Finance\CharityController;
use App\Http\Controllers\Finance\FinancialAuditController;
use App\Http\Controllers\Finance\WadiahController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\Financial\BudgetPlanningController;
use App\Http\Controllers\Financial\CostCenterController;
use App\Http\Controllers\Financial\ProcurementController;
use App\Http\Controllers\Financial\ScholarshipController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\Grc\AccreditationController;
use App\Http\Controllers\Grc\QualityAssuranceController;
use App\Http\Controllers\Grc\RiskManagementController;
use App\Http\Controllers\Grc\SopGovernanceController;
use App\Http\Controllers\Growth\P5AssessmentController;
use App\Http\Controllers\Growth\PtcSchedulerController;
use App\Http\Controllers\Growth\StudentLifecycleController;
use App\Http\Controllers\Growth\WellbeingController;
use App\Http\Controllers\HRD\EmployeeController;
use App\Http\Controllers\HRD\MyHRDController;
use App\Http\Controllers\HRD\PayrollController;
use App\Http\Controllers\HumanCapital\CpdTrackerController;
use App\Http\Controllers\HumanCapital\Evaluation360Controller;
use App\Http\Controllers\HumanCapital\SuccessionPlanningController;
use App\Http\Controllers\HumanCapital\WorkloadBalancingController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\Integration\ApiDocumentationController;
use App\Http\Controllers\Integration\ApiGatewayController;
use App\Http\Controllers\Integration\EtlController;
use App\Http\Controllers\Integration\PluginController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LibraryCirculationController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MeetingNoteController;
use App\Http\Controllers\MpkController;
use App\Http\Controllers\MutabaahController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\Operations\BusRoutingController;
use App\Http\Controllers\Operations\IotFacilityController;
use App\Http\Controllers\Operations\UtilityTrackerController;
use App\Http\Controllers\OsisController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\Performance\AssetPerformanceController;
use App\Http\Controllers\Performance\LoadTestController;
use App\Http\Controllers\Performance\QueryPerformanceController;
use App\Http\Controllers\Performance\ScalingController;
use App\Http\Controllers\Platform\AiRecommendationController;
use App\Http\Controllers\Platform\DashboardBuilderController;
use App\Http\Controllers\Platform\DeveloperApiController;
use App\Http\Controllers\Platform\DigitalTwinController;
use App\Http\Controllers\PointCategoryController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SmartAlertController;
use App\Http\Controllers\SpmbController;
use App\Http\Controllers\Student\CertificateController;
use App\Http\Controllers\Student\CourseEvaluationController;
use App\Http\Controllers\Student\DigitalIDController;
use App\Http\Controllers\Student\StudentPortalController;
use App\Http\Controllers\StudentAcademicController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentFinanceController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TahfidzController;
use App\Http\Controllers\TeacherTodoController;
use App\Http\Controllers\TeachingJournalController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TracerStudyController;
use App\Http\Controllers\UksController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\UX\AccessibilityController;
use App\Http\Controllers\UX\DesignSystemController;
use App\Http\Controllers\UX\PrintEngineController;
use App\Http\Controllers\UX\TranslationController;
use App\Http\Controllers\WellKnownController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\WorkflowController;
use App\Models\TeachingJournal;
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
Route::get('/verify-document/{code}', [DocumentSignatureController::class, 'verifyPublic'])->name('documents.verify_public');
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
});

// ============================================================
// PUBLIC E-PORTFOLIO SHOWCASE
// ============================================================
Route::get('portfolio/{uuid}', [PortfolioController::class, 'publicPortfolio'])->name('portfolios.public');

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

    // Career & Scholarship Portal (read-only untuk semua)
    Route::get('career-portal', [CareerOpportunityController::class, 'index'])->name('career-portal.index');

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

    // Digital Signature & E-Stamp Document Workflow
    Route::get('documents/pending-signatures', [DocumentSignatureController::class, 'pendingDocuments'])->name('documents.pending_signatures');
    Route::get('documents/signatures/{id}/sign', [DocumentSignatureController::class, 'signForm'])->name('documents.sign_form');
    Route::post('documents/signatures/{id}/sign', [DocumentSignatureController::class, 'signDocument'])->name('documents.sign');
    Route::post('documents/test-request', [DocumentSignatureController::class, 'testRequest'])->name('documents.test_request');
    Route::get('documents/signature-history', [DocumentSignatureController::class, 'signatureHistory'])->name('documents.signature_history');
    // Enterprise Calendar & Resource Booking System
    Route::get('calendar-hub', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar-hub/events-json', [CalendarController::class, 'eventsJson'])->name('calendar.events_json');
    Route::post('calendar-hub/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('calendar-hub/events/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('calendar-hub/events/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('calendar-hub/events/{id}/rsvp', [CalendarController::class, 'rsvp'])->name('calendar.rsvp');

    Route::get('resource-bookings', [ResourceBookingController::class, 'index'])->name('resource_bookings.index');
    Route::post('resource-bookings', [ResourceBookingController::class, 'createBooking'])->name('resource_bookings.store');
    Route::post('resource-bookings/{id}/approve', [ResourceBookingController::class, 'approveBooking'])->name('resource_bookings.approve');
    Route::post('resource-bookings/{id}/reject', [ResourceBookingController::class, 'rejectBooking'])->name('resource_bookings.reject');
    Route::get('resource-bookings/check', [ResourceBookingController::class, 'checkAvailability'])->name('resource_bookings.check');

    // FASE 33: Enterprise Financial Governance & Budgetary Control Suite
    // 33.1: RAPBS Multi-Year Budget Planning & Variance
    Route::get('financial/budget', [BudgetPlanningController::class, 'dashboard'])->name('financial.budget.dashboard');
    Route::get('financial/budget/create', [BudgetPlanningController::class, 'create'])->name('financial.budget.create');
    Route::post('financial/budget', [BudgetPlanningController::class, 'store'])->name('financial.budget.store');
    Route::get('financial/budget/{id}', [BudgetPlanningController::class, 'show'])->name('financial.budget.show');
    Route::post('financial/budget/{id}/line-items', [BudgetPlanningController::class, 'storeLineItem'])->name('financial.budget.store_line_item');
    Route::post('financial/budget/{id}/submit', [BudgetPlanningController::class, 'submitApproval'])->name('financial.budget.submit');
    Route::post('financial/budget/{id}/approve', [BudgetPlanningController::class, 'approve'])->name('financial.budget.approve');
    Route::get('financial/budget/{id}/variance', [BudgetPlanningController::class, 'varianceReport'])->name('financial.budget.variance');
    Route::get('financial/budget/{id}/forecast', [BudgetPlanningController::class, 'forecastReport'])->name('financial.budget.forecast');

    // 33.2: Procurement & Purchase Order Workflow
    Route::get('financial/procurement', [ProcurementController::class, 'dashboard'])->name('financial.procurement.dashboard');
    Route::get('financial/procurement/request', [ProcurementController::class, 'createRequest'])->name('financial.procurement.create_request');
    Route::post('financial/procurement/request', [ProcurementController::class, 'storeRequest'])->name('financial.procurement.store_request');
    Route::post('financial/procurement/{id}/submit', [ProcurementController::class, 'submitApproval'])->name('financial.procurement.submit');
    Route::post('financial/procurement/{id}/approve', [ProcurementController::class, 'approve'])->name('financial.procurement.approve');
    Route::post('financial/procurement/{id}/reject', [ProcurementController::class, 'reject'])->name('financial.procurement.reject');
    Route::get('financial/procurement/{id}/po', [ProcurementController::class, 'createPO'])->name('financial.procurement.create_po');
    Route::post('financial/procurement/{id}/po', [ProcurementController::class, 'storePO'])->name('financial.procurement.store_po');
    Route::post('financial/procurement/po/{poId}/grn', [ProcurementController::class, 'recordGRN'])->name('financial.procurement.record_grn');
    Route::get('financial/procurement/history', [ProcurementController::class, 'history'])->name('financial.procurement.history');

    // 33.3: Scholarship & Grant Management Portal
    Route::get('financial/scholarships', [ScholarshipController::class, 'programs'])->name('financial.scholarships.programs');
    Route::get('financial/scholarships/create', [ScholarshipController::class, 'createProgram'])->name('financial.scholarships.create_program');
    Route::post('financial/scholarships', [ScholarshipController::class, 'storeProgram'])->name('financial.scholarships.store_program');
    Route::post('financial/scholarships/{id}/open', [ScholarshipController::class, 'openApplications'])->name('financial.scholarships.open');
    Route::get('financial/scholarships/{programId}/apply', [ScholarshipController::class, 'applyForm'])->name('financial.scholarships.apply_form');
    Route::post('financial/scholarships/{programId}/apply', [ScholarshipController::class, 'submitApplication'])->name('financial.scholarships.submit_application');
    Route::get('financial/scholarships/{programId}/review', [ScholarshipController::class, 'reviewApplications'])->name('financial.scholarships.review');
    Route::post('financial/scholarships/applications/{appId}/review', [ScholarshipController::class, 'review'])->name('financial.scholarships.review_action');
    Route::get('financial/scholarships/applications/{appId}/disburse', [ScholarshipController::class, 'disbursementForm'])->name('financial.scholarships.disburse_form');
    Route::post('financial/scholarships/applications/{appId}/disburse', [ScholarshipController::class, 'disburse'])->name('financial.scholarships.disburse_action');
    Route::get('financial/scholarships/report', [ScholarshipController::class, 'report'])->name('financial.scholarships.report');

    // 33.4: Cost Center Accounting & Budget Allocation
    Route::get('financial/cost-centers', [CostCenterController::class, 'dashboard'])->name('financial.cost-centers.dashboard');
    Route::get('financial/cost-centers/allocate', [CostCenterController::class, 'allocateForm'])->name('financial.cost-centers.allocate_form');
    Route::post('financial/cost-centers', [CostCenterController::class, 'storeCostCenter'])->name('financial.cost-centers.store');
    Route::post('financial/cost-centers/{id}/allocate', [CostCenterController::class, 'storeAllocation'])->name('financial.cost-centers.store_allocation');
    Route::post('financial/cost-centers/{id}/expense', [CostCenterController::class, 'storeExpense'])->name('financial.cost-centers.store_expense');
    Route::get('financial/cost-centers/{id}/transactions', [CostCenterController::class, 'transactions'])->name('financial.cost-centers.transactions');

    // FASE 34: Student Growth Ecosystem -- Wellbeing, Positive Behavior & Parent Conference
    // 34.1: Student Mental Health & Wellbeing Screening
    Route::get('growth/wellbeing', [WellbeingController::class, 'screenings'])->name('growth.wellbeing.screenings');
    Route::get('growth/wellbeing/create', [WellbeingController::class, 'createScreening'])->name('growth.wellbeing.create');
    Route::post('growth/wellbeing', [WellbeingController::class, 'storeScreening'])->name('growth.wellbeing.store');
    Route::get('growth/wellbeing/{id}/fill', [WellbeingController::class, 'fillScreening'])->name('growth.wellbeing.fill');
    Route::post('growth/wellbeing/{id}/fill', [WellbeingController::class, 'submitScreening'])->name('growth.wellbeing.submit');
    Route::get('growth/wellbeing/results', [WellbeingController::class, 'results'])->name('growth.wellbeing.results');
    Route::get('growth/wellbeing/mood-journal', [WellbeingController::class, 'moodJournal'])->name('growth.wellbeing.mood_journal');
    Route::post('growth/wellbeing/mood-journal', [WellbeingController::class, 'storeMoodJournal'])->name('growth.wellbeing.store_mood_journal');

    // 34.2: Positive Behavior & Character Report Card (P5)
    Route::get('growth/character/dimensions', [P5AssessmentController::class, 'dimensions'])->name('growth.character.dimensions');
    Route::get('growth/character/assess', [P5AssessmentController::class, 'assessForm'])->name('growth.character.assess_form');
    Route::post('growth/character/assess', [P5AssessmentController::class, 'storeAssessment'])->name('growth.character.store_assessment');
    Route::get('growth/character/student/{studentId}', [P5AssessmentController::class, 'studentReport'])->name('growth.character.student_report');
    Route::get('growth/character/leaderboard', [P5AssessmentController::class, 'leaderboard'])->name('growth.character.leaderboard');
    Route::post('growth/character/merit', [P5AssessmentController::class, 'awardMerit'])->name('growth.character.award_merit');

    // 34.3: Parent-Teacher Conference (PTC) Scheduler
    Route::get('growth/ptc/sessions', [PtcSchedulerController::class, 'sessions'])->name('growth.ptc.sessions');
    Route::post('growth/ptc/sessions', [PtcSchedulerController::class, 'storeSession'])->name('growth.ptc.store_session');
    Route::get('growth/ptc/sessions/{sessionId}/slots', [PtcSchedulerController::class, 'availableSlots'])->name('growth.ptc.available_slots');
    Route::post('growth/ptc/slots/{slotId}/book', [PtcSchedulerController::class, 'bookSlot'])->name('growth.ptc.book_slot');
    Route::get('growth/ptc/teacher-schedule', [PtcSchedulerController::class, 'teacherSchedule'])->name('growth.ptc.teacher_schedule');

    // 34.4: Student Lifecycle Orchestration Engine
    Route::get('growth/lifecycle/student/{studentId}', [StudentLifecycleController::class, 'timeline'])->name('growth.lifecycle.timeline');
    Route::post('growth/lifecycle/student/{studentId}/event', [StudentLifecycleController::class, 'storeEvent'])->name('growth.lifecycle.store_event');
    Route::get('growth/lifecycle/retention', [StudentLifecycleController::class, 'retentionDashboard'])->name('growth.lifecycle.retention');
    Route::get('growth/lifecycle/at-risk', [StudentLifecycleController::class, 'atRiskStudents'])->name('growth.lifecycle.at_risk');

    // FASE 35: Enterprise Academic Intelligence -- Kurikulum Merdeka & LMS 2.0
    // 35.1: Competency Mapping (CP/TP/ATP)
    Route::get('academic/competency/outcomes', [CompetencyMappingController::class, 'outcomes'])->name('academic.competency.outcomes');
    Route::post('academic/competency/outcomes', [CompetencyMappingController::class, 'storeOutcome'])->name('academic.competency.store_outcome');
    Route::post('academic/competency/outcomes/{outcomeId}/objectives', [CompetencyMappingController::class, 'storeObjective'])->name('academic.competency.store_objective');
    Route::get('academic/competency/objectives/{objectiveId}/atp', [CompetencyMappingController::class, 'atpMapping'])->name('academic.competency.atp_mapping');
    Route::post('academic/competency/objectives/{objectiveId}/atp', [CompetencyMappingController::class, 'storeAtp'])->name('academic.competency.store_atp');
    Route::get('academic/competency/subjects/{subjectId}/heatmap', [CompetencyMappingController::class, 'classHeatmap'])->name('academic.competency.class_heatmap');

    // 35.2: Multi-Year Academic Planning & Graduation Pathway
    Route::get('academic/pathways', [AcademicPathwayController::class, 'pathways'])->name('academic.pathways.index');
    Route::post('academic/pathways/enroll', [AcademicPathwayController::class, 'storeEnrollment'])->name('academic.pathways.store_enrollment');
    Route::get('academic/pathways/student/{studentId}/progress', [AcademicPathwayController::class, 'progressDashboard'])->name('academic.pathways.progress_dashboard');
    Route::get('academic/pathways/graduation-readiness', [AcademicPathwayController::class, 'graduationReadiness'])->name('academic.pathways.graduation_readiness');

    // 35.3: SCORM/xAPI Learning Content Interoperability
    Route::get('academic/scorm/packages', [ScormController::class, 'packages'])->name('academic.scorm.packages');
    Route::post('academic/scorm/packages', [ScormController::class, 'storePackage'])->name('academic.scorm.store_package');
    Route::get('academic/scorm/packages/{id}/launch', [ScormController::class, 'launch'])->name('academic.scorm.launch');
    Route::post('academic/scorm/packages/{id}/track', [ScormController::class, 'recordTracking'])->name('academic.scorm.record_tracking');

    // 35.4: Virtual Classroom & Video Conferencing Hub
    Route::get('academic/virtual', [VirtualClassroomController::class, 'index'])->name('academic.virtual.index');
    Route::post('academic/virtual', [VirtualClassroomController::class, 'storeMeeting'])->name('academic.virtual.store_meeting');
    Route::get('academic/virtual/{id}/join', [VirtualClassroomController::class, 'joinLobby'])->name('academic.virtual.join_lobby');

    // FASE 36: Enterprise Human Capital Excellence -- 360 Evaluation, CPD & Leadership Pipeline
    // 36.1: 360-Degree Teacher Evaluation & EDOM
    Route::get('hc/evaluation/cycles', [Evaluation360Controller::class, 'cycles'])->name('hc.evaluation.cycles');
    Route::post('hc/evaluation/cycles', [Evaluation360Controller::class, 'storeCycle'])->name('hc.evaluation.store_cycle');
    Route::get('hc/evaluation/cycles/{cycleId}/fill', [Evaluation360Controller::class, 'fillForm'])->name('hc.evaluation.fill_form');
    Route::post('hc/evaluation/cycles/{cycleId}/fill', [Evaluation360Controller::class, 'submitEvaluation'])->name('hc.evaluation.submit');
    Route::get('hc/evaluation/teacher/{teacherId}/results', [Evaluation360Controller::class, 'results'])->name('hc.evaluation.results');
    Route::get('hc/evaluation/ranking', [Evaluation360Controller::class, 'ranking'])->name('hc.evaluation.ranking');

    // 36.2: CPD/PKB Credit Tracker
    Route::get('hc/cpd/dashboard', [CpdTrackerController::class, 'dashboard'])->name('hc.cpd.dashboard');
    Route::post('hc/cpd/log', [CpdTrackerController::class, 'logActivity'])->name('hc.cpd.log_activity');
    Route::get('hc/cpd/team-overview', [CpdTrackerController::class, 'teamOverview'])->name('hc.cpd.team_overview');
    Route::post('hc/cpd/verify/{id}', [CpdTrackerController::class, 'verifyActivity'])->name('hc.cpd.verify');

    // 36.3: School Leadership Succession Planning
    Route::get('hc/succession/positions', [SuccessionPlanningController::class, 'positions'])->name('hc.succession.positions');
    Route::post('hc/succession/candidates', [SuccessionPlanningController::class, 'storeCandidate'])->name('hc.succession.store_candidate');
    Route::get('hc/succession/pipeline-report', [SuccessionPlanningController::class, 'pipelineReport'])->name('hc.succession.pipeline_report');

    // 36.4: Teacher Workload Balancing & Capacity Dashboard
    Route::get('hc/workload/dashboard', [WorkloadBalancingController::class, 'dashboard'])->name('hc.workload.dashboard');
    Route::post('hc/workload', [WorkloadBalancingController::class, 'storeWorkload'])->name('hc.workload.store');
    Route::get('hc/workload/capacity-report', [WorkloadBalancingController::class, 'capacityReport'])->name('hc.workload.capacity_report');

    // FASE 37: Enterprise UX Mastery -- Accessibility, Internationalization & Design System
    // 37.1: WCAG 2.2 AA+ Accessibility Hardening
    Route::get('ux/accessibility/dashboard', [AccessibilityController::class, 'dashboard'])->name('ux.accessibility.dashboard');
    Route::get('ux/accessibility/report', [AccessibilityController::class, 'auditReport'])->name('ux.accessibility.report');

    // 37.2: Advanced i18n & Full Arabic (RTL) Support
    Route::get('ux/i18n/manager', [TranslationController::class, 'index'])->name('ux.i18n.manager');
    Route::post('ux/i18n/override', [TranslationController::class, 'override'])->name('ux.i18n.override');
    Route::get('ux/i18n/report', [TranslationController::class, 'completionReport'])->name('ux.i18n.report');

    // 37.3: Enterprise Design System Component Library
    Route::get('ux/design-system/showcase', [DesignSystemController::class, 'showcase'])->name('ux.design_system.showcase');
    Route::get('ux/design-system/documentation', [DesignSystemController::class, 'documentation'])->name('ux.design_system.documentation');

    // 37.4: Print-Optimized Stylesheet Engine
    Route::get('ux/print/preview', [PrintEngineController::class, 'preview'])->name('ux.print.preview');

    // FASE 38: Enterprise Deployment & Production Hardening Blueprint
    // 38.1: Production Environment & Secret Management
    Route::get('deploy/environment', [EnvironmentConfigController::class, 'dashboard'])->name('deploy.environment.dashboard');
    Route::get('deploy/environment/configure', [EnvironmentConfigController::class, 'configureForm'])->name('deploy.environment.configure_form');
    Route::post('deploy/environment', [EnvironmentConfigController::class, 'storeConfig'])->name('deploy.environment.store_config');

    // 38.3: Release Versioning, Semantic Changelog & Rollback Strategy
    Route::get('deploy/releases', [ReleaseManagerController::class, 'index'])->name('deploy.releases.index');
    Route::post('deploy/releases', [ReleaseManagerController::class, 'storeRelease'])->name('deploy.releases.store');
    Route::get('deploy/releases/{id}/changelog', [ReleaseManagerController::class, 'changelog'])->name('deploy.releases.changelog');
    Route::get('deploy/releases/{id}/rollback', [ReleaseManagerController::class, 'rollbackTest'])->name('deploy.releases.rollback');

    // 38.4: Comprehensive E2E Testing Suite Blueprint
    Route::get('deploy/e2e', [E2eTestController::class, 'dashboard'])->name('deploy.e2e.dashboard');
    Route::post('deploy/e2e/trigger', [E2eTestController::class, 'triggerRun'])->name('deploy.e2e.trigger');
    Route::get('deploy/e2e/results/{id}', [E2eTestController::class, 'results'])->name('deploy.e2e.results');
    Route::get('deploy/e2e/flakey', [E2eTestController::class, 'flakeyTests'])->name('deploy.e2e.flakey');

    // FASE 39: Advanced Analytics & Business Intelligence
    // 39.1: Predictive Student Performance Engine
    Route::get('analytics/predictive/dashboard', [PredictivePerformanceController::class, 'dashboard'])->name('analytics.predictive.dashboard');
    Route::get('analytics/predictive/risk/{id}', [PredictivePerformanceController::class, 'showStudentRisk'])->name('analytics.predictive.student_risk');
    Route::post('analytics/predictive/intervention', [PredictivePerformanceController::class, 'storeIntervention'])->name('analytics.predictive.store_intervention');

    // 39.2: Parent Engagement Analytics Dashboard
    Route::get('analytics/engagement/dashboard', [ParentEngagementController::class, 'dashboard'])->name('analytics.engagement.dashboard');
    Route::get('analytics/engagement/{id}', [ParentEngagementController::class, 'show'])->name('analytics.engagement.detail');

    // 39.3: Financial Forecasting & Cash Flow Projection
    Route::get('analytics/finance/forecast', [FinancialForecastingController::class, 'dashboard'])->name('analytics.finance.dashboard');
    Route::get('analytics/finance/scenario-builder', [FinancialForecastingController::class, 'createScenario'])->name('analytics.finance.create_scenario');
    Route::post('analytics/finance/scenario', [FinancialForecastingController::class, 'storeScenario'])->name('analytics.finance.store_scenario');

    // 39.4: Academic Benchmarking & National Comparison Engine
    Route::get('analytics/benchmarking/dashboard', [AcademicBenchmarkController::class, 'dashboard'])->name('analytics.benchmarking.dashboard');
    Route::get('analytics/benchmarking/import', [AcademicBenchmarkController::class, 'importForm'])->name('analytics.benchmarking.import');

    // FASE 40: Advanced Student Experience Platform
    // 40.1: Student Self-Service Portal
    Route::get('student/portal/dashboard', [StudentPortalController::class, 'dashboard'])->name('student.portal.dashboard');
    Route::get('student/portal/request', [StudentPortalController::class, 'createRequest'])->name('student.portal.create_request');
    Route::post('student/portal/request', [StudentPortalController::class, 'storeRequest'])->name('student.portal.store_request');

    // 40.2: Digital Student ID Card (QR & NFC)
    Route::get('student/idcard/my-card', [DigitalIDController::class, 'showMyCard'])->name('student.idcard.my_card');
    Route::get('student/idcard/scanner', [DigitalIDController::class, 'scanner'])->name('student.idcard.scanner');

    // 40.3: Student Achievement Certificate Generator
    Route::get('certificates/templates', [CertificateController::class, 'index'])->name('certificates.templates');
    Route::get('certificates/verify/{code}', [CertificateController::class, 'verifyPage'])->name('certificates.verify');

    // 40.4: Student Feedback & Course Evaluation System
    Route::get('evaluations', [CourseEvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('evaluations/{id}/fill', [CourseEvaluationController::class, 'fillForm'])->name('evaluations.fill');
    Route::post('evaluations/response', [CourseEvaluationController::class, 'storeResponse'])->name('evaluations.store_response');

    // FASE 41: Advanced Parent & Community Engagement
    // 41.1: Parent Mobile App API Gateway
    Route::post('api/mobile/token', [MobileAppGatewayController::class, 'registerToken'])->name('api.mobile.token');

    // 41.2: School-Community Partnership Management
    Route::get('partnerships', [PartnershipController::class, 'index'])->name('partnerships.index');
    Route::get('partnerships/create', [PartnershipController::class, 'create'])->name('partnerships.create');
    Route::post('partnerships', [PartnershipController::class, 'store'])->name('partnerships.store');

    // 41.3: Volunteer Management & Hour Tracking
    Route::get('volunteers/opportunities', [VolunteerController::class, 'opportunities'])->name('volunteers.opportunities');
    Route::get('volunteers/dashboard', [VolunteerController::class, 'dashboard'])->name('volunteers.dashboard');

    // 41.4: Alumni Mentorship Program Platform
    Route::get('mentorship/directory', [MentorshipController::class, 'directory'])->name('mentorship.directory');
    Route::get('mentorship/workspace', [MentorshipController::class, 'workspace'])->name('mentorship.workspace');

    // FASE 42: Advanced Facility & Operations Management
    // 42.1: IoT Smart Facility & Energy Monitor Hub
    Route::get('facility/iot/dashboard', [IotFacilityController::class, 'dashboard'])->name('facility.iot.dashboard');

    // 42.2: Utility Expense & Carbon Footprint Tracker
    Route::get('facility/utility/dashboard', [UtilityTrackerController::class, 'dashboard'])->name('facility.utility.dashboard');

    // 42.3: Transportation & Bus Routing System
    Route::get('facility/transport/routes', [BusRoutingController::class, 'routes'])->name('facility.transport.routes');

    // 42.4: Asset Maintenance & Preventive Service Scheduler
    Route::get('facility/maintenance/schedule', [App\Http\Controllers\Operations\AssetMaintenanceController::class, 'schedule'])->name('facility.maintenance.schedule');

    // FASE 43: Advanced Compliance & Regulatory Technology
    // 43.1: Dapodik/EMIS Auto-Export & Validation Engine
    Route::get('compliance/dapodik/index', [DapodikExportController::class, 'index'])->name('compliance.dapodik.index');

    // 43.2: Automated Data Retention & PDP Purge Engine
    Route::get('compliance/retention/index', [DataRetentionController::class, 'index'])->name('compliance.retention.index');

    // 43.3: Digital Forensics & Compliance Audit Console
    Route::get('compliance/audit/console', [AuditConsoleController::class, 'console'])->name('compliance.audit.console');

    // 43.4: Legal Contract & Vendor Compliance Manager
    Route::get('compliance/contracts/index', [LegalContractController::class, 'index'])->name('compliance.contracts.index');

    // FASE 44: Platform Maturity & Future-Proofing
    // 44.1: Developer API Gateway & Webhook Engine
    Route::get('developer/api/dashboard', [DeveloperApiController::class, 'dashboard'])->name('developer.api.dashboard');

    // 44.2: Drag-and-Drop Executive Dashboard Builder
    Route::get('developer/dashboards/index', [DashboardBuilderController::class, 'index'])->name('developer.dashboards.index');

    // 44.3: AI Recommendation & Personalization Engine
    Route::get('developer/ai/recommendations', [AiRecommendationController::class, 'recommendations'])->name('developer.ai.recommendations');

    // 44.4: Digital Twin School Infrastructure Simulation Engine
    Route::get('developer/twin/simulation', [DigitalTwinController::class, 'simulation'])->name('developer.twin.simulation');

    // Centralized Digital Asset Management (DAM)
    Route::get('media', [MediaAssetController::class, 'index'])->name('media.index');
    Route::post('media/upload', [MediaAssetController::class, 'upload'])->name('media.upload');
    Route::post('media/folders', [MediaAssetController::class, 'createFolder'])->name('media.create_folder');
    Route::post('media/{id}/move', [MediaAssetController::class, 'moveToFolder'])->name('media.move');
    Route::delete('media/{id}', [MediaAssetController::class, 'destroy'])->name('media.destroy');
    Route::get('media/storage-stats', [MediaAssetController::class, 'storageStats'])->name('media.storage_stats');

    // Intelligent Document Processing (IDP) & OCR Engine
    Route::get('ocr', [OcrDocumentController::class, 'index'])->name('ocr.index');
    Route::post('ocr/upload', [OcrDocumentController::class, 'upload'])->name('ocr.upload');
    Route::get('ocr/{id}/review', [OcrDocumentController::class, 'reviewExtraction'])->name('ocr.review');
    Route::post('ocr/{id}/apply', [OcrDocumentController::class, 'applyToRegistration'])->name('ocr.apply');

    // Multi-Format Export Engine & Data Interoperability
    Route::middleware('role:staf_tu,waka_kurikulum,waka_sarpras,kepala_sekolah,admin,superadmin')->group(function () {
        Route::get('exports', [ExportEngineController::class, 'selectFormat'])->name('exports.index');
        Route::post('exports/generate', [ExportEngineController::class, 'generateExport'])->name('exports.generate');
        Route::get('exports/{id}/download', [ExportEngineController::class, 'downloadExport'])->whereNumber('id')->name('exports.download');
        Route::get('exports/history', [ExportEngineController::class, 'exportHistory'])->name('exports.history');
    });

    // BAN-SM Accreditation Readiness Dashboard & Evidence Collector
    Route::get('accreditation', [AccreditationController::class, 'dashboard'])->name('accreditation.dashboard');
    Route::get('accreditation/standards/{id}', [AccreditationController::class, 'standardDetail'])->name('accreditation.standard_detail');
    Route::post('accreditation/evidences', [AccreditationController::class, 'uploadEvidence'])->name('accreditation.upload_evidence');
    Route::post('accreditation/evidences/{id}/verify', [AccreditationController::class, 'verifyEvidence'])->name('accreditation.verify_evidence');
    Route::get('accreditation/report', [AccreditationController::class, 'report'])->name('accreditation.report');

    // Internal Quality Assurance (SPMI) & School Self-Evaluation (EDS) Engine
    Route::get('quality/cycles', [QualityAssuranceController::class, 'cycles'])->name('quality.cycles');
    Route::post('quality/cycles', [QualityAssuranceController::class, 'createCycle'])->name('quality.create_cycle');
    Route::get('quality/cycles/{id}/evaluation', [QualityAssuranceController::class, 'fillEvaluation'])->name('quality.fill_evaluation');
    Route::post('quality/cycles/{id}/evaluation', [QualityAssuranceController::class, 'submitEvaluation'])->name('quality.submit_evaluation');
    Route::get('quality/cycles/{id}/results', [QualityAssuranceController::class, 'results'])->name('quality.results');
    Route::get('quality/cycles/{id}/compare', [QualityAssuranceController::class, 'compareReport'])->name('quality.compare');

    // Risk Assessment Matrix & Incident Response Workflow
    Route::get('risk/register', [RiskManagementController::class, 'riskRegister'])->name('risk.register');
    Route::post('risk/register', [RiskManagementController::class, 'createRisk'])->name('risk.create');
    Route::get('risk/matrix', [RiskManagementController::class, 'riskMatrix'])->name('risk.matrix');
    Route::get('risk/incidents', [RiskManagementController::class, 'incidents'])->name('risk.incidents');
    Route::post('risk/incidents', [RiskManagementController::class, 'createIncident'])->name('risk.create_incident');
    Route::post('risk/incidents/{id}/resolve', [RiskManagementController::class, 'resolveIncident'])->name('risk.resolve_incident');

    // Policy & SOP Document Governance System
    Route::get('sop', [SopGovernanceController::class, 'index'])->name('sop.index');
    Route::get('sop/create', [SopGovernanceController::class, 'create'])->name('sop.create');
    Route::post('sop', [SopGovernanceController::class, 'store'])->name('sop.store');
    Route::get('sop/{id}', [SopGovernanceController::class, 'show'])->name('sop.show');
    Route::post('sop/{id}/publish', [SopGovernanceController::class, 'publish'])->name('sop.publish');
    Route::post('sop/{id}/acknowledge', [SopGovernanceController::class, 'acknowledge'])->name('sop.acknowledge');
    Route::get('sop-compliance', [SopGovernanceController::class, 'complianceReport'])->name('sop.compliance');

    // 31.1 GraphQL API Federation & Gateway
    Route::match(['get', 'post'], 'graphql', [ApiGatewayController::class, 'graphqlEndpoint'])->name('api.graphql');
    Route::get('api-clients', [ApiGatewayController::class, 'clientManagement'])->name('api.clients');
    Route::post('api-clients', [ApiGatewayController::class, 'registerClient'])->name('api.register_client');
    Route::get('api-stats', [ApiGatewayController::class, 'apiUsageStats'])->name('api.stats');

    // 31.2 Enterprise ETL Pipeline & Migration Toolkit
    Route::get('etl', [EtlController::class, 'index'])->name('etl.index');
    Route::post('etl/jobs', [EtlController::class, 'createJob'])->name('etl.create_job');
    Route::get('etl/jobs/{id}/mapping', [EtlController::class, 'configureMapping'])->name('etl.configure_mapping');
    Route::post('etl/jobs/{id}/execute', [EtlController::class, 'execute'])->name('etl.execute');
    Route::get('etl/jobs/{id}/progress', [EtlController::class, 'progress'])->name('etl.progress');
    Route::post('etl/jobs/{id}/rollback', [EtlController::class, 'rollback'])->name('etl.rollback');

    // 31.3 Third-Party Plugin Architecture & Marketplace
    Route::get('plugins', [PluginController::class, 'index'])->name('plugins.index');
    Route::get('plugins/marketplace', [PluginController::class, 'marketplace'])->name('plugins.marketplace');
    Route::post('plugins/install', [PluginController::class, 'install'])->name('plugins.install');
    Route::post('plugins/{id}/toggle', [PluginController::class, 'toggle'])->name('plugins.toggle');

    // 31.4 Public API Documentation Portal (OpenAPI 3.0)
    Route::get('api-docs', [ApiDocumentationController::class, 'index'])->name('api_docs.index');
    Route::get('api-docs/openapi.json', [ApiDocumentationController::class, 'openApiJson'])->name('api_docs.openapi_json');
    Route::get('api-docs/swagger', [ApiDocumentationController::class, 'swaggerUi'])->name('api_docs.swagger_ui');
    Route::get('api-docs/postman', [ApiDocumentationController::class, 'postmanExport'])->name('api_docs.postman');

    // 32.1 Database Query Optimizer & N+1 Sentinel
    Route::get('query-performance', [QueryPerformanceController::class, 'dashboard'])->name('query_performance.dashboard');
    Route::get('query-performance/slow', [QueryPerformanceController::class, 'slowQueries'])->name('query_performance.slow');
    Route::get('query-performance/suggestions', [QueryPerformanceController::class, 'optimizationSuggestions'])->name('query_performance.suggestions');

    // 32.2 Lazy Loading, Code Splitting & Asset Optimization
    Route::get('asset-performance', [AssetPerformanceController::class, 'assetReport'])->name('asset_performance.report');

    // 32.3 Automated Stress Test & Load Testing Suite
    Route::get('load-test', [LoadTestController::class, 'dashboard'])->name('load_test.dashboard');
    Route::post('load-test/run', [LoadTestController::class, 'runTest'])->name('load_test.run');
    Route::get('load-test/{id}/results', [LoadTestController::class, 'results'])->name('load_test.results');
    Route::get('load-test/{id}/compare', [LoadTestController::class, 'compareBaseline'])->name('load_test.compare');

    // 32.4 Horizontal Scaling Architecture Blueprint & Read Replicas
    Route::get('scaling', [ScalingController::class, 'dashboard'])->name('scaling.dashboard');
    Route::get('scaling/configuration', [ScalingController::class, 'configuration'])->name('scaling.configuration');

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
        Route::middleware(['role:waka_kesiswaan,admin,superadmin,student'])->group(function () {
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
    Route::middleware('role:student')->group(function () {
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
    });

    // =========================================================
    // GURU â€” Fitur Akademik Pengajar
    // =========================================================
    Route::middleware('role:guru,bk,admin,superadmin')->group(function () {
        // Absensi
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
        Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');

        // Input Nilai
        Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
        Route::get('grades/create', [GradeController::class, 'create'])->name('grades.create');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');

        // E-Rapor (Wali Kelas)
        Route::get('report-cards', [ReportCardController::class, 'index'])->name('report-cards.index');
        Route::post('report-cards/sync', [ReportCardController::class, 'sync'])->name('report-cards.sync');
        Route::post('report-cards/notes', [ReportCardController::class, 'storeNotes'])->name('report-cards.store-notes');
        Route::get('report-cards/{student}/pdf', [ReportCardController::class, 'printPdf'])->name('report-cards.pdf');

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

        // Agenda & Todo List: every staff member keeps their own list. One rule
        // here (it used to disagree with a second list inside the controller).
        Route::withoutMiddleware('role:guru,bk,admin,superadmin')
            ->middleware('role:guru,bk,waka_kurikulum,waka_kesiswaan,waka_humas,waka_sarpras,staf_tu,admin')
            ->group(function () {
                Route::get('todos', [TeacherTodoController::class, 'index'])->name('todos.index');
                Route::post('todos', [TeacherTodoController::class, 'store'])->name('todos.store');
                Route::patch('todos/{todo}/toggle', [TeacherTodoController::class, 'toggle'])->name('todos.toggle');
                Route::put('todos/{todo}', [TeacherTodoController::class, 'update'])->name('todos.update');
                Route::delete('todos/{todo}', [TeacherTodoController::class, 'destroy'])->name('todos.destroy');
            });
        Route::get('todos/today', [TeacherTodoController::class, 'todayTasks'])->name('todos.today');
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
    // ADMINISTRASI â€” Manajemen Surat & Dokumen Digital
    // =========================================================
    Route::middleware('role:staf_tu,admin,superadmin,kepala_sekolah')->group(function () {
        // Halaman Kelola Template
        Route::get('letter-templates', [LetterController::class, 'manageTemplates'])->name('letters.templates.index');
        Route::post('letter-templates', [LetterController::class, 'storeTemplate'])->name('letters.templates.store');

        // Pembuatan Massal (Bulk)
        Route::get('letters/bulk', [LetterController::class, 'showBulkForm'])->name('letters.bulk');
        Route::post('letters/bulk', [LetterController::class, 'bulkStore'])->name('letters.bulk.store');

        // Operasi CRUD & Transisi Status Surat
        Route::resource('letters', LetterController::class)->except(['edit', 'update']);
        Route::post('letters/{letter}/issue', [LetterController::class, 'issue'])->name('letters.issue');
        Route::get('letters/{letter}/pdf', [LetterController::class, 'downloadPdf'])->name('letters.pdf');
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

        // Career Opportunity Management
        Route::get('career-portal/manage', [CareerOpportunityController::class, 'manage'])->name('career-portal.manage');
        Route::get('career-portal/create', [CareerOpportunityController::class, 'create'])->name('career-portal.create');
        Route::post('career-portal', [CareerOpportunityController::class, 'store'])->name('career-portal.store');
        Route::get('career-portal/{opportunity}/edit', [CareerOpportunityController::class, 'edit'])->name('career-portal.edit');
        Route::put('career-portal/{opportunity}', [CareerOpportunityController::class, 'update'])->name('career-portal.update');
        Route::delete('career-portal/{opportunity}', [CareerOpportunityController::class, 'destroy'])->name('career-portal.destroy');
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
    // Career Opportunity Detail & Download (wildcards, evaluated after specific paths)
    Route::get('career-portal/{opportunity}', [CareerOpportunityController::class, 'show'])->name('career-portal.show');
    Route::get('career-portal/{opportunity}/download', [CareerOpportunityController::class, 'downloadBrosur'])->name('career-portal.download');

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
        Route::resource('payrolls', PayrollController::class)->except(['store', 'edit']);
    });

    // =========================================================
    // SELF-SERVICE HRD (Semua Pegawai/Staf/Guru)
    // =========================================================
    Route::middleware('auth')->prefix('my-hrd')->name('my-hrd.')->group(function () {
        Route::get('portal', [MyHRDController::class, 'index'])->name('portal');
        Route::post('attendance', [MyHRDController::class, 'recordAttendance'])->name('attendance');
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

    // =========================================================
    // ADMIN & SUPERADMIN ONLY â€” Manajemen Sistem
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
        Route::get('/spmb-admin/scoring/{wave}', [SpmbController::class, 'scoring'])->name('spmb.admin.scoring');
        Route::post('/spmb-admin/scoring/{wave}', [SpmbController::class, 'storeScoring'])->name('spmb.admin.scoring.store');
        Route::get('/spmb-admin/selection/{wave}', [SpmbController::class, 'selection'])->name('spmb.admin.selection');
        Route::post('/spmb-admin/selection/{wave}/run', [SpmbController::class, 'runSelection'])->name('spmb.admin.selection.run');
        Route::get('/spmb-admin/reregistration/{wave}', [SpmbController::class, 'reregistration'])->name('spmb.admin.reregistration');
        Route::post('/spmb-admin/reregistration/{registration}/confirm', [SpmbController::class, 'confirmReregistered'])->name('spmb.admin.reregistration.confirm');
        Route::post('/spmb-admin/bulk-convert/{wave}', [SpmbController::class, 'bulkConvert'])->name('spmb.admin.bulk-convert');

        Route::get('/spmb-admin', [SpmbController::class, 'index'])->name('spmb.admin.index');
        Route::get('/spmb-admin/{registration}', [SpmbController::class, 'show'])->name('spmb.admin.show');
        Route::post('/spmb-admin/{registration}/status', [SpmbController::class, 'updateStatus'])->name('spmb.admin.status');

        // Queue Monitoring
        Route::get('admin/queue-monitor', [QueueMonitorController::class, 'index'])->name('admin.queue.index');
        Route::post('admin/queue-monitor/retry/{id}', [QueueMonitorController::class, 'retryFailedJob'])->name('admin.queue.retry');
        Route::post('admin/queue-monitor/retry-all', [QueueMonitorController::class, 'retryAllFailedJobs'])->name('admin.queue.retry-all');
        Route::post('admin/queue-monitor/flush', [QueueMonitorController::class, 'flushFailedJobs'])->name('admin.queue.flush');

        // Security Monitoring & Audit Trail (SIEM)
        Route::middleware('role:admin,superadmin')->prefix('admin/security')->name('admin.security.')->group(function () {
            Route::get('/dashboard', [SecurityController::class, 'dashboard'])->name('dashboard');
            Route::get('/audit', [SecurityController::class, 'audit'])->name('audit');
            Route::get('/sessions', [SecurityController::class, 'sessions'])->name('sessions');
            Route::delete('/sessions/{session}', [SecurityController::class, 'terminateSession'])->name('sessions.terminate');
        });
    });

    // =========================================================
    // ðŸ“Š CUSTOM REPORT BUILDER (Drag-Drop)
    // =========================================================
    Route::middleware('role:admin,superadmin,kepala_sekolah,waka_kurikulum')->prefix('admin/report-builder')->name('admin.report-builder.')->group(function () {
        Route::get('/', [ReportBuilderController::class, 'index'])->name('index');
        Route::get('/create', [ReportBuilderController::class, 'create'])->name('create');
        Route::post('/', [ReportBuilderController::class, 'store'])->name('store');
        Route::post('/preview', [ReportBuilderController::class, 'preview'])->name('preview');
        Route::get('/{report}', [ReportBuilderController::class, 'show'])->name('show');
        Route::get('/{report}/export/pdf', [ReportBuilderController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{report}/export/excel', [ReportBuilderController::class, 'exportExcel'])->name('export.excel');
        Route::delete('/{report}', [ReportBuilderController::class, 'destroy'])->name('destroy');
    });

    // =========================================================
    // FORUM DISKUSI & RUANG KELAS VIRTUAL
    // =========================================================
    Route::prefix('forum')->name('forum.')->group(function () {
        Route::get('/', [ForumController::class, 'index'])->name('index');
        Route::get('create', [ForumController::class, 'create'])->name('create');
        Route::post('threads', [ForumController::class, 'storeThread'])->name('threads.store');
        Route::get('thread/{thread}', [ForumController::class, 'show'])->name('show');
        Route::post('thread/{thread}/replies', [ForumController::class, 'storeReply'])->name('replies.store');
        Route::post('thread/{thread}/pin', [ForumController::class, 'togglePin'])->name('threads.pin');
        Route::post('thread/{thread}/lock', [ForumController::class, 'toggleLock'])->name('threads.lock');
        Route::post('reply/{reply}/best-answer', [ForumController::class, 'markBestAnswer'])->name('replies.best');
        Route::post('react', [ForumController::class, 'react'])->name('react');
        Route::delete('thread/{thread}', [ForumController::class, 'destroy'])->name('threads.destroy');
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
    Route::middleware('role:student')->group(function () {
        Route::get('elearning/assignment/{elearningAssignment}', [ElearningController::class, 'showAssignment'])->name('elearning.assignment.show');
        Route::get('elearning/assignment/{elearningAssignment}/secure', [ElearningSecureController::class, 'secureView'])->name('elearning.assignment.secure');
        Route::post('elearning/assignment/{elearningAssignment}/submit', [ElearningController::class, 'submitAssignment'])->name('elearning.assignment.submit');
        Route::post('elearning/assignment/{elearningAssignment}/log-violation', [ElearningSecureController::class, 'logViolation'])->name('elearning.assignment.violation');
    });

    // Teacher & Admin Actions
    Route::middleware('role:guru,staf_tu,admin,superadmin')->group(function () {
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
    // ðŸ“Š RAPORT MUTU SEKOLAH (EVALUASI DIRI SEKOLAH / EDS)
    // =========================================================
    Route::middleware('role:kepala_sekolah,waka_kurikulum,admin,superadmin')
        ->prefix('eds')
        ->name('eds.')
        ->group(function () {
            Route::get('/', [EdsController::class, 'index'])->name('index');
            Route::post('/', [EdsController::class, 'store'])->name('store');
            Route::get('/comparison', [EdsController::class, 'comparison'])->name('comparison');

            Route::get('/{period}', [EdsController::class, 'show'])->name('show');
            Route::post('/{period}/complete', [EdsController::class, 'complete'])->name('complete');
            Route::get('/{period}/standard/{standard}', [EdsController::class, 'assess'])->name('assess');
            Route::post('/{period}/assess', [EdsController::class, 'storeAssessment'])->name('assess.store');
            Route::get('/{period}/gap-analysis', [EdsController::class, 'gapAnalysis'])->name('gap-analysis');
            Route::get('/{period}/export-pdf', [EdsController::class, 'exportPdf'])->name('export-pdf');
        });

    // =========================================================
    // ðŸ“‘ AUDIT & COMPLIANCE DASHBOARD (MODUL 9)
    // =========================================================
    Route::middleware('role:kepala_sekolah,admin,superadmin')
        ->prefix('compliance')
        ->name('compliance.')
        ->group(function () {
            Route::get('/', [ComplianceController::class, 'index'])->name('index');
            Route::get('/checklist', [ComplianceController::class, 'checklist'])->name('checklist');
            Route::get('/documents', [ComplianceController::class, 'documentRepository'])->name('documents');
            Route::post('/documents', [ComplianceController::class, 'uploadDocument'])->name('documents.upload');
            Route::delete('/documents/{document}', [ComplianceController::class, 'deleteDocument'])->name('documents.destroy');
            Route::get('/timeline', [ComplianceController::class, 'timeline'])->name('timeline');
            Route::post('/milestones', [ComplianceController::class, 'storeMilestone'])->name('milestones.store');
            Route::post('/milestones/{milestone}/toggle', [ComplianceController::class, 'toggleMilestone'])->name('milestones.toggle');
            Route::get('/export', [ComplianceController::class, 'exportReport'])->name('export');
        });

    // =========================================================
    // ðŸ“ UJIAN ONLINE / CBT (COMPUTER-BASED TEST)
    // =========================================================
    Route::prefix('cbt')->name('cbt.')->group(function () {
        // Bank Soal â€” Guru & Admin
        Route::resource('question-banks', QuestionBankController::class);

        // Exam CRUD â€” Guru & Admin
        Route::resource('exams', ExamController::class);

        // Exam Taking â€” Siswa
        Route::get('exams/{exam}/take', [ExamController::class, 'take'])->name('exams.take');
        Route::post('exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
        Route::post('exams/{exam}/save-answers', [ExamController::class, 'saveAnswers'])->name('exams.save-answers');
        Route::post('exams/{exam}/log-violation', [ExamController::class, 'logViolation'])->name('exams.log-violation');
        Route::post('exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');

        // Proctor Monitor & Results â€” Guru & Admin
        Route::get('exams/{exam}/monitor', [ExamController::class, 'monitor'])->name('exams.monitor');
        Route::get('exams/{exam}/results', [ExamController::class, 'results'])->name('exams.results');
        Route::get('exams/{exam}/analysis', [ExamController::class, 'itemAnalysis'])->name('exams.analysis');
        Route::get('exams/{exam}/analysis/pdf', [ExamController::class, 'exportAnalysisPdf'])->name('exams.analysis.pdf');
    });

    // =========================================================
    // ðŸ§  SMART RECOMMENDATION ENGINE (SMART ALERTS & DETEKSI DINI)
    // =========================================================
    Route::middleware('role:kepala_sekolah,admin,superadmin,bk,waka_kesiswaan,waka_kurikulum,guru')
        ->prefix('smart-alerts')
        ->name('smart-alerts.')
        ->group(function () {
            Route::get('/', [SmartAlertController::class, 'index'])->name('index');
            Route::get('/dashboard', [SmartAlertController::class, 'dashboard'])->name('dashboard');
            Route::get('/rules', [SmartAlertController::class, 'rulesConfig'])->name('rules');
            Route::post('/rules', [SmartAlertController::class, 'updateRules'])->name('rules.update');
            Route::get('/create', [SmartAlertController::class, 'create'])->name('create');
            Route::post('/manual-create', [SmartAlertController::class, 'manualCreate'])->name('manual-create');
            Route::post('/{alert}/acknowledge', [SmartAlertController::class, 'acknowledge'])->name('acknowledge');
            Route::post('/{alert}/resolve', [SmartAlertController::class, 'resolve'])->name('resolve');
            Route::post('/{alert}/escalate', [SmartAlertController::class, 'escalate'])->name('escalate');
            Route::get('/history', [SmartAlertController::class, 'history'])->name('history');
            Route::get('/export', [SmartAlertController::class, 'exportReport'])->name('export');
            Route::get('/student/{student}', [SmartAlertController::class, 'studentAlerts'])->name('student');
        });

    // =========================================================
    // ðŸ“‹ DIGITAL APPROVAL WORKFLOW ENGINE
    // =========================================================
    Route::middleware('auth')
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
    // ðŸŽ“ E-PORTFOLIO SISWA (MODUL 8)
    // =========================================================
    Route::prefix('portfolios')->name('portfolios.')->group(function () {
        // Rute Siswa (Akses Pengelolaan Portofolio Mandiri)
        Route::middleware('role:student')->group(function () {
            Route::get('/mine', [PortfolioController::class, 'mine'])->name('mine');
            Route::post('/', [PortfolioController::class, 'store'])->name('store');
            Route::put('/{portfolioItem}', [PortfolioController::class, 'update'])->name('update');
            Route::delete('/{portfolioItem}', [PortfolioController::class, 'destroy'])->name('destroy');
            Route::post('/{portfolioItem}/toggle-visibility', [PortfolioController::class, 'toggleVisibility'])->name('toggle-visibility');
        });

        // Rute Staf Guru & BK (Akses Ulasan & Pemberian Lencana)
        Route::middleware('role:guru,bk,admin,superadmin,kepala_sekolah')->group(function () {
            Route::get('/', [PortfolioController::class, 'index'])->name('index');
            Route::get('/student/{student}/review', [PortfolioController::class, 'review'])->name('review');
            Route::post('/item/{portfolioItem}/verify', [PortfolioController::class, 'verify'])->name('verify');
            Route::post('/student/{student}/badge', [PortfolioController::class, 'awardBadge'])->name('award-badge');
        });
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
    // ðŸ† GAMIFIKASI & ENGAGEMENT SYSTEM SISWA (MODUL 5)
    // =========================================================
    Route::prefix('gamification')->name('gamification.')->group(function () {
        Route::get('leaderboard', [GamificationController::class, 'leaderboard'])->name('leaderboard');
        Route::get('my-progress', [GamificationController::class, 'myProgress'])->name('my-progress');
        Route::get('achievements', [GamificationController::class, 'achievements'])->name('achievements');
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
        Route::post('/dapodik/export', [IntegrationController::class, 'exportDapodik'])->name('dapodik.export');
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
    // ðŸ§ª UAT FEEDBACK SYSTEM (MODUL 12.2)
    // =========================================================
    Route::post('admin/uat/feedback', [UatFeedbackController::class, 'store'])->name('uat.feedback.store');
    Route::middleware('role:admin,superadmin')->prefix('admin/uat')->name('admin.uat.')->group(function () {
        Route::get('feedback-dashboard', [UatFeedbackController::class, 'dashboard'])->name('feedback.dashboard');
        Route::post('feedback/{id}/status', [UatFeedbackController::class, 'updateStatus'])->name('feedback.update-status');
        Route::get('feedback-report', [UatFeedbackController::class, 'report'])->name('feedback.report');
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

// --- AI Chatbot Sekolah (Modul 1) ---
Route::middleware(['auth'])->prefix('chatbot')->name('chatbot.')->group(function () {
    Route::post('send', [ChatbotController::class, 'send'])->name('send');
    Route::post('feedback', [ChatbotController::class, 'feedback'])->name('feedback');
});
Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/chatbot')->name('admin.chatbot.')->group(function () {
    Route::get('/', [ChatbotController::class, 'adminDashboard'])->name('dashboard');
    Route::get('conversations', [ChatbotController::class, 'conversationHistory'])->name('conversations');
    Route::get('conversations/{id}', [ChatbotController::class, 'showConversation'])->name('conversations.show');
    Route::post('conversations/{id}/escalate', [ChatbotController::class, 'escalateToTicket'])->name('conversations.escalate');
    Route::get('analytics', [ChatbotController::class, 'analytics'])->name('analytics');
    Route::get('train-suggestions', [ChatbotController::class, 'trainSuggestions'])->name('train-suggestions');
    Route::post('faqs/import', [ChatbotController::class, 'importFaqs'])->name('faqs.import');
    Route::get('faqs/export', [ChatbotController::class, 'exportFaqs'])->name('faqs.export');

    Route::get('faqs', [ChatbotController::class, 'manageFaqs'])->name('faqs');
    Route::post('faqs', [ChatbotController::class, 'storeFaq'])->name('faqs.store');
    Route::put('faqs/{faq}', [ChatbotController::class, 'updateFaq'])->name('faqs.update');
    Route::delete('faqs/{faq}', [ChatbotController::class, 'destroyFaq'])->name('faqs.destroy');
});
// --- Advanced Logging & Observability (Modul 9) ---
Route::middleware(['auth', 'role:superadmin'])->prefix('admin/observability')->name('admin.observability.')->group(function () {
    Route::get('/', [ObservabilityController::class, 'dashboard'])->name('dashboard');
    Route::get('/metrics', [ObservabilityController::class, 'metrics'])->name('metrics');
    Route::post('/clear-logs', [ObservabilityController::class, 'clearLogs'])->name('clear-logs');
    Route::post('/simulate', [ObservabilityController::class, 'simulate'])->name('simulate');
});

// =========================================================
// ðŸ› ï¸ DEVELOPER PORTAL (DOCUMENTATION)
// =========================================================
Route::middleware(['auth', 'role:admin,superadmin'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/', [DeveloperPortalController::class, 'index'])->name('portal');
    Route::get('/{category}', [DeveloperPortalController::class, 'index'])->name('page');
    Route::get('/{category}/{page}', [DeveloperPortalController::class, 'index'])->name('subpage');
});

// =========================================================
// FINANCE â€” Tabungan Wadiah & Cashless Kantin (Admin/TU/Merchant)
// =========================================================
Route::middleware(['auth'])->group(function () {
    Route::middleware('role:staf_tu,admin,superadmin')->prefix('admin/finance/wadiah')->name('admin.finance.wadiah.')->group(function () {
        Route::get('dashboard', [WadiahController::class, 'dashboard'])->name('dashboard');
        Route::get('accounts', [WadiahController::class, 'index'])->name('index');
        Route::get('accounts/{account}', [WadiahController::class, 'showAccount'])->name('show');
        Route::get('accounts/{account}/deposit', [WadiahController::class, 'depositForm'])->name('deposit');
        Route::post('deposit', [WadiahController::class, 'storeDeposit'])->name('deposit.store');
        Route::get('accounts/{account}/withdraw', [WadiahController::class, 'withdrawForm'])->name('withdraw');
        Route::post('withdraw', [WadiahController::class, 'storeWithdraw'])->name('withdraw.store');
        Route::post('accounts/{account}/limit', [WadiahController::class, 'setLimit'])->name('limit.store');
        Route::get('accounts/{account}/export-passbook', [WadiahController::class, 'exportPassbook'])->name('export-passbook');
    });

    Route::middleware('role:staf_tu,admin,superadmin')->prefix('admin/finance/canteen')->name('admin.finance.canteen.')->group(function () {
        Route::get('merchants', [CanteenController::class, 'merchants'])->name('merchants');
        Route::post('merchants', [CanteenController::class, 'storeMerchant'])->name('merchants.store');
        Route::post('merchants/{id}/toggle', [CanteenController::class, 'toggleMerchant'])->name('merchants.toggle');
    });

    Route::middleware('role:merchant,admin,superadmin')->prefix('merchant/canteen')->name('merchant.canteen.')->group(function () {
        Route::get('dashboard', [CanteenController::class, 'merchantDashboard'])->name('dashboard');
        Route::get('pos', [CanteenController::class, 'posTerminal'])->name('pos');
        Route::post('payment', [CanteenController::class, 'processPayment'])->name('payment');
        Route::get('settlement', [CanteenController::class, 'settlementReport'])->name('settlement');
    });

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

    Route::middleware('role:student,parent,admin,superadmin')->prefix('portal/charity')->name('portal.charity.')->group(function () {
        Route::get('/', [CharityController::class, 'portal'])->name('portal');
        Route::get('donate/{campaignId}', [CharityController::class, 'donateForm'])->name('donate');
        Route::post('donate', [CharityController::class, 'processDonate'])->name('donate.store');
        Route::get('history', [CharityController::class, 'history'])->name('history');
        Route::get('donations/{id}/certificate', [CharityController::class, 'downloadCertificate'])->name('certificate');
    });

    Route::middleware('role:staf_tu,admin,superadmin')->prefix('finance/audit')->name('finance.audit.')->group(function () {
        Route::get('/', [FinancialAuditController::class, 'index'])->name('index');
        Route::post('run', [FinancialAuditController::class, 'runAudit'])->name('run');
        Route::get('journal/{id}', [FinancialAuditController::class, 'showUnbalancedJournalDetails'])->name('show');
        Route::get('export', [FinancialAuditController::class, 'exportAuditReport'])->name('export');
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

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/observability/queues')->name('admin.observability.queues.')->group(function () {
        Route::get('/', [QueueMonitorController::class, 'index'])->name('index');
        Route::post('retry/{id}', [QueueMonitorController::class, 'retryFailedJob'])->name('retry');
        Route::delete('delete/{id}', [QueueMonitorController::class, 'deleteFailedJob'])->name('delete');
        Route::post('flush', [QueueMonitorController::class, 'flushFailedJobs'])->name('flush');
        Route::post('dispatch', [QueueMonitorController::class, 'dispatchDemoJob'])->name('dispatch');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/observability/cache')->name('admin.observability.cache.')->group(function () {
        Route::get('/', [CacheMonitorController::class, 'index'])->name('index');
        Route::post('flush-tag', [CacheMonitorController::class, 'flushTag'])->name('flush-tag');
        Route::post('flush-all', [CacheMonitorController::class, 'flushAll'])->name('flush-all');
        Route::post('warmup', [CacheMonitorController::class, 'warmupCache'])->name('warmup');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/observability/gateway')->name('admin.observability.gateway.')->group(function () {
        Route::get('/', [ApiGatewayMonitorController::class, 'index'])->name('index');
        Route::post('reset', [ApiGatewayMonitorController::class, 'resetCircuit'])->name('reset');
        Route::post('simulate-failure', [ApiGatewayMonitorController::class, 'simulateFailure'])->name('simulate-failure');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/observability/apm')->name('admin.observability.apm.')->group(function () {
        Route::get('/', [ApmObservabilityController::class, 'dashboard'])->name('index');
        Route::get('trace', [ApmObservabilityController::class, 'trace'])->name('trace');
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

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/disaster-recovery')->name('admin.disaster-recovery.')->group(function () {
        Route::get('/', [DisasterRecoveryController::class, 'index'])->name('index');
        Route::post('create', [DisasterRecoveryController::class, 'createSnapshot'])->name('create');
        Route::post('verify', [DisasterRecoveryController::class, 'verifySnapshot'])->name('verify');
        Route::post('restore', [DisasterRecoveryController::class, 'restoreSnapshot'])->name('restore');
        Route::delete('delete', [DisasterRecoveryController::class, 'deleteSnapshot'])->name('delete');
        Route::post('audit', [DisasterRecoveryController::class, 'runAudit'])->name('audit');
    });

    Route::middleware(['auth'])->prefix('admin/security/2fa')->name('admin.security.2fa.')->group(function () {
        Route::get('/', [SecurityTwoFactorController::class, 'index'])->name('index');
        Route::post('confirm', [SecurityTwoFactorController::class, 'confirm'])->name('confirm');
        Route::post('disable', [SecurityTwoFactorController::class, 'disable'])->name('disable');
        Route::get('elevate', [SecurityTwoFactorController::class, 'showElevateForm'])->name('elevate_form');
        Route::post('elevate', [SecurityTwoFactorController::class, 'elevate'])->name('elevate');
    });

    Route::middleware(['auth', 'role:admin,superadmin,kepala_sekolah'])->prefix('admin/analytics/edw')->name('admin.analytics.edw.')->group(function () {
        Route::get('/', [EdwOlapController::class, 'index'])->name('index');
        Route::post('refresh', [EdwOlapController::class, 'refreshMarts'])->name('refresh');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/vendor-sla')->name('admin.vendor-sla.')->group(function () {
        Route::get('/', [VendorSlaController::class, 'index'])->name('index');
        Route::post('probe', [VendorSlaController::class, 'probeNow'])->name('probe');
    });

    Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/webhooks')->name('admin.webhooks.')->group(function () {
        Route::get('/', [WebhookHubController::class, 'index'])->name('index');
        Route::post('store', [WebhookHubController::class, 'store'])->name('store');
        Route::post('toggle/{subscription}', [WebhookHubController::class, 'toggle'])->name('toggle');
        Route::delete('delete/{subscription}', [WebhookHubController::class, 'destroy'])->name('delete');
        Route::post('replay/{deliveryId}', [WebhookHubController::class, 'replay'])->name('replay');
        Route::post('test-dispatch', [WebhookHubController::class, 'testDispatch'])->name('test-dispatch');
    });

    Route::middleware(['auth', 'role:admin,superadmin,kepala_sekolah'])->prefix('admin/bpa/rules')->name('admin.bpa.rules.')->group(function () {
        Route::get('/', [BpaRuleEngineController::class, 'index'])->name('index');
        Route::post('store', [BpaRuleEngineController::class, 'store'])->name('store');
        Route::post('toggle/{rule}', [BpaRuleEngineController::class, 'toggle'])->name('toggle');
        Route::delete('delete/{rule}', [BpaRuleEngineController::class, 'destroy'])->name('delete');
        Route::post('test-execute', [BpaRuleEngineController::class, 'testExecute'])->name('test-execute');
    });

    Route::middleware('role:staf_tu,admin,superadmin')->prefix('admin/integration/sync')->name('admin.integration.sync.')->group(function () {
        Route::get('dashboard', [SyncController::class, 'dashboard'])->name('dashboard');
        Route::get('mapping/{target}/{table}', [SyncController::class, 'mappingForm'])->name('mapping');
        Route::post('mapping', [SyncController::class, 'saveMapping'])->name('mapping.store');
        Route::get('validation/{target}/{table}', [SyncController::class, 'runValidation'])->name('validation');
        Route::get('export/{target}/{table}', [SyncController::class, 'exportData'])->name('export');
        Route::get('history', [SyncController::class, 'syncHistory'])->name('history');
    });

    Route::middleware('role:guru,admin,superadmin')->prefix('academic/copilot')->name('academic.copilot.')->group(function () {
        Route::get('/', [AiCopilotController::class, 'index'])->name('index');
        Route::get('rpp', [AiCopilotController::class, 'rppForm'])->name('rpp');
        Route::post('rpp', [AiCopilotController::class, 'generateRpp'])->name('rpp.generate');
        Route::post('rpp/save', [AiCopilotController::class, 'saveRppToSista'])->name('rpp.save');
        Route::get('cbt', [AiCopilotController::class, 'cbtForm'])->name('cbt');
        Route::post('cbt', [AiCopilotController::class, 'generateCbt'])->name('cbt.generate');
        Route::post('cbt/save', [AiCopilotController::class, 'saveCbtToSista'])->name('cbt.save');
    });

    Route::get('assets/maintenance/scan/{uuid}', [AssetMaintenanceTicketController::class, 'scanQr'])->name('admin.assets.maintenance.scan');
    Route::post('assets/maintenance/ticket', [AssetMaintenanceTicketController::class, 'storeTicket'])->name('admin.assets.maintenance.store');

    Route::middleware('role:staf_tu,admin,superadmin')->prefix('admin/assets/maintenance')->name('admin.assets.maintenance.')->group(function () {
        Route::get('/', [AssetMaintenanceTicketController::class, 'index'])->name('index');
        Route::get('tickets/{id}', [AssetMaintenanceTicketController::class, 'showTicket'])->name('show');
        Route::post('tickets/{id}/status', [AssetMaintenanceTicketController::class, 'updateTicketStatus'])->name('update-status');
        Route::get('print-qr', [AssetMaintenanceTicketController::class, 'generateQrLabels'])->name('print-qr');
    });
});

require __DIR__.'/strategic.php';
require __DIR__.'/knowledge.php';
require __DIR__.'/treasury.php';
require __DIR__.'/adaptive.php';
require __DIR__.'/foundation.php';
require __DIR__.'/recruitment.php';
require __DIR__.'/omnichannel.php';
require __DIR__.'/data_engineering.php';
require __DIR__.'/microlearning.php';
require __DIR__.'/career_readiness.php';
require __DIR__.'/school_safety.php';

require __DIR__.'/islamic_tech.php';
require __DIR__.'/alumni_relations.php';
require __DIR__.'/auth.php';

require __DIR__.'/crisis.php';

require __DIR__.'/campus_ops.php';

require __DIR__.'/family_engagement.php';
require __DIR__.'/inclusive_education.php';
require __DIR__.'/green_school.php';
require __DIR__.'/extended_diamond.php';

// --- Mobile Universal Links & Android App Links (.well-known) ---
Route::get('/.well-known/assetlinks.json', [WellKnownController::class, 'assetLinks'])->name('well-known.assetlinks');
Route::get('/.well-known/apple-app-site-association', [WellKnownController::class, 'appleAppSiteAssociation'])->name('well-known.apple-app-site-association');

// --- FASE 107: PWA (Progressive Web App) Routes ---
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('pwa.service-worker');
Route::post('/pwa/subscribe', [PwaController::class, 'subscribe'])->middleware('auth')->name('pwa.subscribe');
