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
use App\Http\Middleware\ApiVersion;
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
// 📱 MOBILE REST API v1 (COMPREHENSIVE IMPLEMENTATION)
// =========================================================================
Route::prefix('v1')->middleware([ApiVersion::class, 'throttle:global'])->group(function () {

    // === PUBLIC AUTH ===
    Route::post('login', [ApiAuthController::class, 'login'])->middleware('throttle:login');

    // === PROTECTED ENDPOINTS ===
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth & Token Rotation
        Route::post('logout', [ApiAuthController::class, 'logout']);
        Route::post('refresh-token', [ApiAuthController::class, 'refreshToken']);
        Route::get('me', [ApiAuthController::class, 'me']);
        
        // General Profile (Backward Compatibility)
        Route::get('profile', [AuthController::class, 'profile'])->middleware('throttle:30,1');

        // === NEW SISWA SCOPED ===
        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('profile', [ApiStudentController::class, 'profile']);
            Route::get('schedule', [ApiStudentController::class, 'schedule']);
            Route::get('grades', [ApiStudentController::class, 'grades']);
            Route::get('attendance', [ApiStudentController::class, 'attendance']);
            Route::get('billings', [ApiStudentController::class, 'billings']);
            Route::get('points', [ApiStudentController::class, 'points']);
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
            Route::post('attendance', [ApiTeacherController::class, 'storeAttendance']);
            Route::get('journals', [ApiTeacherController::class, 'journals']);
            Route::post('journals', [ApiTeacherController::class, 'storeJournal']);
        });

        // === UNIVERSAL GENERAL ===
        Route::get('announcements', [ApiGeneralController::class, 'announcements']);
        Route::get('calendar', [ApiGeneralController::class, 'calendarEvents']);
        Route::get('notifications', [ApiGeneralController::class, 'notifications']);

        // =====================================================================
        // 🔄 BACKWARD COMPATIBLE DATA ENDPOINTS (USED BY LEGACY SUITE)
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
    });
});
