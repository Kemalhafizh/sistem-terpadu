<?php

use App\Http\Controllers\Analytics\MobileAnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\MobileDataController;
use App\Http\Controllers\Api\v1\AchievementMobileApiController;
use App\Http\Controllers\Api\v1\AdminClassSessionController;
use App\Http\Controllers\Api\v1\AdminMobileDashboardController;
use App\Http\Controllers\Api\v1\ApiAuthController;
use App\Http\Controllers\Api\v1\ApiGeneralController;
use App\Http\Controllers\Api\v1\ApiParentController;
use App\Http\Controllers\Api\v1\ApiStudentController;
use App\Http\Controllers\Api\v1\ApiTeacherController;
use App\Http\Controllers\Api\v1\AssetDamageReportApiController;
use App\Http\Controllers\Api\v1\BullyingReportApiController;
use App\Http\Controllers\Api\v1\CapabilitiesController;
use App\Http\Controllers\Api\v1\CbtAccessControlApiController;
use App\Http\Controllers\Api\v1\CbtEnterpriseApiController;
use App\Http\Controllers\Api\v1\ClassSessionController;
use App\Http\Controllers\Api\v1\ConfigController;
use App\Http\Controllers\Api\v1\ContextualHomeController;
use App\Http\Controllers\Api\v1\CounselingMobileApiController;
use App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController;
use App\Http\Controllers\Api\v1\DisciplineMobileApiController;
use App\Http\Controllers\Api\v1\DocumentMobileApiController;
use App\Http\Controllers\Api\v1\ElearningMobileApiController;
use App\Http\Controllers\Api\v1\ElectiveSurveyApiController;
use App\Http\Controllers\Api\v1\ERaporApiController;
use App\Http\Controllers\Api\v1\EvaluationMobileApiController;
use App\Http\Controllers\Api\v1\ExternalIntegrationApiController;
use App\Http\Controllers\Api\v1\ExtracurricularMobileApiController;
use App\Http\Controllers\Api\v1\LibraryMobileApiController;
use App\Http\Controllers\Api\v1\Mobile\MobileAuthController;
use App\Http\Controllers\Api\v1\Mobile\MobileConfigController;
use App\Http\Controllers\Api\v1\Mobile\MobileSyncController;
use App\Http\Controllers\Api\v1\Mobile\ScannerMobileApiController;
use App\Http\Controllers\Api\v1\P5ReportApiController;
use App\Http\Controllers\Api\v1\ParentPortalController;
use App\Http\Controllers\Api\v1\PrayerTimeApiController;
use App\Http\Controllers\Api\v1\ProfileChangeRequestApiController;
use App\Http\Controllers\Api\v1\QuestionBankApiController;
use App\Http\Controllers\Api\v1\SchoolOperationsApiController;
use App\Http\Controllers\Api\v1\SmartNotificationController;
use App\Http\Controllers\Api\v1\StudentApiController;
use App\Http\Controllers\Api\v1\StudentLeaveApiController;
use App\Http\Controllers\Api\v1\StudentProfileApiController;
use App\Http\Controllers\Api\v1\StudentSessionAttendanceController;
use App\Http\Controllers\Api\v1\SyncController;
use App\Http\Controllers\Api\v1\TahsinSubmissionApiController;
use App\Http\Controllers\Api\v1\VizController;
use App\Http\Controllers\Auth\WhatsappPasswordResetController;
use App\Http\Middleware\ApiVersion;
use App\Http\Middleware\RoleBasedRateLimiterMiddleware;
use App\Models\BullyingReport;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health Check - public, no auth required
Route::get('/health', HealthCheckController::class)->name('health');

// Midtrans Webhook Callback (Public Sandbox / Simulator)
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handleWebhook'])->name('api.midtrans.webhook');

// MOBILE REST API v1 (COMPREHENSIVE IMPLEMENTATION)
Route::prefix('v1')->middleware([ApiVersion::class, 'throttle:global'])->group(function () {

    // === PUBLIC AUTH & APP CONFIG ===
    Route::post('login', [ApiAuthController::class, 'login'])->middleware('throttle:login');
    Route::post('auth/whatsapp-otp/request', [WhatsappPasswordResetController::class, 'requestOtp'])->middleware('throttle:login');
    Route::post('auth/whatsapp-otp/reset', [WhatsappPasswordResetController::class, 'resetWithOtp'])->middleware('throttle:login');
    Route::get('mobile/config', [MobileConfigController::class, 'getConfig']);
    Route::post('mobile/auth/login', [MobileAuthController::class, 'login'])->middleware('throttle:login');
    Route::post('mobile/auth/biometric/challenge', [MobileAuthController::class, 'biometricChallenge'])->middleware('throttle:login');
    Route::post('mobile/auth/biometric/verify', [MobileAuthController::class, 'biometricVerify'])->middleware('throttle:login');
    Route::get('rapor/verify/{hash}', [ERaporApiController::class, 'verify']);
    Route::get('rapor/files/{pdf}', [ERaporApiController::class, 'downloadPdf'])
        ->middleware('signed')
        ->name('api.v1.rapor.files.show');

    // === SPMB / PPDB MOBILE (PUBLIC) ===
    Route::prefix('spmb')->group(function () {
        Route::get('waves', [SchoolOperationsApiController::class, 'spmbWaves']);
        Route::post('register', [SchoolOperationsApiController::class, 'spmbRegister']);
        Route::get('status/{registrationNo}', [SchoolOperationsApiController::class, 'spmbCheckStatus']);
        Route::post('registrations/{registrationNo}/documents', [SchoolOperationsApiController::class, 'spmbUploadDocument']);
        Route::get('documents/{document}/download', [SchoolOperationsApiController::class, 'spmbDownloadDocument'])
            ->middleware('signed')
            ->name('api.v1.spmb.documents.download');
    });

    // === PROTECTED ENDPOINTS ===
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & Token Rotation
        Route::post('logout', [ApiAuthController::class, 'logout']);
        Route::post('refresh-token', [ApiAuthController::class, 'refreshToken']);
        Route::get('me', [ApiAuthController::class, 'me']);
        Route::put('me/password', [ApiAuthController::class, 'updatePassword']);
        Route::patch('me/contact', [ProfileChangeRequestApiController::class, 'updateContact']);
        Route::put('me/locale', [ApiAuthController::class, 'updateLocale']);
        Route::get('me/capabilities', CapabilitiesController::class);

        // K4: Self-Service Identity Change Requests
        Route::get('profile-change-requests', [ProfileChangeRequestApiController::class, 'index']);
        Route::post('profile-change-requests', [ProfileChangeRequestApiController::class, 'store']);
        Route::post('profile-change-requests/{changeRequest}/cancel', [ProfileChangeRequestApiController::class, 'cancel']);
        Route::get('profile-change-requests/{changeRequest}/evidence', [ProfileChangeRequestApiController::class, 'downloadEvidence'])
            ->name('api.v1.profile-change-requests.evidence');

        // C3: Protected medical letter attachment download (IDOR-checked in service)
        Route::get('student-leave-requests/{leaveRequest}/attachment', [StudentLeaveApiController::class, 'downloadAttachment'])
            ->name('api.v1.student-leave-requests.attachment');

        // D4: Rapor Projek P5 (Kurikulum Merdeka) & I3: Scan QR Aset / Lapor Kerusakan Sarpras
        Route::middleware(Roles::only(Roles::MEMBERS))->group(function () {
            Route::get('p5/report', [P5ReportApiController::class, 'show']);
            Route::get('assets/scan/{asset}', [AssetDamageReportApiController::class, 'scan']);
            Route::post('assets/scan/{asset}/report', [AssetDamageReportApiController::class, 'report']);
            Route::get('assets/damage-reports/my', [AssetDamageReportApiController::class, 'myReports']);
            Route::get('assets/damage-reports/{ticket}/attachment', [AssetDamageReportApiController::class, 'downloadAttachment'])
                ->name('api.v1.assets.damage-reports.attachment');
        });

        // Mobile Multi-Device & Biometrics
        Route::post('mobile/auth/biometric/register', [MobileAuthController::class, 'registerBiometric']);
        Route::post('mobile/devices', [MobileAuthController::class, 'registerDevice']);
        Route::get('mobile/devices', [MobileAuthController::class, 'getActiveDevices']);
        Route::delete('mobile/devices/{deviceId}', [MobileAuthController::class, 'revokeDevice']);

        // Mobile multi-mode scanner (event ticket / visitor pass) - real EventTicketingService/VisitorService, gate & front-desk staff only.
        Route::post('mobile/scanner/event-ticket', [ScannerMobileApiController::class, 'scanEventTicket'])->middleware('role:admin,superadmin,staf_tu,guru,teacher,kepala_sekolah');
        Route::post('mobile/scanner/visitor-pass', [ScannerMobileApiController::class, 'scanVisitorPass'])->middleware('role:admin,superadmin,staf_tu,guru,teacher,kepala_sekolah');

        // Mobile Delta-Sync Engine
        Route::get('mobile/sync/pull', [MobileSyncController::class, 'pullDelta']);

        // General Profile (Backward Compatibility)
        Route::get('profile', [AuthController::class, 'profile'])->middleware('throttle:30,1');

        // Protected CBT handwritten essay submission download (student owner or exam proctor)
        Route::get('cbt/essay-submissions/{submission}', [CbtEnterpriseApiController::class, 'showEssay'])
            ->middleware('role:student,siswa,guru,teacher,waka_kurikulum,admin,superadmin,kepala_sekolah')
            ->name('api.v1.cbt.essays.show');

        // === NEW SISWA SCOPED ===
        Route::prefix('student')->middleware('role:student,siswa')->group(function () {
            Route::get('profile', [ApiStudentController::class, 'profile']);
            Route::get('schedule', [ApiStudentController::class, 'schedule']);
            Route::get('grades', [ApiStudentController::class, 'grades']);
            Route::get('attendance', [ApiStudentController::class, 'attendance']);
            Route::get('billings', [ApiStudentController::class, 'billings']);
            Route::post('billings/{id}/pay', [ApiStudentController::class, 'payBilling']);
            Route::get('points', [ApiStudentController::class, 'points']);
            Route::get('tahfidz', [ApiStudentController::class, 'tahfidz']);
            Route::get('mutabaah', [ApiStudentController::class, 'mutabaah']);
            Route::get('cbt/exams', [ApiStudentController::class, 'cbtExams']);
            Route::get('cbt/exams/{id}/questions', [ApiStudentController::class, 'cbtExamQuestions']);
            Route::post('cbt/exams/{id}/submit', [ApiStudentController::class, 'cbtSubmitExam']);

            // CBT Enterprise Maximization
            Route::get('cbt/exams/{id}/encrypted-payload', [CbtEnterpriseApiController::class, 'encryptedPayload']);
            Route::get('cbt/exams/{id}/decryption-key', [CbtEnterpriseApiController::class, 'decryptionKey']);
            Route::post('cbt/exams/{id}/micro-sync', [CbtEnterpriseApiController::class, 'microSync']);
            Route::post('cbt/exams/{id}/upload-essay', [CbtEnterpriseApiController::class, 'uploadEssay']);

            // CBT Token-Gated & Force-Close
            Route::post('cbt/exams/{id}/validate-token', [CbtAccessControlApiController::class, 'validateToken']);
            Route::post('cbt/exams/{id}/force-close', [CbtAccessControlApiController::class, 'forceClose']);
            Route::post('cbt/exams/{id}/heartbeat', [CbtAccessControlApiController::class, 'heartbeat']);
            Route::post('cbt/exams/{id}/log-violation', [CbtAccessControlApiController::class, 'logViolation']);

            Route::get('notifications', [ApiStudentController::class, 'notifications']);
            Route::post('notifications/{id}/read', [ApiStudentController::class, 'markNotificationRead']);

            // presensi per-sesi kelas via QR berputar dari layar guru
            Route::prefix('class-session')->group(function () {
                Route::post('scan-qr', [StudentSessionAttendanceController::class, 'scanQr'])->middleware('throttle:20,1');
                Route::get('active', [StudentSessionAttendanceController::class, 'activeSession']);
                Route::get('attendance', [StudentSessionAttendanceController::class, 'dayAttendance']);
            });

            // Survei peminatan kelas X, rombel TA depan, & riwayat rombel (A2–A3)
            Route::get('elective-survey', [ElectiveSurveyApiController::class, 'studentSurvey']);
            Route::put('elective-survey/choices', [ElectiveSurveyApiController::class, 'submitChoices']);
            Route::get('next-classroom', [ElectiveSurveyApiController::class, 'studentNextClassroom']);
            Route::get('enrollment-history', [ElectiveSurveyApiController::class, 'studentEnrollmentHistory']);
        });

        // === NEW ORANG TUA SCOPED ===
        Route::prefix('parent')->middleware('role:parent,orang_tua,orangtua,wali_murid')->group(function () {
            Route::get('children', [ApiParentController::class, 'children']);
            Route::get('child/{student}/summary', [ApiParentController::class, 'childSummary']);
            Route::get('child/{student}/grades', [ApiParentController::class, 'childGrades']);
            Route::get('child/{student}/attendance', [ApiParentController::class, 'childAttendance']);
            Route::get('child/{student}/class-session-attendance', [ApiParentController::class, 'childSessionAttendance']);
            Route::get('child/{student}/billings', [ApiParentController::class, 'childBillings']);
            Route::get('child/{student}/points', [ApiParentController::class, 'childPoints']);
            Route::get('child/{student}/elective-survey', [ElectiveSurveyApiController::class, 'parentChildSurvey']);
            Route::get('child/{student}/enrollment-history', [ElectiveSurveyApiController::class, 'parentChildEnrollmentHistory']);
            Route::get('messages', [ApiParentController::class, 'messages']);
            Route::post('messages', [ApiParentController::class, 'sendMessage']);

            // C3: Izin / Sakit Siswa dari Orang Tua
            Route::get('leave-requests', [StudentLeaveApiController::class, 'parentIndex']);
            Route::post('leave-requests', [StudentLeaveApiController::class, 'store']);
            Route::post('leave-requests/{leaveRequest}/cancel', [StudentLeaveApiController::class, 'cancel']);
        });

        // C3: Persetujuan Izin / Sakit Siswa (Wali Kelas / Guru / Waka Kesiswaan / Admin)
        Route::prefix('teacher/leave-requests')->middleware('role:guru,teacher,wali_kelas,waka_kesiswaan,admin,superadmin')->group(function () {
            Route::get('/', [StudentLeaveApiController::class, 'teacherIndex']);
            Route::post('{leaveRequest}/approve', [StudentLeaveApiController::class, 'approve']);
            Route::post('{leaveRequest}/reject', [StudentLeaveApiController::class, 'reject']);
            Route::post('{leaveRequest}/revoke', [StudentLeaveApiController::class, 'revoke']);
        });

        // === NEW GURU & BK SCOPED ===
        Route::prefix('teacher')->middleware('role:guru,teacher,bk')->group(function () {
            Route::get('schedule', [ApiTeacherController::class, 'schedule']);
            Route::get('classes', [ApiTeacherController::class, 'classes']);
            Route::get('classes/{id}/students', [ApiTeacherController::class, 'classStudents']);
            Route::post('attendance', [ApiTeacherController::class, 'storeAttendance']);
            Route::get('journals', [ApiTeacherController::class, 'journals']);
            Route::post('journals', [ApiTeacherController::class, 'storeJournal']);

            Route::get('messages', [ApiTeacherController::class, 'messages']);
            Route::post('messages', [ApiTeacherController::class, 'sendMessage']);

            // Proctor Live Interventions
            Route::post('cbt/exams/{id}/proctor/warning', [CbtEnterpriseApiController::class, 'proctorWarning']);
            Route::post('cbt/exams/{id}/proctor/force-submit', [CbtEnterpriseApiController::class, 'proctorForceSubmit']);
            Route::post('cbt/exams/{id}/proctor/extend-time', [CbtEnterpriseApiController::class, 'proctorExtendTime']);

            // CBT Token Distribution & Student Reset
            Route::get('cbt/exams', [CbtAccessControlApiController::class, 'teacherExams']);
            Route::get('cbt/exams/{id}/token', [CbtAccessControlApiController::class, 'getToken']);
            Route::post('cbt/exams/{id}/token/regenerate', [CbtAccessControlApiController::class, 'regenerateToken']);
            Route::post('cbt/exams/{id}/token/reset-student', [CbtAccessControlApiController::class, 'resetStudent']);
            Route::get('cbt/exams/{id}/locked-students', [CbtAccessControlApiController::class, 'lockedStudents']);

            // CBT Manual Authoring with LaTeX & Image Support
            Route::post('cbt/exams', [ApiTeacherController::class, 'storeExamWithQuestions']);
            Route::post('cbt/upload-image', [ApiTeacherController::class, 'uploadExamImage']);

            // sesi kelas hidup (mulai/akhiri kelas, QR berputar, absensi manual)
            Route::prefix('class-sessions')->group(function () {
                Route::get('today', [ClassSessionController::class, 'todaySessions']);
                Route::post('{scheduleId}/start', [ClassSessionController::class, 'startSession'])->whereNumber('scheduleId');
                Route::post('{sessionId}/end', [ClassSessionController::class, 'endSession'])->whereNumber('sessionId');
                Route::get('{sessionId}/qr', [ClassSessionController::class, 'getActiveQr'])->whereNumber('sessionId');
                Route::get('{sessionId}/students', [ClassSessionController::class, 'sessionStudentList'])->whereNumber('sessionId');
                Route::post('{sessionId}/attendance/manual', [ClassSessionController::class, 'manualAttendance'])->whereNumber('sessionId');
                Route::post('{sessionId}/attendance/bulk', [ClassSessionController::class, 'bulkManualAttendance'])->whereNumber('sessionId');
            });
        });

        // === KOREKSI & REKAP PRESENSI SESI KELAS (WAKA KURIKULUM / TU / ADMIN) ===
        Route::prefix('admin/class-sessions')->group(function () {
            // Monitoring & rekap: kepala sekolah boleh membaca.
            Route::middleware('role:admin,superadmin,waka_kurikulum,staf_tu,kepala_sekolah')->group(function () {
                Route::get('/', [AdminClassSessionController::class, 'index']);
                Route::get('reports', [AdminClassSessionController::class, 'attendanceReport']);
                Route::get('{sessionId}/attendances', [AdminClassSessionController::class, 'sessionAttendances'])->whereNumber('sessionId');
            });
            // Koreksi presensi tetap hanya Waka Kurikulum / TU / Admin (aturan 117.6 #8).
            Route::put('attendances/{attendanceId}/override', [AdminClassSessionController::class, 'overrideAttendance'])
                ->middleware('role:admin,superadmin,waka_kurikulum,staf_tu')
                ->whereNumber('attendanceId');
        });

        // === BANK SOAL TERPUSAT & AUTO-GENERATE UJIAN CBT ===
        // Bank soal: guru & waka kurikulum (sebelumnya terbuka untuk semua akun, termasuk siswa).
        Route::prefix('question-bank')->middleware('role:guru,teacher,waka_kurikulum')->group(function () {
            Route::get('categories', [QuestionBankApiController::class, 'getCategories']);
            Route::get('items', [QuestionBankApiController::class, 'getItems']);
            Route::post('items', [QuestionBankApiController::class, 'store']);
            Route::post('auto-generate', [QuestionBankApiController::class, 'autoGenerate']);
        });

        // === E-RAPOR KURIKULUM MERDEKA ===
        Route::prefix('rapor')->group(function () {
            Route::get('current', [ERaporApiController::class, 'getCurrentRapor'])->middleware('role:student,siswa');
            // Pemilik data dicek di controller (StudentDataAccess): ortu hanya anaknya.
            Route::get('child/{childId}', [ERaporApiController::class, 'getChildRapor'])
                ->middleware('role:parent,orang_tua,orangtua,wali_murid,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kurikulum,waka_kesiswaan,staf_tu,admin');
            Route::post('generate-pdf', [ERaporApiController::class, 'generatePdf'])
                ->middleware('role:student,siswa,parent,orang_tua,orangtua,wali_murid,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kurikulum,waka_kesiswaan,staf_tu,admin');
        });

        // === PENILAIAN HARIAN & REMEDIAL WORKFLOW ===
        Route::prefix('assessments')->group(function () {
            // Guru: hanya penilaian miliknya (dicek di controller).
            Route::middleware('role:guru,teacher')->group(function () {
                Route::get('teacher', [DailyAssessmentMobileApiController::class, 'teacherAssessments']);
                Route::post('teacher', [DailyAssessmentMobileApiController::class, 'storeAssessment']);
                Route::get('{id}/scores', [DailyAssessmentMobileApiController::class, 'assessmentScores'])->whereNumber('id');
                Route::post('{id}/batch-scores', [DailyAssessmentMobileApiController::class, 'batchScores'])->whereNumber('id');
                Route::post('{id}/auto-remedial', [DailyAssessmentMobileApiController::class, 'autoRemedial'])->whereNumber('id');
                Route::post('remedials/{sessionId}/score', [DailyAssessmentMobileApiController::class, 'submitRemedialScore'])->whereNumber('sessionId');
            });
            Route::middleware('role:student,siswa')->group(function () {
                Route::get('student', [DailyAssessmentMobileApiController::class, 'studentAssessments']);
                Route::get('student/remedials', [DailyAssessmentMobileApiController::class, 'studentRemedials']);
            });
        });

        // === E-LEARNING MOBILE LMS TERPADU ===
        Route::prefix('elearning')->group(function () {
            // Keanggotaan kelas & kepemilikan dicek di controller (StudentDataAccess).
            Route::middleware('role:student,siswa')->group(function () {
                Route::get('classes/student', [ElearningMobileApiController::class, 'studentClasses']);
                Route::post('assignments/{id}/submit', [ElearningMobileApiController::class, 'submitAssignment'])->whereNumber('id');
            });
            Route::middleware('role:student,siswa,guru,teacher')->group(function () {
                Route::get('classes/{classId}/materials', [ElearningMobileApiController::class, 'studentMaterials'])->whereNumber('classId');
                Route::get('classes/{classId}/assignments', [ElearningMobileApiController::class, 'studentAssignments'])->whereNumber('classId');
            });
            Route::middleware('role:guru,teacher')->group(function () {
                Route::get('classes/teacher', [ElearningMobileApiController::class, 'teacherClasses']);
                Route::post('classes/{classId}/materials', [ElearningMobileApiController::class, 'storeMaterial'])->whereNumber('classId');
                Route::post('classes/{classId}/assignments', [ElearningMobileApiController::class, 'storeAssignment'])->whereNumber('classId');
                Route::get('assignments/{id}/submissions', [ElearningMobileApiController::class, 'getSubmissions'])->whereNumber('id');
                Route::post('submissions/{id}/grade', [ElearningMobileApiController::class, 'gradeSubmission'])->whereNumber('id');
            });
        });

        // === BIMBINGAN KONSELING (BK) MOBILE & AT-RISK MONITORING ===
        Route::prefix('counseling')->group(function () {
            Route::middleware('role:student,siswa')->group(function () {
                Route::post('appointments/request', [CounselingMobileApiController::class, 'requestAppointment']);
                Route::get('appointments/my', [CounselingMobileApiController::class, 'studentAppointments']);
            });

            Route::middleware('role:admin,superadmin,bk,guru,teacher,kepala_sekolah')->group(function () {
                Route::get('dashboard', [CounselingMobileApiController::class, 'dashboard']);
                Route::post('sessions', [CounselingMobileApiController::class, 'storeSession']);
                Route::get('at-risk', [CounselingMobileApiController::class, 'atRiskStudents']);
                Route::post('sessions/{id}/follow-up', [CounselingMobileApiController::class, 'addFollowUp']);
            });

            // anyone signed in may report bullying; reading the reports follows the web rule.
            Route::post('bullying-reports', [BullyingReportApiController::class, 'store']);
            Route::middleware(Roles::only(BullyingReport::READER_ROLES))->group(function () {
                Route::get('bullying-reports', [BullyingReportApiController::class, 'index']);
                Route::get('bullying-reports/{id}', [BullyingReportApiController::class, 'show'])->whereNumber('id');
            });
        });

        // === PROFIL KOMPREHENSIF SISWA 360 DERAJAT ===
        Route::prefix('profile/student')->middleware('role:student,siswa,parent,orang_tua,orangtua,wali_murid,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kurikulum,waka_kesiswaan,staf_tu,admin')->group(function () {
            Route::get('me', [StudentProfileApiController::class, 'myProfile'])->middleware('role:student,siswa');
            Route::get('{id?}', [StudentProfileApiController::class, 'show']);
            Route::get('{id?}/academic', [StudentProfileApiController::class, 'academic']);
            Route::get('{id?}/ibadah', [StudentProfileApiController::class, 'ibadah']);
            Route::get('{id?}/discipline', [StudentProfileApiController::class, 'discipline']);
            Route::get('{id?}/health', [StudentProfileApiController::class, 'health']);
        });

        // === KALENDER AKADEMIK & SYNC ICAL ===
        Route::prefix('academic-calendar')->group(function () {
            Route::get('/', [SchoolOperationsApiController::class, 'calendarEvents']);
            Route::get('export-ical', [SchoolOperationsApiController::class, 'exportIcal']);
        });

        // === REKAM MEDIS UKS & SKRINING KESEHATAN ===
        Route::prefix('uks')->group(function () {
            Route::get('visits/my', [SchoolOperationsApiController::class, 'uksVisits'])->middleware('role:student,siswa,parent,orang_tua,orangtua,wali_murid,petugas_uks,staf_tu,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kesiswaan,admin');
            Route::post('visits', [SchoolOperationsApiController::class, 'recordUksVisit'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru,teacher');
            Route::get('screening/{studentId?}', [SchoolOperationsApiController::class, 'healthScreening'])->middleware('role:student,siswa,parent,orang_tua,orangtua,wali_murid,petugas_uks,staf_tu,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kesiswaan,admin');
            Route::get('students/search', [SchoolOperationsApiController::class, 'searchUksStudents'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru,teacher');
            Route::get('medicines', [SchoolOperationsApiController::class, 'uksMedicines'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru,teacher');
        });

        // PERPUSTAKAAN DIGITAL MOBILE API
        Route::prefix('library')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
            Route::get('books', [LibraryMobileApiController::class, 'index']);
            Route::get('books/{id}', [LibraryMobileApiController::class, 'show']);
            Route::post('self-checkout', [LibraryMobileApiController::class, 'selfCheckout']);
            Route::get('my-loans', [LibraryMobileApiController::class, 'myLoans']);
        });

        // EKSTRAKURIKULER & OSIS MOBILE API
        Route::prefix('extracurricular')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
            Route::get('list', [ExtracurricularMobileApiController::class, 'index']);
            Route::post('{id}/register', [ExtracurricularMobileApiController::class, 'register']);
            Route::post('attendance/submit', [ExtracurricularMobileApiController::class, 'submitAttendance']);
            Route::get('osis-feed', [ExtracurricularMobileApiController::class, 'osisFeed']);
        });

        // PRESTASI & PORTOFOLIO MOBILE API
        Route::prefix('achievements')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
            Route::get('list', [AchievementMobileApiController::class, 'index']);
            Route::post('upload', [AchievementMobileApiController::class, 'upload']);
            Route::get('certificates', [AchievementMobileApiController::class, 'certificates']);
            Route::get('academic-cv-summary', [AchievementMobileApiController::class, 'cvSummary']);
            Route::post('{id}/verify', [AchievementMobileApiController::class, 'verify']);
        });

        // PEREKAM TAHSIN/TAHFIDZ & EVALUASI GURU MOBILE API
        Route::prefix('tahsin')->middleware('role:student,siswa,guru,teacher,bk,admin,superadmin')->group(function () {
            Route::post('submissions', [TahsinSubmissionApiController::class, 'store']);
            Route::get('submissions', [TahsinSubmissionApiController::class, 'myIndex']);
            Route::get('submissions/{id}', [TahsinSubmissionApiController::class, 'show']);
        });
        Route::prefix('teacher/tahsin')->middleware('role:guru,teacher,bk,admin,superadmin')->group(function () {
            Route::get('submissions', [TahsinSubmissionApiController::class, 'teacherIndex']);
            Route::post('submissions/{id}/annotate', [TahsinSubmissionApiController::class, 'annotate']);
            Route::post('submissions/{id}/mark-reviewed', [TahsinSubmissionApiController::class, 'markReviewed']);
        });

        // EVALUASI GURU & E-VOTING OSIS MOBILE API
        Route::prefix('evaluations')->middleware(['auth:sanctum', 'role:student,siswa'])->group(function () {
            Route::get('teachers', [EvaluationMobileApiController::class, 'teacherList']);
            Route::post('teachers/submit', [EvaluationMobileApiController::class, 'submitTeacherEval']);
            Route::get('facilities', [EvaluationMobileApiController::class, 'facilityList']);
            Route::post('facilities/submit', [EvaluationMobileApiController::class, 'submitFacilityEval']);
            Route::get('osis-election', [EvaluationMobileApiController::class, 'electionInfo']);
            Route::post('osis-election/vote', [EvaluationMobileApiController::class, 'castVote']);
        });

        // KEDISIPLINAN LENGKAP MOBILE API
        Route::prefix('discipline')->middleware(['auth:sanctum', 'role:student,siswa,parent,orang_tua,orangtua,wali_murid'])->group(function () {
            Route::get('/summary', [DisciplineMobileApiController::class, 'summary']);
            Route::get('/history', [DisciplineMobileApiController::class, 'history']);
            Route::get('/warning-letters', [DisciplineMobileApiController::class, 'letters']);
            Route::post('/warning-letters/{id}/sign', [DisciplineMobileApiController::class, 'signLetter']);
        });

        // ADMIN DASHBOARD & DOCUMENT MANAGEMENT MOBILE API
        Route::prefix('mobile/admin')->middleware(['role:admin,superadmin,kepsek,kepala_sekolah'])->group(function () {
            Route::get('/dashboard', [AdminMobileDashboardController::class, 'dashboard']);
            Route::get('/kpi', [AdminMobileDashboardController::class, 'kpiDetail']);
            Route::get('/approvals', [AdminMobileDashboardController::class, 'pendingApprovals']);
            Route::post('/approvals/{id}/action', [AdminMobileDashboardController::class, 'processApproval']);
            Route::post('/emergency-broadcast', [AdminMobileDashboardController::class, 'broadcastEmergency']);
        });

        // Priority 6 Unified Cross-Module Approval Inbox for all authorized staff/teachers
        Route::get('approvals/pending', [AdminMobileDashboardController::class, 'unifiedPendingApprovals'])
            ->middleware('role:admin,superadmin,kepsek,kepala_sekolah,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,staf_tu,bendahara,pustakawan,petugas_uks,bk,guru,teacher,wali_kelas');

        Route::prefix('documents')->middleware('auth:sanctum')->group(function () {
            Route::get('/reports', [DocumentMobileApiController::class, 'reportList'])->middleware('role:student,siswa');
            Route::get('/certificates', [DocumentMobileApiController::class, 'certificateList'])->middleware('role:student,siswa');
        });

        // CONTEXTUAL & PREDICTIVE HOME API
        Route::prefix('home')->group(function () {
            Route::get('contextual', [ContextualHomeController::class, 'index']);
            Route::get('suggestions', [ContextualHomeController::class, 'suggestions']);
            Route::post('dismiss-suggestion', [ContextualHomeController::class, 'dismissSuggestion']);
            Route::post('suggestions/{id}/dismiss', [ContextualHomeController::class, 'dismissSuggestion']);
        });

        // MICRO-INTERACTION & ANIMATION CONFIG API
        Route::prefix('config/animations')->group(function () {
            Route::get('/', [ConfigController::class, 'getAnimations']);
            Route::put('preferences', [ConfigController::class, 'updatePreferences']);
        });

        // Notification Priority Inbox
        Route::prefix('notifications')->group(function () {
            Route::get('preferences', [SmartNotificationController::class, 'getPreferences']);
            Route::put('preferences', [SmartNotificationController::class, 'updatePreferences']);
            Route::get('digest', [SmartNotificationController::class, 'getDigest']);
        });
        Route::prefix('notifications/preferences')->group(function () {
            Route::get('/', [SmartNotificationController::class, 'getPreferences']);
            Route::put('/', [SmartNotificationController::class, 'updatePreferences']);
        });

        // ADVANCED DATA VISUALIZATION WEB & API
        Route::prefix('viz')->group(function () {
            Route::get('heatmap/attendance', [VizController::class, 'attendanceHeatmap']);
            Route::get('distribution/grades/{examId}', [VizController::class, 'gradeDistribution']);
            Route::get('radar/student/{studentId}', [VizController::class, 'studentRadar']);
            Route::get('trend/student/{studentId}', [VizController::class, 'studentTrend']);
            Route::get('comparison/classes', [VizController::class, 'classComparison']);
            Route::get('counters/live', [VizController::class, 'liveCounters']);
        });

        // OFFLINE-FIRST SYNC ENGINE 2.0 API
        Route::prefix('sync')->group(function () {
            Route::get('delta', [SyncController::class, 'getDelta']);
            Route::post('batch', [SyncController::class, 'processBatch']);
            Route::get('check-version', [SyncController::class, 'checkVersion']);
            Route::get('catch-up', [SyncController::class, 'catchUp']);
        });

        // PARENT EXPERIENCE OVERHAUL API
        Route::prefix('parent')->middleware('role:parent,orang_tua,orangtua,wali_murid,student,siswa')->group(function () {
            Route::get('child/{childId}/feed', [ParentPortalController::class, 'activityFeed']);
            Route::get('feed', [ParentPortalController::class, 'activityFeed']);
            Route::get('child/{childId}/digest', [ParentPortalController::class, 'weeklyDigest']);
            Route::get('weekly-digest', [ParentPortalController::class, 'weeklyDigest']);
            Route::get('child/{childId}/benchmark', [ParentPortalController::class, 'benchmarkClass']);
            Route::get('comparison', [ParentPortalController::class, 'benchmarkClass']);
            Route::get('actions', [ParentPortalController::class, 'quickActions']);
        });

        // === UNIVERSAL GENERAL ===
        Route::get('prayer-times', [PrayerTimeApiController::class, 'show']);
        Route::get('announcements', [ApiGeneralController::class, 'announcements']);
        Route::get('announcements/{id}', [ApiGeneralController::class, 'announcementDetail'])->whereNumber('id');
        Route::post('announcements/{id}/acknowledge', [ApiGeneralController::class, 'acknowledgeAnnouncement'])->whereNumber('id');
        Route::get('calendar', [ApiGeneralController::class, 'calendarEvents']);
        Route::get('notifications', [ApiGeneralController::class, 'notifications']);
        Route::get('notifications/unread-count', [ApiGeneralController::class, 'unreadNotificationCount']);
        Route::post('notifications/read-all', [ApiGeneralController::class, 'markAllNotificationsRead']);
        Route::post('notifications/{id}/read', [ApiGeneralController::class, 'markNotificationRead'])->whereUuid('id');
        Route::delete('notifications/{id}', [ApiGeneralController::class, 'deleteNotification'])->whereUuid('id');

        // BACKWARD COMPATIBLE DATA ENDPOINTS (USED BY LEGACY SUITE)
        Route::prefix('students')->group(function () {
            Route::get('/', [MobileDataController::class, 'getStudents']);
            Route::prefix('{uuid}')->group(function () {
                Route::get('/attendances', [MobileDataController::class, 'getAttendances']);
                Route::get('/grades', [MobileDataController::class, 'getGrades']);
                Route::get('/billings', [MobileDataController::class, 'getBillings']);
                Route::get('/point-logs', [MobileDataController::class, 'getPointLogs']);
                Route::get('/tahfidz', [MobileDataController::class, 'getTahfidzLogs']);
                Route::get('/mutabaah', [MobileDataController::class, 'getMutabaahLogs']);
            });
        });

        // === API GATEWAY V1 ENTERPRISE GATEWAY ENDPOINTS ===
        Route::prefix('gateway')->middleware(RoleBasedRateLimiterMiddleware::class)->group(function () {
            Route::get('student/me', [StudentApiController::class, 'profile'])->name('api.v1.student.me');
            Route::post('whatsapp/send', [ExternalIntegrationApiController::class, 'sendWhatsapp'])->name('api.v1.whatsapp.send');
        });

        // BIG DATA PREDICTIVE ANALYTICS
        Route::prefix('analytics')->group(function () {

            // Mobile app analytics screens (student/teacher/parent/executive dashboards) - real aggregates only.
            Route::get('student/summary', [MobileAnalyticsController::class, 'studentSummary'])->middleware('role:student,siswa');
            Route::get('teacher/my-classes', [MobileAnalyticsController::class, 'teacherClasses'])->middleware('role:guru,teacher,bk,admin,superadmin,kepala_sekolah');
            Route::get('teacher/class-performance', [MobileAnalyticsController::class, 'classPerformance'])->middleware('role:guru,teacher,bk,admin,superadmin,kepala_sekolah');
            Route::get('parent/child-progress', [MobileAnalyticsController::class, 'parentChildProgress'])->middleware('role:parent,orang_tua,orangtua,wali_murid');
            Route::get('executive/kpi', [MobileAnalyticsController::class, 'executiveKpi'])->middleware('role:admin,superadmin,kepala_sekolah,staf_tu');
        });

    });

});
