<?php

use App\Http\Controllers\Ai\AiLessonPlanController;
use App\Http\Controllers\Ai\AiTutorController;
use App\Http\Controllers\Ai\NlpEssayGraderController;
use App\Http\Controllers\Analytics\AtRiskStudentPredictionController;
use App\Http\Controllers\Analytics\MobileAnalyticsController;
use App\Http\Controllers\Analytics\TreasuryForecastController;
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
use App\Http\Controllers\Api\v1\CapabilitiesController;
use App\Http\Controllers\Api\v1\CbtAccessControlApiController;
use App\Http\Controllers\Api\v1\CbtEnterpriseApiController;
use App\Http\Controllers\Api\v1\ClassSessionController;
use App\Http\Controllers\Api\v1\ConfigController;
use App\Http\Controllers\Api\v1\ContextualHomeController;
use App\Http\Controllers\Api\v1\CounselingMobileApiController;
use App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController;
use App\Http\Controllers\Api\v1\DesignSystemController;
use App\Http\Controllers\Api\v1\DisciplineMobileApiController;
use App\Http\Controllers\Api\v1\DocumentMobileApiController;
use App\Http\Controllers\Api\v1\ElearningMobileApiController;
use App\Http\Controllers\Api\v1\ERaporApiController;
use App\Http\Controllers\Api\v1\EvaluationMobileApiController;
use App\Http\Controllers\Api\v1\ExternalIntegrationApiController;
use App\Http\Controllers\Api\v1\ExtracurricularMobileApiController;
use App\Http\Controllers\Api\v1\GamificationController;
use App\Http\Controllers\Api\v1\LibraryMobileApiController;
use App\Http\Controllers\Api\v1\Mobile\GeofenceController;
use App\Http\Controllers\Api\v1\Mobile\MobileAttendanceController;
use App\Http\Controllers\Api\v1\Mobile\MobileAuthController;
use App\Http\Controllers\Api\v1\Mobile\MobileConfigController;
use App\Http\Controllers\Api\v1\Mobile\MobileSyncController;
use App\Http\Controllers\Api\v1\Mobile\ScannerMobileApiController;
use App\Http\Controllers\Api\v1\MobileV2ApiController;
use App\Http\Controllers\Api\v1\ParentPortalController;
use App\Http\Controllers\Api\v1\QuestionBankApiController;
use App\Http\Controllers\Api\v1\SchoolOperationsApiController;
use App\Http\Controllers\Api\v1\SmartNotificationController;
use App\Http\Controllers\Api\v1\StudentApiController;
use App\Http\Controllers\Api\v1\StudentProfileApiController;
use App\Http\Controllers\Api\v1\StudentSessionAttendanceController;
use App\Http\Controllers\Api\v1\SyncController;
use App\Http\Controllers\Api\v1\TahsinSubmissionApiController;
use App\Http\Controllers\Api\v1\UtbkMobileApiController;
use App\Http\Controllers\Api\v1\VizController;
use App\Http\Controllers\Blockchain\BlockchainCredentialController;
use App\Http\Controllers\Blockchain\StudentDidController;
use App\Http\Controllers\Debloat\PragmaticDebloatApiController;
use App\Http\Controllers\Infrastructure\CloudClusterController;
use App\Http\Controllers\Infrastructure\EdgeCdnController;
use App\Http\Controllers\Iot\EnergyManagementController;
use App\Http\Controllers\Iot\FacialRecognitionAttendanceController;
use App\Http\Middleware\ApiVersion;
use App\Http\Middleware\RoleBasedRateLimiterMiddleware;
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

// Health Check — public, no auth required
Route::get('/health', HealthCheckController::class)->name('health');

// Midtrans Webhook Callback (Public Sandbox / Simulator)
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handleWebhook'])->name('api.midtrans.webhook');

// =========================================================================
// MOBILE REST API v1 (COMPREHENSIVE IMPLEMENTATION)
// =========================================================================
Route::prefix('v1')->middleware([ApiVersion::class, 'throttle:global'])->group(function () {

    // === PUBLIC AUTH & APP CONFIG ===
    Route::post('login', [ApiAuthController::class, 'login'])->middleware('throttle:login');
    Route::get('mobile/config', [MobileConfigController::class, 'getConfig']);
    Route::post('mobile/auth/login', [MobileAuthController::class, 'login'])->middleware('throttle:login');
    Route::post('mobile/auth/biometric/challenge', [MobileAuthController::class, 'biometricChallenge'])->middleware('throttle:login');
    Route::post('mobile/auth/biometric/verify', [MobileAuthController::class, 'biometricVerify'])->middleware('throttle:login');
    Route::get('rapor/verify/{hash}', [ERaporApiController::class, 'verify']);

    // === FASE 94: SPMB / PPDB MOBILE (PUBLIC) ===
    Route::prefix('spmb')->group(function () {
        Route::get('waves', [SchoolOperationsApiController::class, 'spmbWaves']);
        Route::post('register', [SchoolOperationsApiController::class, 'spmbRegister']);
        Route::get('status/{registrationNo}', [SchoolOperationsApiController::class, 'spmbCheckStatus']);
        Route::post('registrations/{registrationNo}/documents', [SchoolOperationsApiController::class, 'spmbUploadDocument']);
    });

    // === PROTECTED ENDPOINTS ===
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & Token Rotation
        Route::post('logout', [ApiAuthController::class, 'logout']);
        Route::post('refresh-token', [ApiAuthController::class, 'refreshToken']);
        Route::get('me', [ApiAuthController::class, 'me']);
        Route::get('me/capabilities', CapabilitiesController::class);

        // Mobile Multi-Device & Biometrics
        Route::post('mobile/auth/biometric/register', [MobileAuthController::class, 'registerBiometric']);
        Route::post('mobile/devices', [MobileAuthController::class, 'registerDevice']);
        Route::get('mobile/devices', [MobileAuthController::class, 'getActiveDevices']);
        Route::delete('mobile/devices/{deviceId}', [MobileAuthController::class, 'revokeDevice']);

        // Mobile GPS Attendance & Dynamic QR
        Route::post('mobile/attendance/gps-checkin', [MobileAttendanceController::class, 'gpsCheckin'])->middleware('role:student,siswa');
        Route::get('mobile/attendance/dynamic-qr', [MobileAttendanceController::class, 'generateDynamicQr']);
        Route::post('mobile/attendance/verify-qr', [MobileAttendanceController::class, 'verifyDynamicQr'])->middleware('role:admin,staf_tu,guru,teacher,kepala_sekolah');

        // Mobile multi-mode scanner (event ticket / visitor pass) — real EventTicketingService/VisitorService, gate & front-desk staff only.
        Route::post('mobile/scanner/event-ticket', [ScannerMobileApiController::class, 'scanEventTicket'])->middleware('role:admin,superadmin,staf_tu,guru,teacher,kepala_sekolah');
        Route::post('mobile/scanner/visitor-pass', [ScannerMobileApiController::class, 'scanVisitorPass'])->middleware('role:admin,superadmin,staf_tu,guru,teacher,kepala_sekolah');

        // Mobile Delta-Sync Engine
        Route::get('mobile/sync/pull', [MobileSyncController::class, 'pullDelta']);

        // General Profile (Backward Compatibility)
        Route::get('profile', [AuthController::class, 'profile'])->middleware('throttle:30,1');

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

            // FASE 86: CBT Enterprise Maximization
            Route::get('cbt/exams/{id}/encrypted-payload', [CbtEnterpriseApiController::class, 'encryptedPayload']);
            Route::get('cbt/exams/{id}/decryption-key', [CbtEnterpriseApiController::class, 'decryptionKey']);
            Route::post('cbt/exams/{id}/micro-sync', [CbtEnterpriseApiController::class, 'microSync']);
            Route::post('cbt/exams/{id}/upload-essay', [CbtEnterpriseApiController::class, 'uploadEssay']);

            // FASE 87: CBT Token-Gated & Force-Close
            Route::post('cbt/exams/{id}/validate-token', [CbtAccessControlApiController::class, 'validateToken']);
            Route::post('cbt/exams/{id}/force-close', [CbtAccessControlApiController::class, 'forceClose']);
            Route::post('cbt/exams/{id}/heartbeat', [CbtAccessControlApiController::class, 'heartbeat']);
            Route::post('cbt/exams/{id}/log-violation', [CbtAccessControlApiController::class, 'logViolation']);

            Route::get('notifications', [ApiStudentController::class, 'notifications']);
            Route::post('notifications/{id}/read', [ApiStudentController::class, 'markNotificationRead']);

            // FASE 117: presensi per-sesi kelas via QR berputar dari layar guru
            Route::prefix('class-session')->group(function () {
                Route::post('scan-qr', [StudentSessionAttendanceController::class, 'scanQr'])->middleware('throttle:20,1');
                Route::get('active', [StudentSessionAttendanceController::class, 'activeSession']);
            });
        });

        // === NEW ORANG TUA SCOPED ===
        Route::prefix('parent')->middleware('role:parent,orang_tua,orangtua,wali_murid')->group(function () {
            Route::get('children', [ApiParentController::class, 'children']);
            Route::get('child/{student}/summary', [ApiParentController::class, 'childSummary']);
            Route::get('child/{student}/grades', [ApiParentController::class, 'childGrades']);
            Route::get('child/{student}/attendance', [ApiParentController::class, 'childAttendance']);
            Route::get('child/{student}/billings', [ApiParentController::class, 'childBillings']);
            Route::get('child/{student}/points', [ApiParentController::class, 'childPoints']);
            Route::get('messages', [ApiParentController::class, 'messages']);
            Route::post('messages', [ApiParentController::class, 'sendMessage']);
        });

        // === NEW GURU & BK SCOPED ===
        Route::prefix('teacher')->middleware('role:guru,bk')->group(function () {
            Route::get('schedule', [ApiTeacherController::class, 'schedule']);
            Route::get('classes', [ApiTeacherController::class, 'classes']);
            Route::get('classes/{id}/students', [ApiTeacherController::class, 'classStudents']);
            Route::post('attendance', [ApiTeacherController::class, 'storeAttendance']);
            Route::get('journals', [ApiTeacherController::class, 'journals']);
            Route::post('journals', [ApiTeacherController::class, 'storeJournal']);

            Route::get('messages', [ApiTeacherController::class, 'messages']);
            Route::post('messages', [ApiTeacherController::class, 'sendMessage']);

            // FASE 86: Proctor Live Interventions
            Route::post('cbt/exams/{id}/proctor/warning', [CbtEnterpriseApiController::class, 'proctorWarning']);
            Route::post('cbt/exams/{id}/proctor/force-submit', [CbtEnterpriseApiController::class, 'proctorForceSubmit']);
            Route::post('cbt/exams/{id}/proctor/extend-time', [CbtEnterpriseApiController::class, 'proctorExtendTime']);

            // FASE 87: CBT Token Distribution & Student Reset
            Route::get('cbt/exams', [CbtAccessControlApiController::class, 'teacherExams']);
            Route::get('cbt/exams/{id}/token', [CbtAccessControlApiController::class, 'getToken']);
            Route::post('cbt/exams/{id}/token/regenerate', [CbtAccessControlApiController::class, 'regenerateToken']);
            Route::post('cbt/exams/{id}/token/reset-student', [CbtAccessControlApiController::class, 'resetStudent']);

            // CBT Manual Authoring with LaTeX & Image Support
            Route::post('cbt/exams', [ApiTeacherController::class, 'storeExamWithQuestions']);
            Route::post('cbt/upload-image', [ApiTeacherController::class, 'uploadExamImage']);

            // FASE 117: sesi kelas hidup (mulai/akhiri kelas, QR berputar, absensi manual)
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

        // === FASE 117: KOREKSI & REKAP PRESENSI SESI KELAS (WAKA KURIKULUM / TU / ADMIN) ===
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

        // === FASE 88: BANK SOAL TERPUSAT & AUTO-GENERATE UJIAN CBT ===
        // Bank soal: guru & waka kurikulum (sebelumnya terbuka untuk semua akun, termasuk siswa).
        Route::prefix('question-bank')->middleware('role:guru,teacher,waka_kurikulum')->group(function () {
            Route::get('categories', [QuestionBankApiController::class, 'getCategories']);
            Route::get('items', [QuestionBankApiController::class, 'getItems']);
            Route::post('items', [QuestionBankApiController::class, 'store']);
            Route::post('auto-generate', [QuestionBankApiController::class, 'autoGenerate']);
        });

        // === FASE 89: E-RAPOR KURIKULUM MERDEKA ===
        Route::prefix('rapor')->group(function () {
            Route::get('current', [ERaporApiController::class, 'getCurrentRapor'])->middleware('role:student,siswa');
            // Pemilik data dicek di controller (StudentDataAccess): ortu hanya anaknya.
            Route::get('child/{childId}', [ERaporApiController::class, 'getChildRapor'])
                ->middleware('role:parent,orang_tua,wali_murid,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kurikulum,waka_kesiswaan,staf_tu,admin');
            Route::post('generate-pdf', [ERaporApiController::class, 'generatePdf'])
                ->middleware('role:student,siswa,parent,orang_tua,wali_murid,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kurikulum,waka_kesiswaan,staf_tu,admin');
        });

        // === FASE 91: PENILAIAN HARIAN & REMEDIAL WORKFLOW ===
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

        // === FASE 90: E-LEARNING MOBILE LMS TERPADU ===
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

        // === FASE 92: BIMBINGAN KONSELING (BK) MOBILE & AT-RISK MONITORING ===
        Route::prefix('counseling')->group(function () {
            Route::middleware('role:student,siswa')->group(function () {
                Route::post('appointments/request', [CounselingMobileApiController::class, 'requestAppointment']);
                Route::get('appointments/my', [CounselingMobileApiController::class, 'studentAppointments']);
            });

            Route::middleware('role:admin,superadmin,bk,guru,kepala_sekolah')->group(function () {
                Route::get('dashboard', [CounselingMobileApiController::class, 'dashboard']);
                Route::post('sessions', [CounselingMobileApiController::class, 'storeSession']);
                Route::get('at-risk', [CounselingMobileApiController::class, 'atRiskStudents']);
                Route::post('sessions/{id}/follow-up', [CounselingMobileApiController::class, 'addFollowUp']);
            });
        });

        // === FASE 95: PROFIL KOMPREHENSIF SISWA 360 DERAJAT ===
        Route::prefix('profile/student')->middleware('role:student,siswa,parent,orang_tua,orangtua,wali_murid,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kurikulum,waka_kesiswaan,staf_tu,admin')->group(function () {
            Route::get('me', [StudentProfileApiController::class, 'myProfile'])->middleware('role:student,siswa');
            Route::get('{id?}', [StudentProfileApiController::class, 'show']);
            Route::get('{id?}/academic', [StudentProfileApiController::class, 'academic']);
            Route::get('{id?}/ibadah', [StudentProfileApiController::class, 'ibadah']);
            Route::get('{id?}/discipline', [StudentProfileApiController::class, 'discipline']);
            Route::get('{id?}/health', [StudentProfileApiController::class, 'health']);
        });

        // === FASE 93: KALENDER AKADEMIK & SYNC ICAL ===
        Route::prefix('academic-calendar')->group(function () {
            Route::get('/', [SchoolOperationsApiController::class, 'calendarEvents']);
            Route::get('export-ical', [SchoolOperationsApiController::class, 'exportIcal']);
        });

        // === FASE 96: REKAM MEDIS UKS & SKRINING KESEHATAN ===
        Route::prefix('uks')->group(function () {
            Route::get('visits/my', [SchoolOperationsApiController::class, 'uksVisits'])->middleware('role:student,siswa,parent,orang_tua,orangtua,wali_murid,petugas_uks,staf_tu,guru,bk,wali_kelas,kepala_sekolah,waka_kesiswaan,admin');
            Route::post('visits', [SchoolOperationsApiController::class, 'recordUksVisit'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru');
            Route::get('screening/{studentId?}', [SchoolOperationsApiController::class, 'healthScreening'])->middleware('role:student,siswa,parent,orang_tua,orangtua,wali_murid,petugas_uks,staf_tu,guru,bk,wali_kelas,kepala_sekolah,waka_kesiswaan,admin');
            Route::get('students/search', [SchoolOperationsApiController::class, 'searchUksStudents'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru');
            Route::get('medicines', [SchoolOperationsApiController::class, 'uksMedicines'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru');
        });

        // === FASE 97: JURNAL MENGAJAR GURU & AGENDA KELAS ===
        Route::prefix('teaching-journals')->middleware('role:admin,superadmin,guru')->group(function () {
            Route::get('my', [SchoolOperationsApiController::class, 'teacherJournals']);
            Route::post('/', [SchoolOperationsApiController::class, 'storeTeacherJournal']);
        });

        // =====================================================================
        // FASE 108: PERPUSTAKAAN DIGITAL MOBILE API
        // =====================================================================
        Route::prefix('library')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
            Route::get('books', [LibraryMobileApiController::class, 'index']);
            Route::get('books/{id}', [LibraryMobileApiController::class, 'show']);
            Route::post('self-checkout', [LibraryMobileApiController::class, 'selfCheckout']);
            Route::get('my-loans', [LibraryMobileApiController::class, 'myLoans']);
        });

        // =====================================================================
        // FASE 109: EKSTRAKURIKULER & OSIS MOBILE API
        // =====================================================================
        Route::prefix('extracurricular')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
            Route::get('list', [ExtracurricularMobileApiController::class, 'index']);
            Route::post('{id}/register', [ExtracurricularMobileApiController::class, 'register']);
            Route::post('attendance/submit', [ExtracurricularMobileApiController::class, 'submitAttendance']);
            Route::get('osis-feed', [ExtracurricularMobileApiController::class, 'osisFeed']);
        });

        // =====================================================================
        // FASE 110: PRESTASI & PORTOFOLIO MOBILE API
        // =====================================================================
        Route::prefix('achievements')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
            Route::get('list', [AchievementMobileApiController::class, 'index']);
            Route::post('upload', [AchievementMobileApiController::class, 'upload']);
            Route::get('certificates', [AchievementMobileApiController::class, 'certificates']);
            Route::get('academic-cv-summary', [AchievementMobileApiController::class, 'cvSummary']);
            Route::post('{id}/verify', [AchievementMobileApiController::class, 'verify']);
        });

        // =====================================================================
        // FASE 72.1: PEREKAM TAHSIN/TAHFIDZ & EVALUASI GURU MOBILE API
        // =====================================================================
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

        // FASE 111: EVALUASI GURU & E-VOTING OSIS MOBILE API
        Route::prefix('evaluations')->middleware(['auth:sanctum', 'role:student,siswa'])->group(function () {
            Route::get('teachers', [EvaluationMobileApiController::class, 'teacherList']);
            Route::post('teachers/submit', [EvaluationMobileApiController::class, 'submitTeacherEval']);
            Route::get('facilities', [EvaluationMobileApiController::class, 'facilityList']);
            Route::post('facilities/submit', [EvaluationMobileApiController::class, 'submitFacilityEval']);
            Route::get('osis-election', [EvaluationMobileApiController::class, 'electionInfo']);
            Route::post('osis-election/vote', [EvaluationMobileApiController::class, 'castVote']);
        });

        // FASE 112: UTBK TRYOUT & REKOMENDASI PTN MOBILE API
        Route::prefix('utbk')->middleware(['auth:sanctum', 'role:student,siswa'])->group(function () {
            Route::get('tryouts', [UtbkMobileApiController::class, 'index']);
            Route::get('tryouts/{id}/questions', [UtbkMobileApiController::class, 'questions']);
            Route::post('tryouts/{id}/submit', [UtbkMobileApiController::class, 'submit']);
            Route::get('recommendations', [UtbkMobileApiController::class, 'ptnRecommendations']);
            Route::get('alumni-directory', [UtbkMobileApiController::class, 'alumniDirectory']);
        });

        // FASE 113: KEDISIPLINAN LENGKAP MOBILE API
        Route::prefix('discipline')->middleware(['auth:sanctum', 'role:student,siswa,parent,orang_tua,orangtua,wali_murid'])->group(function () {
            Route::get('/summary', [DisciplineMobileApiController::class, 'summary']);
            Route::get('/history', [DisciplineMobileApiController::class, 'history']);
            Route::get('/warning-letters', [DisciplineMobileApiController::class, 'letters']);
            Route::post('/warning-letters/{id}/sign', [DisciplineMobileApiController::class, 'signLetter']);
        });

        // FASE 114: ADMIN DASHBOARD & DOCUMENT MANAGEMENT MOBILE API
        Route::prefix('mobile/admin')->middleware(['role:admin,superadmin,kepsek,kepala_sekolah'])->group(function () {
            Route::get('/dashboard', [AdminMobileDashboardController::class, 'dashboard']);
            Route::get('/kpi', [AdminMobileDashboardController::class, 'kpiDetail']);
            Route::get('/approvals', [AdminMobileDashboardController::class, 'pendingApprovals']);
            Route::post('/approvals/{id}/action', [AdminMobileDashboardController::class, 'processApproval']);
            Route::post('/emergency-broadcast', [AdminMobileDashboardController::class, 'broadcastEmergency']);
        });

        Route::prefix('documents')->middleware('auth:sanctum')->group(function () {
            Route::get('/reports', [DocumentMobileApiController::class, 'reportList'])->middleware('role:student,siswa');
            Route::get('/certificates', [DocumentMobileApiController::class, 'certificateList'])->middleware('role:student,siswa');
            Route::post('/ocr/scan', [DocumentMobileApiController::class, 'ocrScan']);
            Route::get('/ocr/{taskId}', [DocumentMobileApiController::class, 'ocrResult']);
            Route::post('/signature/submit', [DocumentMobileApiController::class, 'submitSignature']);
        });

        // =====================================================================
        // FASE 98: MODERN DESIGN SYSTEM API & THEMING ENGINE
        // =====================================================================
        Route::prefix('design')->group(function () {
            Route::get('tokens', [DesignSystemController::class, 'getTokens']);
            Route::get('theme', [DesignSystemController::class, 'getUserTheme']);
            Route::put('theme/preferences', [DesignSystemController::class, 'updatePreferences']);
            Route::get('presets', [DesignSystemController::class, 'getPresets']);
        });

        // =====================================================================
        // FASE 99: CONTEXTUAL & PREDICTIVE HOME API
        // =====================================================================
        Route::prefix('home')->group(function () {
            Route::get('contextual', [ContextualHomeController::class, 'index']);
            Route::get('suggestions', [ContextualHomeController::class, 'suggestions']);
            Route::post('dismiss-suggestion', [ContextualHomeController::class, 'dismissSuggestion']);
            Route::post('suggestions/{id}/dismiss', [ContextualHomeController::class, 'dismissSuggestion']);
        });

        // =====================================================================
        // FASE 100: MICRO-INTERACTION & ANIMATION CONFIG API
        // =====================================================================
        Route::prefix('config/animations')->group(function () {
            Route::get('/', [ConfigController::class, 'getAnimations']);
            Route::put('preferences', [ConfigController::class, 'updatePreferences']);
        });

        // =====================================================================
        // FASE 101: GAMIFICATION & ENGAGEMENT ENGINE
        // =====================================================================
        Route::prefix('gamification')->middleware('role:student,siswa')->group(function () {
            Route::get('profile', [GamificationController::class, 'getProfile']);
            Route::get('leaderboard', [GamificationController::class, 'getLeaderboard']);
            Route::get('badges', [GamificationController::class, 'getBadges']);
            Route::get('xp-history', [GamificationController::class, 'getXpHistory']);
            Route::get('history', [GamificationController::class, 'getXpHistory']);
            Route::get('streak-calendar', [GamificationController::class, 'getStreakCalendar']);
            Route::get('streak', [GamificationController::class, 'getStreakCalendar']);
        });

        // =====================================================================
        // FASE 102: SMART NOTIFICATION SYSTEM — PRIORITY INBOX
        // =====================================================================
        Route::prefix('notifications')->group(function () {
            Route::get('preferences', [SmartNotificationController::class, 'getPreferences']);
            Route::put('preferences', [SmartNotificationController::class, 'updatePreferences']);
            Route::get('digest', [SmartNotificationController::class, 'getDigest']);
        });
        Route::prefix('notifications/preferences')->group(function () {
            Route::get('/', [SmartNotificationController::class, 'getPreferences']);
            Route::put('/', [SmartNotificationController::class, 'updatePreferences']);
        });

        // =====================================================================
        // FASE 104: ADVANCED DATA VISUALIZATION WEB & API
        // =====================================================================
        Route::prefix('viz')->group(function () {
            Route::get('heatmap/attendance', [VizController::class, 'attendanceHeatmap']);
            Route::get('distribution/grades/{examId}', [VizController::class, 'gradeDistribution']);
            Route::get('radar/student/{studentId}', [VizController::class, 'studentRadar']);
            Route::get('trend/student/{studentId}', [VizController::class, 'studentTrend']);
            Route::get('comparison/classes', [VizController::class, 'classComparison']);
            Route::get('counters/live', [VizController::class, 'liveCounters']);
        });

        // =====================================================================
        // FASE 105: OFFLINE-FIRST SYNC ENGINE 2.0 API
        // =====================================================================
        Route::prefix('sync')->group(function () {
            Route::get('delta', [SyncController::class, 'getDelta']);
            Route::post('batch', [SyncController::class, 'processBatch']);
            Route::get('check-version', [SyncController::class, 'checkVersion']);
            Route::get('catch-up', [SyncController::class, 'catchUp']);
        });

        // =====================================================================
        // FASE 106: PARENT EXPERIENCE OVERHAUL API
        // =====================================================================
        Route::prefix('parent')->middleware('role:parent,orang_tua,orangtua,wali_murid,student,siswa')->group(function () {
            Route::get('child/{childId}/feed', [ParentPortalController::class, 'activityFeed']);
            Route::get('feed', [ParentPortalController::class, 'activityFeed']);
            Route::get('child/{childId}/digest', [ParentPortalController::class, 'weeklyDigest']);
            Route::get('weekly-digest', [ParentPortalController::class, 'weeklyDigest']);
            Route::get('child/{childId}/benchmark', [ParentPortalController::class, 'benchmarkClass']);
            Route::get('comparison', [ParentPortalController::class, 'benchmarkClass']);
            Route::get('actions', [ParentPortalController::class, 'quickActions']);
        });

        Route::get('ai/tutor/suggestions', [MobileV2ApiController::class, 'getAiTutorSuggestions'])->middleware('role:student,siswa');

        // === UNIVERSAL GENERAL ===
        Route::get('announcements', [ApiGeneralController::class, 'announcements']);
        Route::get('announcements/{id}', [ApiGeneralController::class, 'announcementDetail']);
        Route::get('calendar', [ApiGeneralController::class, 'calendarEvents']);
        Route::get('notifications', [ApiGeneralController::class, 'notifications']);

        // =====================================================================
        // BACKWARD COMPATIBLE DATA ENDPOINTS (USED BY LEGACY SUITE)
        // =====================================================================
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

        // =====================================================================
        // FASE 69: SUPER APP ENTERPRISE API & MOBILE GATEWAY
        // =====================================================================
        Route::prefix('mobile')->group(function () {
            Route::post('geofence/checkin', [GeofenceController::class, 'checkin']);
        });

        // =====================================================================
        // FASE 70: GENERATIVE AI & AUTONOMOUS LEARNING ENGINE
        // =====================================================================
        Route::prefix('ai')->group(function () {
            Route::middleware('role:student,siswa')->group(function () {
                Route::post('tutor/session', [AiTutorController::class, 'startSession']);
                Route::post('tutor/session/{id}/message', [AiTutorController::class, 'sendMessage']);
                Route::get('tutor/session/{id}', [AiTutorController::class, 'getHistory']);
                Route::post('essay/grade', [NlpEssayGraderController::class, 'submitEssay']);
                Route::get('essay/{id}', [NlpEssayGraderController::class, 'getSubmission']);
            });
            Route::middleware('role:guru,teacher')->group(function () {
                Route::post('lesson-plan/generate', [AiLessonPlanController::class, 'generate']);
                Route::get('lesson-plans', [AiLessonPlanController::class, 'getPlans']);
            });
        });

        // =====================================================================
        // FASE 71: IMMUTABLE BLOCKCHAIN CREDENTIALING (WEB3)
        // =====================================================================
        Route::prefix('blockchain')->group(function () {
            // Credentials are issued by the school to a student, never by the student.
            Route::post('credentials/issue', [BlockchainCredentialController::class, 'issue'])->middleware('role:admin,staf_tu,kepala_sekolah');
            Route::middleware('role:student,siswa,alumni')->group(function () {
                Route::get('credentials/my', [BlockchainCredentialController::class, 'myCredentials']);
                Route::get('did/me', [StudentDidController::class, 'getMyDid']);
                Route::get('did/passport', [StudentDidController::class, 'getPassport']);
            });
        });

        // =====================================================================
        // FASE 72: SMART CAMPUS IOT 2.0 & FACIAL RECOGNITION
        // =====================================================================
        Route::prefix('iot')->group(function () {
            // Everyone who walks through the gate: students and school staff.
            Route::post('face/enroll', [FacialRecognitionAttendanceController::class, 'enroll'])->middleware('role:student,siswa,guru,teacher,bk,wali_kelas,kepala_sekolah,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,staf_tu,petugas_uks,admin');
            // Gate cameras log attendance for whoever they recognise: device/operator accounts only.
            Route::post('face/match', [FacialRecognitionAttendanceController::class, 'matchGate'])->middleware('role:admin,staf_tu,operator');
            Route::post('energy/sensor', [EnergyManagementController::class, 'updateSensor'])->middleware('role:admin,superadmin,staf_tu,operator');
            Route::get('energy/dashboard', [EnergyManagementController::class, 'getDashboard'])->middleware('role:admin,staf_tu,operator,waka_sarpras,kepala_sekolah');
        });

        // =====================================================================
        // FASE 73: BIG DATA PREDICTIVE ANALYTICS
        // =====================================================================
        Route::prefix('analytics')->group(function () {
            Route::get('student/{id}/risk', [AtRiskStudentPredictionController::class, 'analyzeStudent'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah,bk,guru');
            Route::get('at-risk/dashboard', [AtRiskStudentPredictionController::class, 'getAtRiskDashboard'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah,bk');
            Route::post('treasury/forecast', [TreasuryForecastController::class, 'generate'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah');
            Route::get('treasury/forecasts', [TreasuryForecastController::class, 'getForecasts'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah');

            // Mobile app analytics screens (student/teacher/parent/executive dashboards) — real aggregates only.
            Route::get('student/summary', [MobileAnalyticsController::class, 'studentSummary'])->middleware('role:student,siswa');
            Route::get('teacher/my-classes', [MobileAnalyticsController::class, 'teacherClasses'])->middleware('role:guru,teacher,bk,admin,superadmin,kepala_sekolah');
            Route::get('teacher/class-performance', [MobileAnalyticsController::class, 'classPerformance'])->middleware('role:guru,teacher,bk,admin,superadmin,kepala_sekolah');
            Route::get('parent/child-progress', [MobileAnalyticsController::class, 'parentChildProgress'])->middleware('role:parent,orang_tua');
            Route::get('executive/kpi', [MobileAnalyticsController::class, 'executiveKpi'])->middleware('role:admin,superadmin,kepala_sekolah,staf_tu');
        });

        // =====================================================================
        // FASE 74: DEDICATED HIGH-AVAILABILITY INFRASTRUCTURE
        // =====================================================================
        Route::prefix('infrastructure')->group(function () {
            Route::get('cluster/health', [CloudClusterController::class, 'getHealth'])->middleware('role:admin,superadmin');
            Route::post('cluster/metrics', [CloudClusterController::class, 'reportMetric'])->middleware('role:admin,superadmin');
            Route::post('cdn/resolve', [EdgeCdnController::class, 'resolveAsset']);
            Route::get('cdn/nodes', [EdgeCdnController::class, 'getNodes'])->middleware('role:admin,superadmin');
        });

        // =====================================================================
        // FASE 85: DEBLOAT, PENYEDERHANAAN & PRAGMATISME ARSITEKTUR
        // =====================================================================
        Route::prefix('debloat')->group(function () {
            Route::post('certificates/issue', [PragmaticDebloatApiController::class, 'issueCertificate']);
            Route::get('students/{studentId}/at-risk', [PragmaticDebloatApiController::class, 'evaluateAtRisk']);
            Route::post('sync/queue', [PragmaticDebloatApiController::class, 'syncQueue']);
            Route::get('sync/pull', [PragmaticDebloatApiController::class, 'syncPull']);
        });
    });

    // Public Web3 & Pragmatic Digital Verification Endpoints
    Route::get('blockchain/credentials/verify/{certificateNumber}', [BlockchainCredentialController::class, 'verifyPublic']);
    Route::get('certificates/verify/{documentNumber}', [PragmaticDebloatApiController::class, 'verifyCertificate']);
    Route::get('debloat/module-catalog', [PragmaticDebloatApiController::class, 'getModuleCatalog']);
    Route::get('debloat/vps/health', [PragmaticDebloatApiController::class, 'getVpsHealth']);
});
