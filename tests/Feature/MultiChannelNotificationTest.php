<?php

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\PaymentCategory;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Notifications\StudentAttendanceNotification;
use App\Notifications\StudentPointNotification;
use App\Services\AttendanceService;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('phone number normalization works correctly', function () {
    $service = new WhatsappService;

    expect($service->normalizePhoneNumber('0812345678'))->toBe('62812345678');
    expect($service->normalizePhoneNumber('812345678'))->toBe('62812345678');
    expect($service->normalizePhoneNumber('+62812345678'))->toBe('62812345678');
});

test('whatsapp service mock sends messages successfully in sandbox mode', function () {
    Setting::set('whatsapp_sandbox_mode', '1');

    $service = new WhatsappService;
    $log = $service->sendMessage('0812345678', 'Test Message', 'test');

    expect($log->status)->toBe('sent');
    expect($log->recipient)->toBe('62812345678');
    expect(WhatsappLog::count())->toBe(1);
});

test('whatsapp service mock logs failed status when number contains 999 or failed', function () {
    Setting::set('whatsapp_sandbox_mode', '1');

    $service = new WhatsappService;
    $log = $service->sendMessage('08123456999', 'Test Failed Message', 'test');

    expect($log->status)->toBe('failed');
    expect($log->error_message)->toContain('Simulated WhatsApp Gateway delivery failure');
});

test('whatsapp service reads sandbox flag and api token from config when settings are empty', function () {
    // Survives `php artisan config:cache`, unlike the env() calls it replaced.
    Setting::set('whatsapp_sandbox_mode', '0');
    config([
        'services.whatsapp.sandbox' => false,
        'services.whatsapp.api_token' => 'config-token-123',
    ]);
    Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);

    $log = (new WhatsappService)->sendMessage('0812345678', 'Live Message', 'test');

    expect($log->status)->toBe('sent');
    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'config-token-123')
        && $request['target'] === '62812345678');
});

test('whatsapp service stays in sandbox when config sandbox flag is on', function () {
    Setting::set('whatsapp_sandbox_mode', '0');
    config(['services.whatsapp.sandbox' => true]);
    Http::fake();

    $log = (new WhatsappService)->sendMessage('0812345678', 'Test Message', 'test');

    expect($log->status)->toBe('sent');
    Http::assertNothingSent();
});

test('alpa attendance automatically triggers student attendance notification to parent', function () {
    Setting::set('whatsapp_sandbox_mode', '1');
    Setting::set('whatsapp_enable_attendance', '1');

    $parent = User::factory()->create(['role' => 'parent', 'phone_number' => '08123456789']);
    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Budi Utomo']);

    $classroom = Classroom::factory()->create();
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
    ]);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);

    $attendanceService = new AttendanceService;
    $attendanceService->recordBulkAttendance(
        $classroom->id,
        date('Y-m-d'),
        [
            $student->id => [
                'status' => 'A',
                'notes' => 'Mangkir',
            ],
        ],
        User::factory()->create(['role' => 'admin'])->id
    );

    // Assert that a database notification was sent to parent
    $parent->refresh();
    expect($parent->unreadNotifications->count())->toBe(2); // One for attendance, one for BK point!

    $attendanceNotification = $parent->unreadNotifications->firstWhere('type', StudentAttendanceNotification::class);
    $pointNotification = $parent->unreadNotifications->firstWhere('type', StudentPointNotification::class);

    expect($attendanceNotification)->not->toBeNull();
    expect($attendanceNotification->data['title'])->toBe('Laporan Absensi Siswa');
    expect($attendanceNotification->data['message'])->toContain('Budi Utomo');

    expect($pointNotification)->not->toBeNull();
    expect($pointNotification->data['title'])->toBe('Pencatatan Poin Kedisiplinan');

    // Assert that a WhatsApp log was created for attendance
    $whatsappLogs = WhatsappLog::where('type', 'StudentAttendanceNotification')->get();
    expect($whatsappLogs->count())->toBe(1);
    expect($whatsappLogs->first()->recipient)->toBe('628123456789');
    expect($whatsappLogs->first()->message)->toContain('ALPA');
});

test('creating a student point log automatically triggers point notification to parent', function () {
    Setting::set('whatsapp_sandbox_mode', '1');
    Setting::set('whatsapp_enable_point', '1');

    $parent = User::factory()->create(['role' => 'parent', 'phone_number' => '08123456789']);
    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Budi Utomo']);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    StudentPointLog::create([
        'student_id' => $student->id,
        'created_by' => $admin->id,
        'points' => 15,
        'type' => 'reward',
        'description' => 'Juara 1 Lomba Pidato',
    ]);

    $parent->refresh();
    // One notification for BK Point
    expect($parent->unreadNotifications->count())->toBe(1);
    expect($parent->unreadNotifications->first()->data['title'])->toBe('Pencatatan Poin Kedisiplinan');
    expect($parent->unreadNotifications->first()->data['message'])->toContain('Budi Utomo');

    $whatsappLogs = WhatsappLog::where('type', 'StudentPointNotification')->get();
    expect($whatsappLogs->count())->toBe(1);
    expect($whatsappLogs->first()->message)->toContain('Juara 1 Lomba Pidato');
});

test('billing command send-reminders successfully notifies parents of unpaid SPP', function () {
    Setting::set('whatsapp_sandbox_mode', '1');
    Setting::set('whatsapp_enable_billing', '1');

    $parent = User::factory()->create(['role' => 'parent', 'phone_number' => '08123456789']);
    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Budi Utomo']);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
    ]);

    $category = PaymentCategory::factory()->create(['name' => 'SPP Bulanan']);
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);

    $billing = Billing::create([
        'student_id' => $student->id,
        'payment_category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'amount' => 500000,
        'due_date' => date('Y-m-d', strtotime('+7 days')),
        'status' => 'unpaid',
    ]);

    $this->artisan('billing:send-reminders')
        ->assertExitCode(0);

    $parent->refresh();
    expect($parent->unreadNotifications->count())->toBe(1);
    expect($parent->unreadNotifications->first()->data['title'])->toBe('Tagihan Pembayaran Baru');

    $whatsappLogs = WhatsappLog::where('type', 'BillingReminderNotification')->get();
    expect($whatsappLogs->count())->toBe(1);
    expect($whatsappLogs->first()->message)->toContain('SPP Bulanan');
});

test('notification toggle switches are respected', function () {
    Setting::set('whatsapp_sandbox_mode', '1');
    Setting::set('whatsapp_enable_attendance', '0'); // Disabled!

    $parent = User::factory()->create(['role' => 'parent', 'phone_number' => '08123456789']);
    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Budi Utomo']);

    $classroom = Classroom::factory()->create();
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
    ]);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);

    $attendanceService = new AttendanceService;
    $attendanceService->recordBulkAttendance(
        $classroom->id,
        date('Y-m-d'),
        [
            $student->id => [
                'status' => 'A',
                'notes' => 'Mangkir',
            ],
        ],
        User::factory()->create(['role' => 'admin'])->id
    );

    // Attendance WA should be blocked, but BK Point WA should still run if BK Point is enabled
    Setting::set('whatsapp_enable_point', '0'); // Disable BK points too

    // Check that no WhatsApp log for Attendance exists
    $attendanceWA = WhatsappLog::where('type', 'StudentAttendanceNotification')->count();
    expect($attendanceWA)->toBe(0);
});
