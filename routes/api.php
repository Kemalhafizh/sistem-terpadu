<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\MobileDataController;
use App\Http\Controllers\Api\v1\ApiAuthController;
use App\Http\Controllers\Api\v1\ApiStudentController;
use App\Http\Controllers\Api\v1\ApiParentController;
use App\Http\Controllers\Api\v1\ApiTeacherController;
use App\Http\Controllers\Api\v1\ApiGeneralController;
use App\Http\Controllers\Api\V1\StudentApiController;
use App\Http\Controllers\Api\V1\ExternalIntegrationApiController;
use App\Http\Controllers\Api\V1\Mobile\MobileAuthController;
use App\Http\Controllers\Api\V1\Mobile\MobileConfigController;
use App\Http\Controllers\Api\V1\Mobile\MobileAttendanceController;
use App\Http\Controllers\Api\V1\Mobile\MobileSyncController;
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
  Route::get('rapor/verify/{hash}', [\App\Http\Controllers\Api\v1\ERaporApiController::class, 'verify']);

  // === FASE 94: SPMB / PPDB MOBILE (PUBLIC) ===
  Route::prefix('spmb')->group(function () {
    Route::get('waves', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'spmbWaves']);
    Route::post('register', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'spmbRegister']);
    Route::get('status/{registrationNo}', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'spmbCheckStatus']);
    Route::post('registrations/{registrationNo}/documents', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'spmbUploadDocument']);
  });

  // === PROTECTED ENDPOINTS ===
  Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Token Rotation
    Route::post('logout', [ApiAuthController::class, 'logout']);
    Route::post('refresh-token', [ApiAuthController::class, 'refreshToken']);
    Route::get('me', [ApiAuthController::class, 'me']);

    // Mobile Multi-Device & Biometrics
    Route::post('mobile/auth/biometric/register', [MobileAuthController::class, 'registerBiometric']);
    Route::post('mobile/devices', [MobileAuthController::class, 'registerDevice']);
    Route::get('mobile/devices', [MobileAuthController::class, 'getActiveDevices']);
    Route::delete('mobile/devices/{deviceId}', [MobileAuthController::class, 'revokeDevice']);

    // Mobile GPS Attendance & Dynamic QR
    Route::post('mobile/attendance/gps-checkin', [MobileAttendanceController::class, 'gpsCheckin']);
    Route::get('mobile/attendance/dynamic-qr', [MobileAttendanceController::class, 'generateDynamicQr']);
    Route::post('mobile/attendance/verify-qr', [MobileAttendanceController::class, 'verifyDynamicQr']);

    // Mobile multi-mode scanner (event ticket / visitor pass) — real EventTicketingService/VisitorService, gate & front-desk staff only.
    Route::post('mobile/scanner/event-ticket', [\App\Http\Controllers\Api\v1\Mobile\ScannerMobileApiController::class, 'scanEventTicket'])->middleware('role:admin,superadmin,staf_tu,guru,teacher,kepala_sekolah');
    Route::post('mobile/scanner/visitor-pass', [\App\Http\Controllers\Api\v1\Mobile\ScannerMobileApiController::class, 'scanVisitorPass'])->middleware('role:admin,superadmin,staf_tu,guru,teacher,kepala_sekolah');

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
      Route::get('cbt/exams/{id}/encrypted-payload', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'encryptedPayload']);
      Route::get('cbt/exams/{id}/decryption-key', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'decryptionKey']);
      Route::post('cbt/exams/{id}/micro-sync', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'microSync']);
      Route::post('cbt/exams/{id}/upload-essay', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'uploadEssay']);

      // FASE 87: CBT Token-Gated & Force-Close
      Route::post('cbt/exams/{id}/validate-token', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'validateToken']);
      Route::post('cbt/exams/{id}/force-close', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'forceClose']);
      Route::post('cbt/exams/{id}/heartbeat', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'heartbeat']);
      Route::post('cbt/exams/{id}/log-violation', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'logViolation']);

      Route::get('notifications', [ApiStudentController::class, 'notifications']);
      Route::post('notifications/{id}/read', [ApiStudentController::class, 'markNotificationRead']);
    });

    // === NEW ORANG TUA SCOPED ===
    Route::prefix('parent')->middleware('role:parent')->group(function () {
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
      Route::post('cbt/exams/{id}/proctor/warning', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'proctorWarning']);
      Route::post('cbt/exams/{id}/proctor/force-submit', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'proctorForceSubmit']);
      Route::post('cbt/exams/{id}/proctor/extend-time', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'proctorExtendTime']);

      // FASE 87: CBT Token Distribution & Student Reset
      Route::get('cbt/exams/{id}/token', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'getToken']);
      Route::post('cbt/exams/{id}/token/regenerate', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'regenerateToken']);
      Route::post('cbt/exams/{id}/token/reset-student', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'resetStudent']);

      // CBT Manual Authoring with LaTeX & Image Support
      Route::post('cbt/exams', [ApiTeacherController::class, 'storeExamWithQuestions']);
      Route::post('cbt/upload-image', [ApiTeacherController::class, 'uploadExamImage']);
    });

    // === FASE 88: BANK SOAL TERPUSAT & AUTO-GENERATE UJIAN CBT ===
    Route::prefix('question-bank')->group(function () {
      Route::get('categories', [\App\Http\Controllers\Api\v1\QuestionBankApiController::class, 'getCategories']);
      Route::get('items', [\App\Http\Controllers\Api\v1\QuestionBankApiController::class, 'getItems']);
      Route::post('items', [\App\Http\Controllers\Api\v1\QuestionBankApiController::class, 'store']);
      Route::post('auto-generate', [\App\Http\Controllers\Api\v1\QuestionBankApiController::class, 'autoGenerate']);
    });

    // === FASE 89: E-RAPOR KURIKULUM MERDEKA ===
    Route::prefix('rapor')->group(function () {
      Route::get('current', [\App\Http\Controllers\Api\v1\ERaporApiController::class, 'getCurrentRapor']);
      Route::get('child/{childId}', [\App\Http\Controllers\Api\v1\ERaporApiController::class, 'getChildRapor']);
      Route::post('generate-pdf', [\App\Http\Controllers\Api\v1\ERaporApiController::class, 'generatePdf']);
    });

    // === FASE 91: PENILAIAN HARIAN & REMEDIAL WORKFLOW ===
    Route::prefix('assessments')->group(function () {
      Route::get('teacher', [\App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController::class, 'teacherAssessments']);
      Route::post('teacher', [\App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController::class, 'storeAssessment']);
      Route::post('{id}/batch-scores', [\App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController::class, 'batchScores']);
      Route::post('{id}/auto-remedial', [\App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController::class, 'autoRemedial']);
      Route::get('student', [\App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController::class, 'studentAssessments']);
      Route::get('student/remedials', [\App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController::class, 'studentRemedials']);
      Route::post('remedials/{sessionId}/score', [\App\Http\Controllers\Api\v1\DailyAssessmentMobileApiController::class, 'submitRemedialScore']);
    });

    // === FASE 90: E-LEARNING MOBILE LMS TERPADU ===
    Route::prefix('elearning')->group(function () {
      Route::get('classes/student', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'studentClasses']);
      Route::get('classes/{classId}/materials', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'studentMaterials']);
      Route::get('classes/{classId}/assignments', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'studentAssignments']);
      Route::post('assignments/{id}/submit', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'submitAssignment']);
      Route::get('classes/teacher', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'teacherClasses']);
      Route::post('classes/{classId}/materials', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'storeMaterial']);
      Route::post('classes/{classId}/assignments', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'storeAssignment']);
      Route::get('assignments/{id}/submissions', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'getSubmissions']);
      Route::post('submissions/{id}/grade', [\App\Http\Controllers\Api\v1\ElearningMobileApiController::class, 'gradeSubmission']);
    });

    // === FASE 92: BIMBINGAN KONSELING (BK) MOBILE & AT-RISK MONITORING ===
    Route::prefix('counseling')->group(function () {
      Route::post('appointments/request', [\App\Http\Controllers\Api\v1\CounselingMobileApiController::class, 'requestAppointment']);
      Route::get('appointments/my', [\App\Http\Controllers\Api\v1\CounselingMobileApiController::class, 'studentAppointments']);

      Route::middleware('role:admin,superadmin,bk,guru,kepala_sekolah')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\v1\CounselingMobileApiController::class, 'dashboard']);
        Route::post('sessions', [\App\Http\Controllers\Api\v1\CounselingMobileApiController::class, 'storeSession']);
        Route::get('at-risk', [\App\Http\Controllers\Api\v1\CounselingMobileApiController::class, 'atRiskStudents']);
        Route::post('sessions/{id}/follow-up', [\App\Http\Controllers\Api\v1\CounselingMobileApiController::class, 'addFollowUp']);
      });
    });

    // === FASE 95: PROFIL KOMPREHENSIF SISWA 360 DERAJAT ===
    Route::prefix('profile/student')->group(function () {
      Route::get('me', [\App\Http\Controllers\Api\v1\StudentProfileApiController::class, 'myProfile']);
      Route::get('{id?}', [\App\Http\Controllers\Api\v1\StudentProfileApiController::class, 'show']);
      Route::get('{id?}/academic', [\App\Http\Controllers\Api\v1\StudentProfileApiController::class, 'academic']);
      Route::get('{id?}/ibadah', [\App\Http\Controllers\Api\v1\StudentProfileApiController::class, 'ibadah']);
      Route::get('{id?}/discipline', [\App\Http\Controllers\Api\v1\StudentProfileApiController::class, 'discipline']);
      Route::get('{id?}/health', [\App\Http\Controllers\Api\v1\StudentProfileApiController::class, 'health']);
    });

    // === FASE 93: KALENDER AKADEMIK & SYNC ICAL ===
    Route::prefix('academic-calendar')->group(function () {
      Route::get('/', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'calendarEvents']);
      Route::get('export-ical', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'exportIcal']);
    });


    // === FASE 96: REKAM MEDIS UKS & SKRINING KESEHATAN ===
    Route::prefix('uks')->group(function () {
      Route::get('visits/my', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'uksVisits']);
      Route::post('visits', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'recordUksVisit'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru');
      Route::get('screening/{studentId?}', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'healthScreening']);
      Route::get('students/search', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'searchUksStudents'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru');
      Route::get('medicines', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'uksMedicines'])->middleware('role:admin,superadmin,petugas_uks,staf_tu,guru');
    });

    // === FASE 97: JURNAL MENGAJAR GURU & AGENDA KELAS ===
    Route::prefix('teaching-journals')->middleware('role:admin,superadmin,guru')->group(function () {
      Route::get('my', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'teacherJournals']);
      Route::post('/', [\App\Http\Controllers\Api\v1\SchoolOperationsApiController::class, 'storeTeacherJournal']);
    });

    // =====================================================================
    // FASE 108: PERPUSTAKAAN DIGITAL MOBILE API
    // =====================================================================
    Route::prefix('library')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
      Route::get('books', [\App\Http\Controllers\Api\v1\LibraryMobileApiController::class, 'index']);
      Route::get('books/{id}', [\App\Http\Controllers\Api\v1\LibraryMobileApiController::class, 'show']);
      Route::post('self-checkout', [\App\Http\Controllers\Api\v1\LibraryMobileApiController::class, 'selfCheckout']);
      Route::get('my-loans', [\App\Http\Controllers\Api\v1\LibraryMobileApiController::class, 'myLoans']);
    });

    // =====================================================================
    // FASE 109: EKSTRAKURIKULER & OSIS MOBILE API
    // =====================================================================
    Route::prefix('extracurricular')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
      Route::get('list', [\App\Http\Controllers\Api\v1\ExtracurricularMobileApiController::class, 'index']);
      Route::post('{id}/register', [\App\Http\Controllers\Api\v1\ExtracurricularMobileApiController::class, 'register']);
      Route::post('attendance/submit', [\App\Http\Controllers\Api\v1\ExtracurricularMobileApiController::class, 'submitAttendance']);
      Route::get('osis-feed', [\App\Http\Controllers\Api\v1\ExtracurricularMobileApiController::class, 'osisFeed']);
    });

    // =====================================================================
    // FASE 110: PRESTASI & PORTOFOLIO MOBILE API
    // =====================================================================
    Route::prefix('achievements')->middleware('role:student,siswa,guru,teacher,admin,superadmin')->group(function () {
      Route::get('list', [\App\Http\Controllers\Api\v1\AchievementMobileApiController::class, 'index']);
      Route::post('upload', [\App\Http\Controllers\Api\v1\AchievementMobileApiController::class, 'upload']);
      Route::get('certificates', [\App\Http\Controllers\Api\v1\AchievementMobileApiController::class, 'certificates']);
      Route::get('academic-cv-summary', [\App\Http\Controllers\Api\v1\AchievementMobileApiController::class, 'cvSummary']);
      Route::post('{id}/verify', [\App\Http\Controllers\Api\v1\AchievementMobileApiController::class, 'verify']);
    });

    // =====================================================================
    // FASE 72.1: PEREKAM TAHSIN/TAHFIDZ & EVALUASI GURU MOBILE API
    // =====================================================================
    Route::prefix('tahsin')->middleware('role:student,siswa,guru,teacher,bk,admin,superadmin')->group(function () {
      Route::post('submissions', [\App\Http\Controllers\Api\v1\TahsinSubmissionApiController::class, 'store']);
      Route::get('submissions', [\App\Http\Controllers\Api\v1\TahsinSubmissionApiController::class, 'myIndex']);
      Route::get('submissions/{id}', [\App\Http\Controllers\Api\v1\TahsinSubmissionApiController::class, 'show']);
    });
    Route::prefix('teacher/tahsin')->middleware('role:guru,teacher,bk,admin,superadmin')->group(function () {
      Route::get('submissions', [\App\Http\Controllers\Api\v1\TahsinSubmissionApiController::class, 'teacherIndex']);
      Route::post('submissions/{id}/annotate', [\App\Http\Controllers\Api\v1\TahsinSubmissionApiController::class, 'annotate']);
      Route::post('submissions/{id}/mark-reviewed', [\App\Http\Controllers\Api\v1\TahsinSubmissionApiController::class, 'markReviewed']);
    });

    // FASE 111: EVALUASI GURU & E-VOTING OSIS MOBILE API
    Route::prefix('evaluations')->middleware('auth:sanctum')->group(function () {
      Route::get('teachers', [\App\Http\Controllers\Api\v1\EvaluationMobileApiController::class, 'teacherList']);
      Route::post('teachers/submit', [\App\Http\Controllers\Api\v1\EvaluationMobileApiController::class, 'submitTeacherEval']);
      Route::get('facilities', [\App\Http\Controllers\Api\v1\EvaluationMobileApiController::class, 'facilityList']);
      Route::post('facilities/submit', [\App\Http\Controllers\Api\v1\EvaluationMobileApiController::class, 'submitFacilityEval']);
      Route::get('osis-election', [\App\Http\Controllers\Api\v1\EvaluationMobileApiController::class, 'electionInfo']);
      Route::post('osis-election/vote', [\App\Http\Controllers\Api\v1\EvaluationMobileApiController::class, 'castVote']);
    });

    // FASE 112: UTBK TRYOUT & REKOMENDASI PTN MOBILE API
    Route::prefix('utbk')->middleware('auth:sanctum')->group(function () {
      Route::get('tryouts', [\App\Http\Controllers\Api\v1\UtbkMobileApiController::class, 'index']);
      Route::get('tryouts/{id}/questions', [\App\Http\Controllers\Api\v1\UtbkMobileApiController::class, 'questions']);
      Route::post('tryouts/{id}/submit', [\App\Http\Controllers\Api\v1\UtbkMobileApiController::class, 'submit']);
      Route::get('recommendations', [\App\Http\Controllers\Api\v1\UtbkMobileApiController::class, 'ptnRecommendations']);
      Route::get('alumni-directory', [\App\Http\Controllers\Api\v1\UtbkMobileApiController::class, 'alumniDirectory']);
    });

    // FASE 113: KEDISIPLINAN LENGKAP MOBILE API
    Route::prefix('discipline')->middleware('auth:sanctum')->group(function () {
        Route::get('/summary', [\App\Http\Controllers\Api\v1\DisciplineMobileApiController::class, 'summary']);
        Route::get('/history', [\App\Http\Controllers\Api\v1\DisciplineMobileApiController::class, 'history']);
        Route::get('/warning-letters', [\App\Http\Controllers\Api\v1\DisciplineMobileApiController::class, 'letters']);
        Route::post('/warning-letters/{id}/sign', [\App\Http\Controllers\Api\v1\DisciplineMobileApiController::class, 'signLetter']);
    });

    // FASE 114: ADMIN DASHBOARD & DOCUMENT MANAGEMENT MOBILE API
    Route::prefix('mobile/admin')->middleware(['role:admin,superadmin,kepsek,kepala_sekolah'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\v1\AdminMobileDashboardController::class, 'dashboard']);
        Route::get('/kpi', [\App\Http\Controllers\Api\v1\AdminMobileDashboardController::class, 'kpiDetail']);
        Route::get('/approvals', [\App\Http\Controllers\Api\v1\AdminMobileDashboardController::class, 'pendingApprovals']);
        Route::post('/approvals/{id}/action', [\App\Http\Controllers\Api\v1\AdminMobileDashboardController::class, 'processApproval']);
        Route::post('/emergency-broadcast', [\App\Http\Controllers\Api\v1\AdminMobileDashboardController::class, 'broadcastEmergency']);
    });

    Route::prefix('documents')->middleware('auth:sanctum')->group(function () {
        Route::get('/reports', [\App\Http\Controllers\Api\v1\DocumentMobileApiController::class, 'reportList']);
        Route::get('/certificates', [\App\Http\Controllers\Api\v1\DocumentMobileApiController::class, 'certificateList']);
        Route::post('/ocr/scan', [\App\Http\Controllers\Api\v1\DocumentMobileApiController::class, 'ocrScan']);
        Route::get('/ocr/{taskId}', [\App\Http\Controllers\Api\v1\DocumentMobileApiController::class, 'ocrResult']);
        Route::post('/signature/submit', [\App\Http\Controllers\Api\v1\DocumentMobileApiController::class, 'submitSignature']);
    });




    // =====================================================================
    // FASE 98: MODERN DESIGN SYSTEM API & THEMING ENGINE
    // =====================================================================
    Route::prefix('design')->group(function () {
      Route::get('tokens', [\App\Http\Controllers\Api\v1\DesignSystemController::class, 'getTokens']);
      Route::get('theme', [\App\Http\Controllers\Api\v1\DesignSystemController::class, 'getUserTheme']);
      Route::put('theme/preferences', [\App\Http\Controllers\Api\v1\DesignSystemController::class, 'updatePreferences']);
      Route::get('presets', [\App\Http\Controllers\Api\v1\DesignSystemController::class, 'getPresets']);
    });

    // =====================================================================
    // FASE 99: CONTEXTUAL & PREDICTIVE HOME API
    // =====================================================================
    Route::prefix('home')->group(function () {
      Route::get('contextual', [\App\Http\Controllers\Api\v1\ContextualHomeController::class, 'index']);
      Route::get('suggestions', [\App\Http\Controllers\Api\v1\ContextualHomeController::class, 'suggestions']);
      Route::post('dismiss-suggestion', [\App\Http\Controllers\Api\v1\ContextualHomeController::class, 'dismissSuggestion']);
      Route::post('suggestions/{id}/dismiss', [\App\Http\Controllers\Api\v1\ContextualHomeController::class, 'dismissSuggestion']);
    });

    // =====================================================================
    // FASE 100: MICRO-INTERACTION & ANIMATION CONFIG API
    // =====================================================================
    Route::prefix('config/animations')->group(function () {
      Route::get('/', [\App\Http\Controllers\Api\v1\ConfigController::class, 'getAnimations']);
      Route::put('preferences', [\App\Http\Controllers\Api\v1\ConfigController::class, 'updatePreferences']);
    });

    // =====================================================================
    // FASE 101: GAMIFICATION & ENGAGEMENT ENGINE
    // =====================================================================
    Route::prefix('gamification')->group(function () {
      Route::get('profile', [\App\Http\Controllers\Api\v1\GamificationController::class, 'getProfile']);
      Route::get('leaderboard', [\App\Http\Controllers\Api\v1\GamificationController::class, 'getLeaderboard']);
      Route::get('badges', [\App\Http\Controllers\Api\v1\GamificationController::class, 'getBadges']);
      Route::get('xp-history', [\App\Http\Controllers\Api\v1\GamificationController::class, 'getXpHistory']);
      Route::get('history', [\App\Http\Controllers\Api\v1\GamificationController::class, 'getXpHistory']);
      Route::get('streak-calendar', [\App\Http\Controllers\Api\v1\GamificationController::class, 'getStreakCalendar']);
      Route::get('streak', [\App\Http\Controllers\Api\v1\GamificationController::class, 'getStreakCalendar']);
    });

    // =====================================================================
    // FASE 102: SMART NOTIFICATION SYSTEM — PRIORITY INBOX
    // =====================================================================
    Route::prefix('notifications')->group(function () {
      Route::get('preferences', [\App\Http\Controllers\Api\v1\SmartNotificationController::class, 'getPreferences']);
      Route::put('preferences', [\App\Http\Controllers\Api\v1\SmartNotificationController::class, 'updatePreferences']);
      Route::get('digest', [\App\Http\Controllers\Api\v1\SmartNotificationController::class, 'getDigest']);
    });
    Route::prefix('notifications/preferences')->group(function () {
      Route::get('/', [\App\Http\Controllers\Api\v1\SmartNotificationController::class, 'getPreferences']);
      Route::put('/', [\App\Http\Controllers\Api\v1\SmartNotificationController::class, 'updatePreferences']);
    });

    // =====================================================================
    // FASE 104: ADVANCED DATA VISUALIZATION WEB & API
    // =====================================================================
    Route::prefix('viz')->group(function () {
      Route::get('heatmap/attendance', [\App\Http\Controllers\Api\v1\VizController::class, 'attendanceHeatmap']);
      Route::get('distribution/grades/{examId}', [\App\Http\Controllers\Api\v1\VizController::class, 'gradeDistribution']);
      Route::get('radar/student/{studentId}', [\App\Http\Controllers\Api\v1\VizController::class, 'studentRadar']);
      Route::get('trend/student/{studentId}', [\App\Http\Controllers\Api\v1\VizController::class, 'studentTrend']);
      Route::get('comparison/classes', [\App\Http\Controllers\Api\v1\VizController::class, 'classComparison']);
      Route::get('counters/live', [\App\Http\Controllers\Api\v1\VizController::class, 'liveCounters']);
    });

    // =====================================================================
    // FASE 105: OFFLINE-FIRST SYNC ENGINE 2.0 API
    // =====================================================================
    Route::prefix('sync')->group(function () {
      Route::get('delta', [\App\Http\Controllers\Api\v1\SyncController::class, 'getDelta']);
      Route::post('batch', [\App\Http\Controllers\Api\v1\SyncController::class, 'processBatch']);
      Route::get('check-version', [\App\Http\Controllers\Api\v1\SyncController::class, 'checkVersion']);
      Route::get('catch-up', [\App\Http\Controllers\Api\v1\SyncController::class, 'catchUp']);
    });

    // =====================================================================
    // FASE 106: PARENT EXPERIENCE OVERHAUL API
    // =====================================================================
    Route::prefix('parent')->middleware('role:parent,orang_tua,admin,superadmin,student,siswa')->group(function () {
      Route::get('child/{childId}/feed', [\App\Http\Controllers\Api\v1\ParentPortalController::class, 'activityFeed']);
      Route::get('feed', [\App\Http\Controllers\Api\v1\ParentPortalController::class, 'activityFeed']);
      Route::get('child/{childId}/digest', [\App\Http\Controllers\Api\v1\ParentPortalController::class, 'weeklyDigest']);
      Route::get('weekly-digest', [\App\Http\Controllers\Api\v1\ParentPortalController::class, 'weeklyDigest']);
      Route::get('child/{childId}/benchmark', [\App\Http\Controllers\Api\v1\ParentPortalController::class, 'benchmarkClass']);
      Route::get('comparison', [\App\Http\Controllers\Api\v1\ParentPortalController::class, 'benchmarkClass']);
      Route::get('actions', [\App\Http\Controllers\Api\v1\ParentPortalController::class, 'quickActions']);
    });

    Route::get('ai/tutor/suggestions', [\App\Http\Controllers\Api\v1\MobileV2ApiController::class, 'getAiTutorSuggestions']);


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
      Route::post('geofence/checkin', [\App\Http\Controllers\Api\V1\Mobile\GeofenceController::class, 'checkin']);
    });

    // =====================================================================
    // FASE 70: GENERATIVE AI & AUTONOMOUS LEARNING ENGINE
    // =====================================================================
    Route::prefix('ai')->group(function () {
      Route::post('tutor/session', [\App\Http\Controllers\Ai\AiTutorController::class, 'startSession']);
      Route::post('tutor/session/{id}/message', [\App\Http\Controllers\Ai\AiTutorController::class, 'sendMessage']);
      Route::get('tutor/session/{id}', [\App\Http\Controllers\Ai\AiTutorController::class, 'getHistory']);
      Route::post('essay/grade', [\App\Http\Controllers\Ai\NlpEssayGraderController::class, 'submitEssay']);
      Route::get('essay/{id}', [\App\Http\Controllers\Ai\NlpEssayGraderController::class, 'getSubmission']);
      Route::post('lesson-plan/generate', [\App\Http\Controllers\Ai\AiLessonPlanController::class, 'generate']);
      Route::get('lesson-plans', [\App\Http\Controllers\Ai\AiLessonPlanController::class, 'getPlans']);
    });

    // =====================================================================
    // FASE 71: IMMUTABLE BLOCKCHAIN CREDENTIALING (WEB3)
    // =====================================================================
    Route::prefix('blockchain')->group(function () {
      Route::post('credentials/issue', [\App\Http\Controllers\Blockchain\BlockchainCredentialController::class, 'issue']);
      Route::get('credentials/my', [\App\Http\Controllers\Blockchain\BlockchainCredentialController::class, 'myCredentials']);
      Route::get('did/me', [\App\Http\Controllers\Blockchain\StudentDidController::class, 'getMyDid']);
      Route::get('did/passport', [\App\Http\Controllers\Blockchain\StudentDidController::class, 'getPassport']);
    });

    // =====================================================================
    // FASE 72: SMART CAMPUS IOT 2.0 & FACIAL RECOGNITION
    // =====================================================================
    Route::prefix('iot')->group(function () {
      Route::post('face/enroll', [\App\Http\Controllers\Iot\FacialRecognitionAttendanceController::class, 'enroll']);
      Route::post('face/match', [\App\Http\Controllers\Iot\FacialRecognitionAttendanceController::class, 'matchGate']);
      Route::post('energy/sensor', [\App\Http\Controllers\Iot\EnergyManagementController::class, 'updateSensor'])->middleware('role:admin,superadmin,staf_tu,operator');
      Route::get('energy/dashboard', [\App\Http\Controllers\Iot\EnergyManagementController::class, 'getDashboard']);
    });

    // =====================================================================
    // FASE 73: BIG DATA PREDICTIVE ANALYTICS
    // =====================================================================
    Route::prefix('analytics')->group(function () {
      Route::get('student/{id}/risk', [\App\Http\Controllers\Analytics\AtRiskStudentPredictionController::class, 'analyzeStudent'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah,bk,guru');
      Route::get('at-risk/dashboard', [\App\Http\Controllers\Analytics\AtRiskStudentPredictionController::class, 'getAtRiskDashboard'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah,bk');
      Route::post('treasury/forecast', [\App\Http\Controllers\Analytics\TreasuryForecastController::class, 'generate'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah');
      Route::get('treasury/forecasts', [\App\Http\Controllers\Analytics\TreasuryForecastController::class, 'getForecasts'])->middleware('role:admin,superadmin,staf_tu,kepala_sekolah');

      // Mobile app analytics screens (student/teacher/parent/executive dashboards) — real aggregates only.
      Route::get('student/summary', [\App\Http\Controllers\Analytics\MobileAnalyticsController::class, 'studentSummary'])->middleware('role:student,siswa');
      Route::get('teacher/my-classes', [\App\Http\Controllers\Analytics\MobileAnalyticsController::class, 'teacherClasses'])->middleware('role:guru,teacher,bk,admin,superadmin,kepala_sekolah');
      Route::get('teacher/class-performance', [\App\Http\Controllers\Analytics\MobileAnalyticsController::class, 'classPerformance'])->middleware('role:guru,teacher,bk,admin,superadmin,kepala_sekolah');
      Route::get('parent/child-progress', [\App\Http\Controllers\Analytics\MobileAnalyticsController::class, 'parentChildProgress'])->middleware('role:parent,orang_tua');
      Route::get('executive/kpi', [\App\Http\Controllers\Analytics\MobileAnalyticsController::class, 'executiveKpi'])->middleware('role:admin,superadmin,kepala_sekolah,staf_tu');
    });

    // =====================================================================
    // FASE 74: DEDICATED HIGH-AVAILABILITY INFRASTRUCTURE
    // =====================================================================
    Route::prefix('infrastructure')->group(function () {
      Route::get('cluster/health', [\App\Http\Controllers\Infrastructure\CloudClusterController::class, 'getHealth'])->middleware('role:admin,superadmin');
      Route::post('cluster/metrics', [\App\Http\Controllers\Infrastructure\CloudClusterController::class, 'reportMetric'])->middleware('role:admin,superadmin');
      Route::post('cdn/resolve', [\App\Http\Controllers\Infrastructure\EdgeCdnController::class, 'resolveAsset']);
      Route::get('cdn/nodes', [\App\Http\Controllers\Infrastructure\EdgeCdnController::class, 'getNodes'])->middleware('role:admin,superadmin');
    });

    // =====================================================================
    // FASE 85: DEBLOAT, PENYEDERHANAAN & PRAGMATISME ARSITEKTUR
    // =====================================================================
    Route::prefix('debloat')->group(function () {
      Route::post('certificates/issue', [\App\Http\Controllers\Debloat\PragmaticDebloatApiController::class, 'issueCertificate']);
      Route::get('students/{studentId}/at-risk', [\App\Http\Controllers\Debloat\PragmaticDebloatApiController::class, 'evaluateAtRisk']);
      Route::post('sync/queue', [\App\Http\Controllers\Debloat\PragmaticDebloatApiController::class, 'syncQueue']);
      Route::get('sync/pull', [\App\Http\Controllers\Debloat\PragmaticDebloatApiController::class, 'syncPull']);
    });
  });

  // Public Web3 & Pragmatic Digital Verification Endpoints
  Route::get('blockchain/credentials/verify/{certificateNumber}', [\App\Http\Controllers\Blockchain\BlockchainCredentialController::class, 'verifyPublic']);
  Route::get('certificates/verify/{documentNumber}', [\App\Http\Controllers\Debloat\PragmaticDebloatApiController::class, 'verifyCertificate']);
  Route::get('debloat/module-catalog', [\App\Http\Controllers\Debloat\PragmaticDebloatApiController::class, 'getModuleCatalog']);
  Route::get('debloat/vps/health', [\App\Http\Controllers\Debloat\PragmaticDebloatApiController::class, 'getVpsHealth']);
});

