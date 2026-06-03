<?php

use App\Http\Controllers\AcademicEventController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\ReportBuilderController;
use App\Http\Controllers\Admin\ScheduledReportController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnalyticsExportController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetLoanController;
use App\Http\Controllers\AssetMaintenanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\ForceChangePasswordController;
use App\Http\Controllers\Auth\GoogleSsoController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\CareerOpportunityController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CounselingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElearningController;
use App\Http\Controllers\ElearningSecureController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\HRD\EmployeeController;
use App\Http\Controllers\HRD\MyHRDController;
use App\Http\Controllers\HRD\PayrollController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LibraryCirculationController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MutabaahController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PointCategoryController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpmbController;
use App\Http\Controllers\StudentAcademicController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentFinanceController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TahfidzController;
use App\Http\Controllers\TeachingJournalController;
use App\Http\Controllers\TracerStudyController;
use App\Http\Controllers\UksController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\ForumController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\OsisController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MpkController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\SmartAlertController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\GamificationController;




Route::get('/', [PublicSiteController::class, 'home'])->name('home');
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
    // UNIVERSAL — Semua user yang login bisa akses
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

    // Career & Scholarship Portal (read-only untuk semua)
    Route::get('career-portal', [CareerOpportunityController::class, 'index'])->name('career-portal.index');

    // Keislaman — baca data tahfidz & mutaba'ah (semua user)
    Route::get('tahfidz/{student}', [TahfidzController::class, 'show'])->name('tahfidz.show');
    Route::get('mutabaah', [MutabaahController::class, 'index'])->name('mutabaah.index');
    Route::post('mutabaah/toggle', [MutabaahController::class, 'toggle'])->name('mutabaah.toggle');

    // Impersonate Leave (Accessible by any auth user who is currently impersonating)
    Route::get('admin/impersonate-leave', [ImpersonateController::class, 'leave'])->name('admin.impersonate.leave');

    // In-App Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::get('notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // =========================================================
    // EKSTRAKURIKULER & OSIS — Pengembangan Non-Akademik
    // =========================================================
    Route::get('extracurriculars', [ExtracurricularController::class, 'index'])->name('ekskul.index');
    Route::get('extracurriculars/{extracurricular}', [ExtracurricularController::class, 'show'])->name('ekskul.show');
    Route::post('extracurriculars/{extracurricular}/register', [ExtracurricularController::class, 'register'])->name('ekskul.register');

    Route::middleware(['role:guru,bk,waka_kesiswaan,admin,superadmin'])->prefix('admin/extracurriculars')->name('ekskul.')->group(function () {
        Route::get('manage/list', [ExtracurricularController::class, 'manage'])->name('manage');
        Route::post('attendance', [ExtracurricularController::class, 'storeAttendance'])->name('attendance.store');
        Route::post('activities', [ExtracurricularController::class, 'storeActivity'])->name('activities.store');
    });

    Route::middleware(['role:waka_kesiswaan,admin,superadmin'])->prefix('admin/osis')->name('osis.')->group(function () {
        Route::get('/', [OsisController::class, 'dashboard'])->name('dashboard');
        Route::post('transactions', [OsisController::class, 'storeTransaction'])->name('transactions.store');
        Route::get('export', [OsisController::class, 'exportReport'])->name('export');
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
    Route::resource('teaching-journals', TeachingJournalController::class);

    // =========================================================
    // STUDENT SELF-SERVICE
    // =========================================================
    Route::middleware('role:student')->group(function () {
        Route::get('my-billings', [StudentFinanceController::class, 'index'])->name('finance.student.index');
        Route::get('my-academic', [StudentAcademicController::class, 'index'])->name('student.academic');
        Route::get('my-billings/{billing}/checkout', [StudentFinanceController::class, 'checkout'])->name('finance.student.checkout');
        Route::get('gateway/{billing}/mock', [StudentFinanceController::class, 'mockGateway'])->name('finance.mock-gateway');
        Route::post('gateway/{billing}/callback', [StudentFinanceController::class, 'callback'])->name('finance.mock-callback');

        // Tracer Study (Siswa dengan status alumni)
        Route::get('alumni/tracer-study', [TracerStudyController::class, 'showForm'])->name('alumni.tracer-study.form');
        Route::post('alumni/tracer-study', [TracerStudyController::class, 'submitForm'])->name('alumni.tracer-study.submit');
    });

    // =========================================================
    // PARENT PORTAL — Area Premium Wali Murid
    // =========================================================
    Route::middleware(['role:parent'])->prefix('parent-portal')->name('parent-portal.')->group(function () {
        Route::get('/', [ParentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('child/{student}/report-card', [ParentPortalController::class, 'viewReportCard'])->name('report-card');
        Route::get('child/{student}/schedule', [ParentPortalController::class, 'viewSchedule'])->name('schedule');
        Route::get('child/{student}/attendance', [ParentPortalController::class, 'viewAttendanceHistory'])->name('attendance');
        Route::get('child/{student}/billings', [ParentPortalController::class, 'viewBillings'])->name('billings');

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
    // GURU — Fitur Akademik Pengajar
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

        // Tahfidz (store — guru input setoran)
        Route::post('tahfidz', [TahfidzController::class, 'store'])->name('tahfidz.store');
    });

    // =========================================================
    // KESISWAAN — BK & Poin Kedisiplinan
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
    // ADMINISTRASI — Keuangan (TU & Admin)
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
    // EXECUTIVE — Laporan Keuangan (Kepsek & Admin)
    // =========================================================
    Route::middleware('role:kepala_sekolah,admin,superadmin')->group(function () {
        Route::get('finance/reports/executive', [FinancialReportController::class, 'index'])->name('finance.reports.executive');
        Route::get('finance/reports/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('finance.reports.income');
        Route::get('finance/reports/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('finance.reports.balance');
        Route::get('finance/reports/general-ledger', [FinancialReportController::class, 'generalLedger'])->name('finance.reports.ledger');

        // Advanced Executive Analytics
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analytics/export/attendance', [AnalyticsExportController::class, 'exportAttendancePdf'])->name('analytics.export.attendance');
        Route::get('analytics/export/grades', [AnalyticsExportController::class, 'exportGradesPdf'])->name('analytics.export.grades');
        Route::get('analytics/export/finance', [AnalyticsExportController::class, 'exportFinancePdf'])->name('analytics.export.finance');
        Route::get('analytics/export/all', [AnalyticsExportController::class, 'exportAllExcel'])->name('analytics.export.all');
    });

    // =========================================================
    // ADMINISTRASI — Manajemen Surat & Dokumen Digital
    // =========================================================
    Route::middleware('role:staf_tu,admin,superadmin,kepala_sekolah')->group(function () {
        // Halaman Kelola Template
        Route::get('letter-templates', [LetterController::class, 'manageTemplates'])->name('letters.templates.index');
        Route::post('letter-templates', [LetterController::class, 'storeTemplate'])->name('letters.templates.store');

        // Pembuatan Massal (Bulk)
        Route::get('letters/bulk', [LetterController::class, 'showBulkForm'])->name('letters.bulk');
        Route::post('letters/bulk', [LetterController::class, 'bulkStore'])->name('letters.bulk.store');

        // Operasi CRUD & Transisi Status Surat
        Route::resource('letters', LetterController::class);
        Route::post('letters/{letter}/issue', [LetterController::class, 'issue'])->name('letters.issue');
        Route::get('letters/{letter}/pdf', [LetterController::class, 'downloadPdf'])->name('letters.pdf');
    });

    // =========================================================
    // KESISWAAN — Unit Kesehatan Sekolah (UKS) Digital
    // =========================================================
    Route::prefix('uks')->name('uks.')->group(function () {
        // Akses Terbuka (Policy Guard di Controller)
        Route::get('student/{student}/history', [UksController::class, 'studentHistory'])->name('student-history');

        // Khusus Guru, BK, Staf TU, Waka, Admin
        Route::middleware('role:guru,bk,staf_tu,waka_kesiswaan,admin,superadmin')->group(function () {
            Route::get('/', [UksController::class, 'dashboard'])->name('dashboard');

            // Kunjungan CRUD
            Route::get('visits', [UksController::class, 'visitsIndex'])->name('visits.index');
            Route::get('visits/create', [UksController::class, 'visitsCreate'])->name('visits.create');
            Route::post('visits', [UksController::class, 'visitsStore'])->name('visits.store');
            Route::get('visits/{visit}', [UksController::class, 'visitsShow'])->name('visits.show');
            Route::delete('visits/{visit}', [UksController::class, 'visitsDestroy'])->name('visits.destroy');

            // Inventaris Obat
            Route::get('medicines', [UksController::class, 'medicinesIndex'])->name('medicines.index');
            Route::post('medicines', [UksController::class, 'medicinesStore'])->name('medicines.store');
            Route::put('medicines/{medicine}', [UksController::class, 'medicinesUpdate'])->name('medicines.update');
            Route::delete('medicines/{medicine}', [UksController::class, 'medicinesDestroy'])->name('medicines.destroy');

            // Ekspor Rekap Bulanan PDF
            Route::get('export/monthly', [UksController::class, 'exportMonthlyReport'])->name('export.monthly');
        });
    });

    // =========================================================
    // KESISWAAN — Bimbingan & Konseling (BK Online)
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
    // KURIKULUM — Master Data Akademik
    // =========================================================
    Route::middleware('role:waka_kurikulum,staf_tu,admin,superadmin,kepala_sekolah')->group(function () {
        // Kalender Akademik CRUD
        Route::resource('calendar/events', AcademicEventController::class)->except(['index']);

        // Tahun Ajaran
        Route::post('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
        Route::resource('academic-years', AcademicYearController::class);

        // Mapel, Kelas, Siswa
        Route::resource('subjects', SubjectController::class);
        Route::resource('classrooms', ClassroomController::class);
        Route::get('students/{student}/pdf', [StudentController::class, 'exportPdf'])->name('students.pdf');
        Route::resource('students', StudentController::class);
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');

        // Penjadwalan
        Route::get('schedules/presets', [ScheduleController::class, 'index'])->name('schedules.presets.index');
        Route::post('schedules/presets', [ScheduleController::class, 'storePreset'])->name('schedules.presets.store');
        Route::post('schedules/presets/{preset}/activate', [ScheduleController::class, 'activatePreset'])->name('schedules.presets.activate');
        Route::post('schedules/presets/{preset}/times', [ScheduleController::class, 'storeTime'])->name('schedules.presets.times.store');
        Route::get('schedules/grid', [ScheduleController::class, 'showGrid'])->name('schedules.grid');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        // Mutasi & Alumni
        Route::resource('mutations', MutationController::class)->only(['index', 'create', 'store']);
        Route::resource('alumni', AlumniController::class)->only(['index', 'create', 'store']);
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
    // SARPRAS — Sarana Prasarana
    // =========================================================
    Route::middleware('role:waka_sarpras,admin,superadmin')->group(function () {
        Route::resource('assets', AssetController::class);
        Route::resource('asset-categories', AssetCategoryController::class)->only(['index', 'store', 'destroy']);
        Route::resource('rooms', RoomController::class)->only(['index', 'store', 'destroy']);
    });

    // =========================================================
    // HUMAS — Pengumuman, Tracer Study Analytics & Career Management
    // =========================================================
    Route::middleware('role:waka_humas,admin,superadmin,kepala_sekolah')->group(function () {
        Route::resource('announcements', AnnouncementController::class)->except(['show']);

        // Tracer Study Analytics
        Route::get('alumni/tracer-study/analytics', [TracerStudyController::class, 'analytics'])->name('alumni.tracer-study.analytics');

        // Career Opportunity Management
        Route::get('career-portal/manage', [CareerOpportunityController::class, 'manage'])->name('career-portal.manage');
        Route::get('career-portal/create', [CareerOpportunityController::class, 'create'])->name('career-portal.create');
        Route::post('career-portal', [CareerOpportunityController::class, 'store'])->name('career-portal.store');
        Route::get('career-portal/{opportunity}/edit', [CareerOpportunityController::class, 'edit'])->name('career-portal.edit');
        Route::put('career-portal/{opportunity}', [CareerOpportunityController::class, 'update'])->name('career-portal.update');
        Route::delete('career-portal/{opportunity}', [CareerOpportunityController::class, 'destroy'])->name('career-portal.destroy');
    });

    // =========================================================
    // HUMAS — Content Management System (CMS)
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
    });

    // Career Opportunity Detail & Download (wildcards, evaluated after specific paths)
    Route::get('career-portal/{opportunity}', [CareerOpportunityController::class, 'show'])->name('career-portal.show');
    Route::get('career-portal/{opportunity}/download', [CareerOpportunityController::class, 'downloadBrosur'])->name('career-portal.download');

    // =========================================================
    // HRD & KEPEGAWAIAN — Manajemen Staf dan Guru
    // =========================================================
    Route::middleware('role:admin,superadmin,kepala_sekolah')->prefix('hrd')->name('hrd.')->group(function () {
        Route::resource('employees', EmployeeController::class);

        // Admin Payroll Management
        Route::post('payrolls/generate', [PayrollController::class, 'store'])->name('payrolls.generate');
        Route::post('payrolls/approve-bulk', [PayrollController::class, 'approveBulk'])->name('payrolls.approve-bulk');
        Route::post('payrolls/pay-bulk', [PayrollController::class, 'payBulk'])->name('payrolls.pay-bulk');
        Route::resource('payrolls', PayrollController::class)->except(['store']);
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
    // LIBRARY MANAGEMENT — Kelola Perpustakaan
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
    });

    // =========================================================
    // ADMIN & SUPERADMIN ONLY — Manajemen Sistem
    // =========================================================
    Route::middleware('role:admin,superadmin,kepala_sekolah')->group(function () {
        // User Management
        Route::resource('users', UserController::class);
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

        // SPMB (Manajemen Pendaftaran)
        Route::get('/spmb-admin', [SpmbController::class, 'index'])->name('spmb.admin.index');
        Route::get('/spmb-admin/{registration}', [SpmbController::class, 'show'])->name('spmb.admin.show');
        Route::post('/spmb-admin/{registration}/status', [SpmbController::class, 'updateStatus'])->name('spmb.admin.status');

        // Queue Monitoring
        Route::get('admin/queue-monitor', [QueueMonitorController::class, 'index'])->name('admin.queue.index');
        Route::post('admin/queue-monitor/retry/{id}', [QueueMonitorController::class, 'retry'])->name('admin.queue.retry');
        Route::post('admin/queue-monitor/retry-all', [QueueMonitorController::class, 'retryAll'])->name('admin.queue.retry-all');
        Route::post('admin/queue-monitor/flush', [QueueMonitorController::class, 'flush'])->name('admin.queue.flush');

        // Security Monitoring & Audit Trail (SIEM)
        Route::middleware('role:admin,superadmin')->prefix('admin/security')->name('admin.security.')->group(function () {
            Route::get('/dashboard', [SecurityController::class, 'dashboard'])->name('dashboard');
            Route::get('/audit', [SecurityController::class, 'audit'])->name('audit');
            Route::get('/sessions', [SecurityController::class, 'sessions'])->name('sessions');
            Route::delete('/sessions/{session}', [SecurityController::class, 'terminateSession'])->name('sessions.terminate');
        });
    });

    // =========================================================
    // 📊 CUSTOM REPORT BUILDER (Drag-Drop)
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
    // 📚 MANAJEMEN KURIKULUM & SILABUS DIGITAL
    // =========================================================
    Route::prefix('curriculum')->name('curriculum.')->group(function () {
        // Dashboard & PDF Report
        Route::get('/', [CurriculumController::class, 'index'])->name('index');
        Route::get('/report', [CurriculumController::class, 'completionReport'])->name('report');

        // Bank Silabus
        Route::get('/syllabi', [CurriculumController::class, 'syllabiIndex'])->name('syllabi.index');
        Route::post('/syllabi', [CurriculumController::class, 'syllabusStore'])->name('syllabi.store');
        Route::post('/syllabi/{syllabus}/review', [CurriculumController::class, 'syllabusReview'])->name('syllabi.review');
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
    // 🎫 SISTEM HELPDESK INTERNAL
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
    // 📊 RAPORT MUTU SEKOLAH (EVALUASI DIRI SEKOLAH / EDS)
    // =========================================================
    Route::middleware('role:kepala_sekolah,waka_kurikulum,admin,superadmin')
        ->prefix('eds')
        ->name('eds.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\EdsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\EdsController::class, 'store'])->name('store');
            Route::get('/comparison', [\App\Http\Controllers\EdsController::class, 'comparison'])->name('comparison');
            
            Route::get('/{period}', [\App\Http\Controllers\EdsController::class, 'show'])->name('show');
            Route::post('/{period}/complete', [\App\Http\Controllers\EdsController::class, 'complete'])->name('complete');
            Route::get('/{period}/standard/{standard}', [\App\Http\Controllers\EdsController::class, 'assess'])->name('assess');
            Route::post('/{period}/assess', [\App\Http\Controllers\EdsController::class, 'storeAssessment'])->name('assess.store');
            Route::get('/{period}/gap-analysis', [\App\Http\Controllers\EdsController::class, 'gapAnalysis'])->name('gap-analysis');
            Route::get('/{period}/export-pdf', [\App\Http\Controllers\EdsController::class, 'exportPdf'])->name('export-pdf');
        });

    // =========================================================
    // 📑 AUDIT & COMPLIANCE DASHBOARD (MODUL 9)
    // =========================================================
    Route::middleware('role:kepala_sekolah,admin,superadmin')
        ->prefix('compliance')
        ->name('compliance.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\ComplianceController::class, 'index'])->name('index');
            Route::get('/checklist', [\App\Http\Controllers\ComplianceController::class, 'checklist'])->name('checklist');
            Route::get('/documents', [\App\Http\Controllers\ComplianceController::class, 'documentRepository'])->name('documents');
            Route::post('/documents', [\App\Http\Controllers\ComplianceController::class, 'uploadDocument'])->name('documents.upload');
            Route::delete('/documents/{document}', [\App\Http\Controllers\ComplianceController::class, 'deleteDocument'])->name('documents.destroy');
            Route::get('/timeline', [\App\Http\Controllers\ComplianceController::class, 'timeline'])->name('timeline');
            Route::post('/milestones', [\App\Http\Controllers\ComplianceController::class, 'storeMilestone'])->name('milestones.store');
            Route::post('/milestones/{milestone}/toggle', [\App\Http\Controllers\ComplianceController::class, 'toggleMilestone'])->name('milestones.toggle');
            Route::get('/export', [\App\Http\Controllers\ComplianceController::class, 'exportReport'])->name('export');
        });

    // =========================================================
    // 📝 UJIAN ONLINE / CBT (COMPUTER-BASED TEST)
    // =========================================================
    Route::prefix('cbt')->name('cbt.')->group(function () {
        // Bank Soal — Guru & Admin
        Route::resource('question-banks', QuestionBankController::class);

        // Exam CRUD — Guru & Admin
        Route::resource('exams', ExamController::class);

        // Exam Taking — Siswa
        Route::get('exams/{exam}/take', [ExamController::class, 'take'])->name('exams.take');
        Route::post('exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
        Route::post('exams/{exam}/save-answers', [ExamController::class, 'saveAnswers'])->name('exams.save-answers');
        Route::post('exams/{exam}/log-violation', [ExamController::class, 'logViolation'])->name('exams.log-violation');
        Route::post('exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');

        // Proctor Monitor & Results — Guru & Admin
        Route::get('exams/{exam}/monitor', [ExamController::class, 'monitor'])->name('exams.monitor');
        Route::get('exams/{exam}/results', [ExamController::class, 'results'])->name('exams.results');
    });

    // =========================================================
    // 🧠 SMART RECOMMENDATION ENGINE (SMART ALERTS & DETEKSI DINI)
    // =========================================================
    Route::middleware('role:kepala_sekolah,admin,superadmin,bk,waka_kesiswaan,waka_kurikulum,guru')
        ->prefix('smart-alerts')
        ->name('smart-alerts.')
        ->group(function () {
            Route::get('/', [SmartAlertController::class, 'index'])->name('index');
            Route::post('/{alert}/acknowledge', [SmartAlertController::class, 'acknowledge'])->name('acknowledge');
            Route::post('/{alert}/resolve', [SmartAlertController::class, 'resolve'])->name('resolve');
            Route::get('/student/{student}', [SmartAlertController::class, 'studentAlerts'])->name('student');
        });

    // =========================================================
    // 📋 DIGITAL APPROVAL WORKFLOW ENGINE
    // =========================================================
    Route::middleware('auth')
        ->prefix('workflows')
        ->name('workflows.')
        ->group(function () {
            Route::get('/pending', [WorkflowController::class, 'pending'])->name('pending');
            Route::get('/my-requests', [WorkflowController::class, 'myRequests'])->name('my-requests');
            Route::get('/{workflowRequest}', [WorkflowController::class, 'show'])->name('show');
            Route::post('/{workflowRequest}/approve', [WorkflowController::class, 'approve'])->name('approve');
            Route::post('/{workflowRequest}/reject', [WorkflowController::class, 'reject'])->name('reject');
        });

    // =========================================================
    // 🎓 E-PORTFOLIO SISWA (MODUL 8)
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
    // 📥 DATA IMPORT/EXPORT HUB UNIVERSAL (MODUL 7)
    // =========================================================
    Route::middleware('role:admin,superadmin,staf_tu')->prefix('import-export')->name('import-export.')->group(function () {
        Route::get('/', [ImportExportController::class, 'index'])->name('index');
        Route::post('upload', [ImportExportController::class, 'upload'])->name('upload');
        Route::post('map', [ImportExportController::class, 'map'])->name('map');
        Route::post('execute', [ImportExportController::class, 'execute'])->name('execute');
        Route::post('export', [ImportExportController::class, 'exportData'])->name('export');
        Route::get('history', [ImportExportController::class, 'history'])->name('history');
        Route::post('rollback/{log}', [ImportExportController::class, 'rollback'])->name('rollback');
    });

    // =========================================================
    // 🏆 GAMIFIKASI & ENGAGEMENT SYSTEM SISWA (MODUL 5)
    // =========================================================
    Route::prefix('gamification')->name('gamification.')->group(function () {
        Route::get('leaderboard', [GamificationController::class, 'leaderboard'])->name('leaderboard');
        Route::get('my-progress', [GamificationController::class, 'myProgress'])->name('my-progress');
        Route::get('achievements', [GamificationController::class, 'achievements'])->name('achievements');
    });

    // =========================================================
    // 📧 SCHEDULED REPORTING & EMAIL DIGEST (MODUL 8)
    // =========================================================
    Route::middleware('role:admin,superadmin,staf_tu')->prefix('admin/scheduled-reports')->name('admin.scheduled-reports.')->group(function () {
        Route::get('/', [ScheduledReportController::class, 'index'])->name('index');
        Route::post('/', [ScheduledReportController::class, 'store'])->name('store');
        Route::put('/{report}', [ScheduledReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [ScheduledReportController::class, 'destroy'])->name('destroy');
        Route::post('/{report}/trigger', [ScheduledReportController::class, 'triggerImmediate'])->name('trigger');
    });

    // =========================================================
    // 🔗 INTEGRASI DAPODIK & SISTEM PEMERINTAH (MODUL 2)
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
    // 🎨 KUSTOMISASI TAMPILAN SEKOLAH (MODUL 6)
    // =========================================================
    Route::middleware('role:admin,superadmin')->prefix('admin/appearance')->name('admin.appearance.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AppearanceController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\AppearanceController::class, 'update'])->name('update');
        Route::post('/reset', [\App\Http\Controllers\Admin\AppearanceController::class, 'reset'])->name('reset');
    });

    // =========================================================
    // 🧪 UAT FEEDBACK SYSTEM (MODUL 12.2)
    // =========================================================
    Route::post('admin/uat/feedback', [\App\Http\Controllers\Admin\UatFeedbackController::class, 'store'])->name('uat.feedback.store');
    Route::middleware('role:admin,superadmin')->prefix('admin/uat')->name('admin.uat.')->group(function () {
        Route::get('feedback-dashboard', [\App\Http\Controllers\Admin\UatFeedbackController::class, 'dashboard'])->name('feedback.dashboard');
        Route::post('feedback/{id}/status', [\App\Http\Controllers\Admin\UatFeedbackController::class, 'updateStatus'])->name('feedback.update-status');
        Route::get('feedback-report', [\App\Http\Controllers\Admin\UatFeedbackController::class, 'report'])->name('feedback.report');
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

// --- Google SSO (Single Sign-On) ---
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [GoogleSsoController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleSsoController::class, 'handleGoogleCallback']);
});

// --- User Preferences (i18n & Theme Switches) ---
Route::post('preferences/locale/{locale}', [UserPreferenceController::class, 'setLocale'])->name('preferences.locale');
Route::post('preferences/theme/{theme}', [UserPreferenceController::class, 'setTheme'])->name('preferences.theme');

// --- AI Chatbot Sekolah (Modul 1) ---
Route::middleware(['auth'])->prefix('chatbot')->name('chatbot.')->group(function () {
    Route::post('send', [ChatbotController::class, 'send'])->name('send');
    Route::post('feedback', [ChatbotController::class, 'feedback'])->name('feedback');
});

Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin/chatbot')->name('admin.chatbot.')->group(function () {
    Route::get('faqs', [ChatbotController::class, 'manageFaqs'])->name('faqs');
    Route::post('faqs', [ChatbotController::class, 'storeFaq'])->name('faqs.store');
    Route::put('faqs/{faq}', [ChatbotController::class, 'updateFaq'])->name('faqs.update');
    Route::delete('faqs/{faq}', [ChatbotController::class, 'destroyFaq'])->name('faqs.destroy');
});

// --- Advanced Logging & Observability (Modul 9) ---
Route::middleware(['auth', 'role:superadmin'])->prefix('admin/observability')->name('admin.observability.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ObservabilityController::class, 'dashboard'])->name('dashboard');
    Route::get('/metrics', [\App\Http\Controllers\Admin\ObservabilityController::class, 'metrics'])->name('metrics');
    Route::post('/clear-logs', [\App\Http\Controllers\Admin\ObservabilityController::class, 'clearLogs'])->name('clear-logs');
    Route::post('/simulate', [\App\Http\Controllers\Admin\ObservabilityController::class, 'simulate'])->name('simulate');
});

// =========================================================
// 🛠️ DEVELOPER PORTAL (DOCUMENTATION)
// =========================================================
Route::middleware(['auth', 'role:admin,superadmin'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DeveloperPortalController::class, 'index'])->name('portal');
    Route::get('/{category}', [\App\Http\Controllers\Admin\DeveloperPortalController::class, 'index'])->name('page');
    Route::get('/{category}/{page}', [\App\Http\Controllers\Admin\DeveloperPortalController::class, 'index'])->name('subpage');
});

require __DIR__.'/auth.php';

