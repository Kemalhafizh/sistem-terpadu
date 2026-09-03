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
  Route::post('mobile/auth/biometric/challenge', [MobileAuthController::class, 'biometricChallenge']);
  Route::post('mobile/auth/biometric/verify', [MobileAuthController::class, 'biometricVerify']);

  // === PROTECTED ENDPOINTS ===
  Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Token Rotation
    Route::post('logout', [ApiAuthController::class, 'logout']);
    Route::post('refresh-token', [ApiAuthController::class, 'refreshToken']);
    Route::get('me', [ApiAuthController::class, 'me']);

    // Mobile Multi-Device & Biometrics
    Route::post('mobile/auth/biometric/register', [MobileAuthController::class, 'registerBiometric']);
    Route::get('mobile/devices', [MobileAuthController::class, 'getActiveDevices']);
    Route::delete('mobile/devices/{deviceId}', [MobileAuthController::class, 'revokeDevice']);

    // Mobile GPS Attendance & Dynamic QR
    Route::post('mobile/attendance/gps-checkin', [MobileAttendanceController::class, 'gpsCheckin']);
    Route::get('mobile/attendance/dynamic-qr', [MobileAttendanceController::class, 'generateDynamicQr']);
    Route::post('mobile/attendance/verify-qr', [MobileAttendanceController::class, 'verifyDynamicQr']);

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

      // FASE 86: Proctor Live Interventions
      Route::post('cbt/exams/{id}/proctor/warning', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'proctorWarning']);
      Route::post('cbt/exams/{id}/proctor/force-submit', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'proctorForceSubmit']);
      Route::post('cbt/exams/{id}/proctor/extend-time', [\App\Http\Controllers\Api\v1\CbtEnterpriseApiController::class, 'proctorExtendTime']);

      // FASE 87: CBT Token Distribution & Student Reset
      Route::get('cbt/exams/{id}/token', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'getToken']);
      Route::post('cbt/exams/{id}/token/regenerate', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'regenerateToken']);
      Route::post('cbt/exams/{id}/token/reset-student', [\App\Http\Controllers\Api\v1\CbtAccessControlApiController::class, 'resetStudent']);
    });

    // === UNIVERSAL GENERAL ===
    Route::get('announcements', [ApiGeneralController::class, 'announcements']);
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
      Route::post('energy/sensor', [\App\Http\Controllers\Iot\EnergyManagementController::class, 'updateSensor']);
      Route::get('energy/dashboard', [\App\Http\Controllers\Iot\EnergyManagementController::class, 'getDashboard']);
    });

    // =====================================================================
    // FASE 73: BIG DATA PREDICTIVE ANALYTICS
    // =====================================================================
    Route::prefix('analytics')->group(function () {
      Route::get('student/{id}/risk', [\App\Http\Controllers\Analytics\AtRiskStudentPredictionController::class, 'analyzeStudent']);
      Route::get('at-risk/dashboard', [\App\Http\Controllers\Analytics\AtRiskStudentPredictionController::class, 'getAtRiskDashboard']);
      Route::post('treasury/forecast', [\App\Http\Controllers\Analytics\TreasuryForecastController::class, 'generate']);
      Route::get('treasury/forecasts', [\App\Http\Controllers\Analytics\TreasuryForecastController::class, 'getForecasts']);
    });

    // =====================================================================
    // FASE 74: DEDICATED HIGH-AVAILABILITY INFRASTRUCTURE
    // =====================================================================
    Route::prefix('infrastructure')->group(function () {
      Route::get('cluster/health', [\App\Http\Controllers\Infrastructure\CloudClusterController::class, 'getHealth']);
      Route::post('cluster/metrics', [\App\Http\Controllers\Infrastructure\CloudClusterController::class, 'reportMetric']);
      Route::post('cdn/resolve', [\App\Http\Controllers\Infrastructure\EdgeCdnController::class, 'resolveAsset']);
      Route::get('cdn/nodes', [\App\Http\Controllers\Infrastructure\EdgeCdnController::class, 'getNodes']);
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

