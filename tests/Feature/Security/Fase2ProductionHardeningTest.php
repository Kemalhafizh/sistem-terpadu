<?php

use App\Events\ProctorInterventionEvent;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\CbtEssaySubmission;
use App\Models\CharacterAssessment;
use App\Models\CharacterDimension;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\MeritPoint;
use App\Models\MobileAppConfig;
use App\Models\OsisMember;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\QuestionBank;
use App\Models\Registration;
use App\Models\SpmbDocument;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Models\StudentPointLog;
use App\Models\Subject;
use App\Models\User;
use App\Services\DataVisualizationService;
use App\Services\PaymentGatewayService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\EntersCbtExam;

uses(EntersCbtExam::class);

beforeEach(function () {
    Cache::forget('academic_year_current');
});

afterEach(function () {
    Carbon::setTestNow();
    Cache::forget('academic_year_current');
});

test('mobile config accepts android versionCode 1 without forced update lockouts', function () {
    $response = $this->getJson('/api/v1/mobile/config?platform=android&build=1')
        ->assertOk();

    expect($response->json('app_name'))->toStartWith('Hanyakra ')
        ->and($response->json('version_check.current_client_build'))->toBe(1)
        ->and($response->json('version_check.minimum_required_build'))->toBe(1)
        ->and($response->json('version_check.latest_available_build'))->toBe(1)
        ->and($response->json('version_check.update_required'))->toBeFalse()
        ->and($response->json('version_check.update_available'))->toBeFalse();

    $config = MobileAppConfig::firstOrFail();
    $config->update(['min_android_build' => 2, 'latest_android_build' => 3]);

    $this->getJson('/api/v1/mobile/config?platform=android&build=1')
        ->assertOk()
        ->assertJsonPath('version_check.update_required', true)
        ->assertJsonPath('version_check.update_available', true);
});

test('spmb photos and documents stay on private local disk with authorized and signed routes', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $studentUser = User::factory()->create(['role' => 'student']);

    Storage::disk('local')->put('spmb/photos/applicant-a.jpg', 'private-photo-bytes');

    $registration = Registration::factory()->create([
        'registration_number' => 'SPMB-2026-0001',
        'photo_path' => 'spmb/photos/applicant-a.jpg',
    ]);

    // Public print-card photo refuses outsiders without matching session
    $this->get(route('spmb.print-card.photo', $registration))->assertForbidden();

    // Applicant owning session registration_number can view own print-card photo
    $this->withSession(['registration_number' => $registration->registration_number])
        ->get(route('spmb.print-card.photo', $registration))
        ->assertOk();

    // Admin photo route refuses guest/student and serves admin
    $this->get(route('spmb.admin.photo', $registration))->assertRedirect(route('login'));
    $this->actingAs($studentUser)->get(route('spmb.admin.photo', $registration))->assertForbidden();
    $this->actingAs($admin)->get(route('spmb.admin.photo', $registration))->assertOk();

    // API SPMB document upload stores on local disk and returns temporary signed URL
    $uploadResponse = $this->postJson("/api/v1/spmb/registrations/{$registration->registration_number}/documents", [
        'document_type' => 'kk',
        'file' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    $document = SpmbDocument::findOrFail($uploadResponse->json('data.id'));
    Storage::disk('local')->assertExists($document->file_path);
    Storage::disk('public')->assertMissing($document->file_path);

    $signedUrl = (string) $uploadResponse->json('data.file_url');
    $this->get($signedUrl)->assertOk();
    $this->get(route('api.v1.spmb.documents.download', ['document' => $document->id]))->assertForbidden();
    $this->get($signedUrl.'&signature=tampered')->assertForbidden();

    // Admin route /spmb-admin/documents/{document}/file resolves before /spmb-admin/{registration}
    $this->actingAs($studentUser)->get(route('spmb.admin.documents.file', $document))->assertForbidden();
    $this->actingAs($admin)->get(route('spmb.admin.documents.file', $document))->assertOk();
});

test('hrd employee documents and student mutation files enforce private storage and ownership checks', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $staffTu = User::factory()->create(['role' => 'staf_tu']);
    $studentUser = User::factory()->create(['role' => 'student']);

    $teacherA = User::factory()->create(['role' => 'guru']);
    $teacherB = User::factory()->create(['role' => 'guru']);

    $employeeA = Employee::create([
        'user_id' => $teacherA->id,
        'employee_number' => 'NIY-2026001',
        'position' => 'Guru Matematika',
        'employment_status' => 'gty',
    ]);
    $employeeB = Employee::create([
        'user_id' => $teacherB->id,
        'employee_number' => 'NIY-2026002',
        'position' => 'Guru Fisika',
        'employment_status' => 'gty',
    ]);

    $this->actingAs($admin)->post(route('hrd.employees.documents.store', $employeeA), [
        'title' => 'SK Pengangkatan',
        'type' => 'sk',
        'file' => UploadedFile::fake()->create('sk.pdf', 120, 'application/pdf'),
    ])->assertRedirect();

    $docA = EmployeeDocument::where('employee_id', $employeeA->id)->firstOrFail();
    Storage::disk('local')->assertExists($docA->file_path);
    Storage::disk('public')->assertMissing($docA->file_path);

    $this->actingAs($admin)
        ->get(route('hrd.employees.documents.show', [$employeeA, $docA]))
        ->assertOk();

    // Cross-employee IDOR must return 404
    $this->actingAs($admin)
        ->get(route('hrd.employees.documents.show', [$employeeB, $docA]))
        ->assertNotFound();

    $this->actingAs($studentUser)
        ->get(route('hrd.employees.documents.show', [$employeeA, $docA]))
        ->assertForbidden();

    // Student mutation document stored privately and served only to authorized roles
    $student = Student::factory()->create(['user_id' => $studentUser->id]);
    $this->actingAs($staffTu)->post(route('mutations.store'), [
        'student_id' => $student->id,
        'type' => 'out',
        'mutation_date' => now()->toDateString(),
        'reason' => 'Pindah domisili orang tua',
        'target_school' => 'SMA Negeri 1',
        'document' => UploadedFile::fake()->create('surat-pindah.pdf', 100, 'application/pdf'),
    ])->assertRedirect(route('mutations.index'));

    $mutation = StudentMutation::where('student_id', $student->id)->firstOrFail();
    Storage::disk('local')->assertExists($mutation->document_path);
    Storage::disk('public')->assertMissing($mutation->document_path);

    $this->actingAs($staffTu)->get(route('mutations.document', $mutation))->assertOk();
    $this->actingAs($studentUser)->get(route('mutations.document', $mutation))->assertForbidden();
});

test('cbt essay submissions are stored privately and restricted to submission owner or exam proctor', function () {
    Storage::fake('local');
    Storage::fake('public');

    $year = AcademicYear::factory()->create(['is_active' => true]);
    $classroom = Classroom::factory()->create(['academic_year' => $year->year]);
    $subject = Subject::factory()->create();

    $ownerTeacher = User::factory()->create(['role' => 'guru']);
    $otherTeacher = User::factory()->create(['role' => 'guru']);
    $studentAUser = User::factory()->create(['role' => 'student']);
    $studentA = Student::factory()->create(['user_id' => $studentAUser->id, 'classroom_id' => $classroom->id]);
    $studentBUser = User::factory()->create(['role' => 'student']);
    Student::factory()->create(['user_id' => $studentBUser->id, 'classroom_id' => $classroom->id]);

    $question = QuestionBank::create([
        'subject_id' => $subject->id,
        'created_by' => $ownerTeacher->id,
        'question_text' => 'Jelaskan hukum Newton II.',
        'type' => 'essay',
        'options' => null,
        'correct_answer' => null,
        'difficulty' => 'medium',
        'grade_level' => 'X',
    ]);

    $exam = Exam::factory()->create([
        'subject_id' => $subject->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $year->id,
        'created_by' => $ownerTeacher->id,
        'status' => 'published',
        'start_time' => now()->subMinutes(10),
        'end_time' => now()->addHours(2),
    ]);
    $exam->questions()->attach($question->id, ['order' => 1, 'weight' => 1.0]);

    $this->enterCbtExam($studentAUser, $exam);

    Sanctum::actingAs($studentAUser);
    $res = $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/upload-essay", [
        'question_id' => $question->id,
        'file' => UploadedFile::fake()->image('lembar-jawaban.jpg', 300, 300),
        'student_notes' => 'Jawaban uraian',
    ])->assertOk();

    $submission = CbtEssaySubmission::findOrFail($res->json('data.submission_id'));
    $fileUrl = (string) $res->json('data.file_url');

    expect($fileUrl)->toContain('/api/v1/cbt/essay-submissions/'.$submission->id);
    Storage::disk('local')->assertExists($submission->file_path);
    Storage::disk('public')->assertMissing($submission->file_path);

    // Owner student and exam creator teacher can access
    Sanctum::actingAs($studentAUser);
    $this->get($fileUrl)->assertOk();

    Sanctum::actingAs($ownerTeacher);
    $this->get($fileUrl)->assertOk();

    // Unrelated student and unrelated teacher must be blocked
    Sanctum::actingAs($studentBUser);
    $this->getJson($fileUrl)->assertForbidden();

    Sanctum::actingAs($otherTeacher);
    $this->getJson($fileUrl)->assertForbidden();
});

test('midtrans supports production snap host, clears stale snap tokens, validates fraud status, and prevents replay duplicates', function () {
    config([
        'services.midtrans.is_production' => 'false',
        'services.midtrans.server_key' => 'Mid-server-secret-test',
        'services.midtrans.client_key' => 'Mid-client-key-test',
        'services.midtrans.simulator' => false,
    ]);

    expect(app(PaymentGatewayService::class)->snapApiUrl())
        ->toBe('https://app.sandbox.midtrans.com/snap/v1/transactions');

    config(['services.midtrans.is_production' => 'true']);
    expect(app(PaymentGatewayService::class)->snapApiUrl())
        ->toBe('https://app.midtrans.com/snap/v1/transactions');

    Http::fake([
        'https://app.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-prod-token-001',
            'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/snap-prod-token-001',
        ], 200),
    ]);

    $year = AcademicYear::factory()->create(['is_active' => true]);
    $category = PaymentCategory::factory()->create(['name' => 'SPP Bulanan', 'default_amount' => 1000000]);
    $parentUser = User::factory()->create(['role' => 'parent']);
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parentUser->id,
    ]);

    $billing = Billing::create([
        'student_id' => $student->id,
        'payment_category_id' => $category->id,
        'academic_year_id' => $year->id,
        'month' => 'November 2026',
        'amount' => 1000000,
        'remaining_balance' => 1000000,
        'due_date' => now()->addDays(10)->toDateString(),
        'status' => 'unpaid',
    ]);

    $this->actingAs($parentUser)
        ->get(route('parent-portal.billings.checkout', $billing))
        ->assertOk();

    $billing->refresh();
    expect($billing->snap_token)->toBe('snap-prod-token-001')
        ->and($billing->payment_url)->toBe('https://app.midtrans.com/snap/v2/vtweb/snap-prod-token-001');

    // Updating remaining_balance automatically invalidates stale snap_token
    $billing->update(['remaining_balance' => 750000]);
    $billing->refresh();
    expect($billing->snap_token)->toBeNull()
        ->and($billing->payment_url)->toBeNull();

    // Restore full remaining balance and token before testing webhook flows
    $billing->update([
        'remaining_balance' => 1000000,
        'snap_token' => 'snap-active-token',
        'payment_url' => 'https://app.midtrans.com/snap/v2/vtweb/snap-active-token',
    ]);

    $makePayload = function (string $orderId, string $statusCode, string $grossAmount, string $txStatus, ?string $fraudStatus = null): array {
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'Mid-server-secret-test');

        return [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $txStatus,
            'fraud_status' => $fraudStatus,
            'payment_type' => 'credit_card',
            'signature_key' => $signature,
        ];
    };

    // Invalid signature is rejected with 403
    $invalidPayload = $makePayload("BILL-{$billing->id}-BAD", '200', '500000.00', 'capture', 'accept');
    $invalidPayload['signature_key'] = 'forged-signature';
    $this->postJson('/api/midtrans/webhook', $invalidPayload)->assertForbidden();

    // Fraud challenge stays unpaid without clearing token or creating payment
    $this->postJson('/api/midtrans/webhook', $makePayload("BILL-{$billing->id}-CHAL", '200', '500000.00', 'capture', 'challenge'))
        ->assertOk()
        ->assertJsonPath('processed', false)
        ->assertJsonPath('billing_status', 'unpaid');
    expect(Payment::count())->toBe(0);

    // Fraud deny clears snap_token so student/parent can re-checkout
    $this->postJson('/api/midtrans/webhook', $makePayload("BILL-{$billing->id}-DENY", '200', '500000.00', 'capture', 'deny'))
        ->assertOk()
        ->assertJsonPath('processed', false)
        ->assertJsonPath('billing_status', 'unpaid');
    $billing->refresh();
    expect($billing->snap_token)->toBeNull()
        ->and($billing->payment_url)->toBeNull()
        ->and(Payment::count())->toBe(0);

    // Partial accepted capture creates 1 payment and ignores duplicate order_id replay
    $acceptPayload = $makePayload("BILL-{$billing->id}-ACC1", '200', '400000.00', 'capture', 'accept');
    $this->postJson('/api/midtrans/webhook', $acceptPayload)
        ->assertOk()
        ->assertJsonPath('processed', true)
        ->assertJsonPath('billing_status', 'partial');

    $this->postJson('/api/midtrans/webhook', $acceptPayload)
        ->assertOk()
        ->assertJsonPath('processed', false)
        ->assertJsonPath('billing_status', 'partial');

    $billing->refresh();
    expect(Payment::count())->toBe(1)
        ->and((float) $billing->remaining_balance)->toBe(600000.0);
});

test('parent portal and report card pdf scope attendance to active academic year and handle H/S/I/A codes', function () {
    $oldYear = AcademicYear::factory()->create(['year' => '2024/2025', 'is_active' => false]);
    $activeYear = AcademicYear::factory()->create(['year' => '2025/2026', 'is_active' => true]);

    $teacher = User::factory()->create(['role' => 'guru']);
    $parent = User::factory()->create(['role' => 'parent']);
    $classroom = Classroom::factory()->create([
        'academic_year' => $activeYear->year,
        'teacher_id' => $teacher->id,
    ]);
    $student = Student::factory()->create([
        'classroom_id' => $classroom->id,
        'parent_id' => $parent->id,
    ]);

    // Previous academic year absences must be excluded from active dashboard, history, and report card PDF
    Attendance::create([
        'student_id' => $student->id,
        'academic_year_id' => $oldYear->id,
        'classroom_id' => $classroom->id,
        'date' => now()->subYear()->toDateString(),
        'status' => 'A',
        'recorded_by' => $teacher->id,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'academic_year_id' => $oldYear->id,
        'classroom_id' => $classroom->id,
        'date' => now()->subYear()->addDay()->toDateString(),
        'status' => 'S',
        'recorded_by' => $teacher->id,
    ]);

    // Active academic year attendances (1 H, 1 S, 1 I => 33.3% hadir, 0 A)
    Attendance::create([
        'student_id' => $student->id,
        'academic_year_id' => $activeYear->id,
        'classroom_id' => $classroom->id,
        'date' => now()->subDays(2)->toDateString(),
        'status' => 'H',
        'recorded_by' => $teacher->id,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'academic_year_id' => $activeYear->id,
        'classroom_id' => $classroom->id,
        'date' => now()->subDay()->toDateString(),
        'status' => 'S',
        'recorded_by' => $teacher->id,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'academic_year_id' => $activeYear->id,
        'classroom_id' => $classroom->id,
        'date' => now()->toDateString(),
        'status' => 'I',
        'recorded_by' => $teacher->id,
    ]);

    $this->actingAs($parent)->get(route('parent-portal.dashboard'))
        ->assertOk()
        ->assertViewHas('hadir', 1)
        ->assertViewHas('sakit', 1)
        ->assertViewHas('izin', 1)
        ->assertViewHas('alpa', 0)
        ->assertViewHas('hadirPercentage', 33.3);

    $historyResponse = $this->actingAs($parent)->get(route('parent-portal.attendance', $student))
        ->assertOk()
        ->assertViewHas('hadir', 1)
        ->assertViewHas('sakit', 1)
        ->assertViewHas('izin', 1)
        ->assertViewHas('alpa', 0)
        ->assertViewHas('hadirPercentage', 33.3);

    expect($historyResponse->viewData('attendances'))->toHaveCount(3);

    $capturedPdfAttendance = null;
    View::composer('report-cards.pdf', function ($view) use (&$capturedPdfAttendance) {
        $capturedPdfAttendance = $view->getData()['attendance'] ?? null;
    });

    $this->actingAs($parent)
        ->get(route('report-cards.pdf', $student))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($capturedPdfAttendance)->not->toBeNull()
        ->and((int) $capturedPdfAttendance->hadir)->toBe(1)
        ->and((int) $capturedPdfAttendance->sakit)->toBe(1)
        ->and((int) $capturedPdfAttendance->izin)->toBe(1)
        ->and((int) $capturedPdfAttendance->alpa)->toBe(0);
});

test('cbt proctor extend time adds persistent extra minutes and server rejects expired submissions after grace window', function () {
    Event::fake([ProctorInterventionEvent::class]);
    Storage::fake('local');

    $startMoment = Carbon::create(2026, 11, 20, 8, 0, 0);
    Carbon::setTestNow($startMoment);

    $year = AcademicYear::factory()->create(['is_active' => true]);
    $classroom = Classroom::factory()->create(['academic_year' => $year->year]);
    $subject = Subject::factory()->create();
    $teacher = User::factory()->create(['role' => 'guru']);
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'classroom_id' => $classroom->id]);

    $question = QuestionBank::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'question_text' => 'Ibu kota Jawa Tengah?',
        'type' => 'multiple_choice',
        'options' => ['A' => 'Semarang', 'B' => 'Surabaya'],
        'correct_answer' => 'A',
        'difficulty' => 'easy',
        'grade_level' => 'X',
    ]);

    $exam = Exam::factory()->create([
        'subject_id' => $subject->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $year->id,
        'created_by' => $teacher->id,
        'duration_minutes' => 30,
        'start_time' => $startMoment->copy()->subMinutes(5),
        'end_time' => $startMoment->copy()->addMinutes(40),
        'mobile_only' => false,
        'status' => 'published',
    ]);
    $exam->questions()->attach($question->id, ['order' => 1, 'weight' => 1.0]);

    $attempt = $this->enterCbtExam($studentUser, $exam);
    expect($attempt->deadline()->toDateTimeString())
        ->toBe($startMoment->copy()->addMinutes(30)->toDateTimeString());

    // Proctor extends time by 15 minutes; persistent extra_minutes extends both duration and end_time cap
    Sanctum::actingAs($teacher);
    $this->postJson("/api/v1/teacher/cbt/exams/{$exam->id}/proctor/extend-time", [
        'student_id' => $student->id,
        'minutes' => 15,
    ])->assertOk();

    $attempt->refresh();
    expect($attempt->extra_minutes)->toBe(15)
        ->and($attempt->deadline()->toDateTimeString())
        ->toBe($startMoment->copy()->addMinutes(45)->toDateTimeString());
    Event::assertDispatched(ProctorInterventionEvent::class);

    // Within extended deadline (+40m <= +45m) micro-sync still succeeds
    Carbon::setTestNow($startMoment->copy()->addMinutes(40));
    Sanctum::actingAs($studentUser);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/micro-sync", [
        'answers' => [$question->id => 'A'],
    ])->assertOk();

    // Past extended deadline + 60s grace (+46m05s), every CBT endpoint refuses and auto-marks timeout
    Carbon::setTestNow($startMoment->copy()->addMinutes(46)->addSeconds(5));

    $this->actingAs($studentUser)
        ->postJson(route('cbt.exams.save-answers', $exam), ['answers' => [$question->id => 'A']])
        ->assertForbidden();
    expect($attempt->fresh()->status)->toBe('timeout')
        ->and($attempt->fresh()->is_auto_submitted)->toBeTrue();

    // Already timed-out attempt stays locked on take, submit, micro-sync, essay upload, and API submit
    $this->actingAs($studentUser)
        ->get(route('cbt.exams.take', $exam))
        ->assertRedirect(route('cbt.exams.results', $exam));
    $this->actingAs($studentUser)
        ->postJson(route('cbt.exams.submit', $exam), ['answers' => [$question->id => 'A']])
        ->assertStatus(409);

    // Test fresh in-progress expired attempt directly against web submit & API endpoints
    $attempt->update(['status' => 'in_progress', 'is_auto_submitted' => false, 'submitted_at' => null]);
    $this->actingAs($studentUser)
        ->postJson(route('cbt.exams.submit', $exam), ['answers' => [$question->id => 'A']])
        ->assertForbidden();
    expect($attempt->fresh()->status)->toBe('timeout');

    $attempt->update(['status' => 'in_progress', 'is_auto_submitted' => false, 'submitted_at' => null]);
    Sanctum::actingAs($studentUser);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/micro-sync", [
        'answers' => [$question->id => 'A'],
    ])->assertForbidden();
    expect($attempt->fresh()->status)->toBe('timeout');

    $attempt->update(['status' => 'in_progress', 'is_auto_submitted' => false, 'submitted_at' => null]);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/upload-essay", [
        'question_id' => $question->id,
        'file' => UploadedFile::fake()->image('late-essay.jpg', 100, 100),
    ])->assertForbidden();
    expect($attempt->fresh()->status)->toBe('timeout');

    $attempt->update(['status' => 'in_progress', 'is_auto_submitted' => false, 'submitted_at' => null]);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/submit", [
        'answers' => [$question->id => 'A'],
    ])->assertForbidden();
    expect($attempt->fresh()->status)->toBe('timeout');
});

test('data visualization service computes real uncached metrics from database and honest empty baselines', function () {
    Carbon::setTestNow(Carbon::create(2026, 11, 20, 10, 0, 0));
    $service = app(DataVisualizationService::class);

    // Empty database returns clean zeros / empty collections without fabricated data or stale cache
    $emptyDist = $service->getGradeDistribution(999);
    expect($emptyDist['count'])->toBe(0)
        ->and($emptyDist['mean'])->toBe(0.0)
        ->and($emptyDist['median'])->toBe(0.0)
        ->and($emptyDist['standard_deviation'])->toBe(0.0)
        ->and($emptyDist['skewness'])->toBe(0.0)
        ->and($emptyDist['scores'])->toBe([]);

    expect($service->getClassComparison())->toBe([]);

    $emptyLive = $service->getLiveCounters();
    expect($emptyLive['active_students_online'])->toBe(0)
        ->and($emptyLive['attendance_today_percentage'])->toBe(0.0)
        ->and($emptyLive['active_cbt_exams'])->toBe(0)
        ->and($emptyLive['unpaid_billings_count'])->toBe(0)
        ->and($emptyLive['last_updated'])->toEndWith(' WIB');

    $emptyHeatmap = $service->getAttendanceHeatmap();
    expect($emptyHeatmap)->toHaveCount(60)
        ->and(collect($emptyHeatmap)->every(fn ($row) => $row['rate'] === 0.0 && $row['level'] === 0))->toBeTrue();

    // Populate real records across academic, ibadah, discipline, character, leadership, and operations
    $year = AcademicYear::factory()->create(['year' => '2026/2027', 'is_active' => true]);
    $year->activate();
    $teacher = User::factory()->create(['role' => 'guru']);
    $classroom = Classroom::factory()->create(['name' => 'X-IPA-1', 'academic_year' => $year->year, 'teacher_id' => $teacher->id]);
    $subject = Subject::factory()->create();

    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'classroom_id' => $classroom->id]);

    $exam = Exam::factory()->create([
        'subject_id' => $subject->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $year->id,
        'created_by' => $teacher->id,
        'status' => 'published',
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);

    ExamAttempt::create(['exam_id' => $exam->id, 'student_id' => $student->id, 'score' => 80, 'status' => 'graded']);

    // Verify immediate visibility (no stale Cache::remember)
    $distOne = $service->getGradeDistribution($exam->id);
    expect($distOne['count'])->toBe(1)
        ->and($distOne['mean'])->toBe(80.0);

    $otherStudent = Student::factory()->create(['classroom_id' => $classroom->id]);
    ExamAttempt::create(['exam_id' => $exam->id, 'student_id' => $otherStudent->id, 'score' => 94, 'status' => 'graded']);
    $distTwo = $service->getGradeDistribution($exam->id);
    expect($distTwo['count'])->toBe(2)
        ->and($distTwo['mean'])->toBe(87.0)
        ->and($distTwo['median'])->toBe(87.0)
        ->and($distTwo['buckets']['71-80'])->toBe(1)
        ->and($distTwo['buckets']['91-100'])->toBe(1);

    Attendance::create([
        'student_id' => $student->id,
        'academic_year_id' => $year->id,
        'classroom_id' => $classroom->id,
        'date' => now()->toDateString(),
        'status' => 'H',
        'recorded_by' => $teacher->id,
    ]);

    $earlierGrade = Grade::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
        'type' => 'uh',
        'score' => 80,
        'recorded_by' => $teacher->id,
    ]);
    DB::table('grades')
        ->where('id', $earlierGrade->id)
        ->update([
            'created_at' => now()->subMonths(2)->toDateTimeString(),
            'updated_at' => now()->subMonths(2)->toDateTimeString(),
        ]);

    Grade::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
        'type' => 'pts',
        'score' => 96,
        'recorded_by' => $teacher->id,
    ]);

    DB::table('mutabaah_logs')->insert([
        'student_id' => $student->id,
        'mutabaah_item_id' => null,
        'is_completed' => true,
        'date' => now()->toDateString(),
        'score' => 90,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    StudentPointLog::create([
        'student_id' => $student->id,
        'created_by' => $teacher->id,
        'points' => 10,
        'type' => 'penalty',
        'description' => 'Terlambat upacara',
    ]);

    $dimension = CharacterDimension::create([
        'name' => 'Beriman',
        'category' => 'islamic',
        'display_order' => 1,
        'is_active' => true,
    ]);
    CharacterAssessment::create([
        'student_id' => $studentUser->id,
        'assessor_id' => $teacher->id,
        'character_dimension_id' => $dimension->id,
        'academic_year' => $year->year,
        'semester' => 'odd',
        'score' => 3,
    ]);

    OsisMember::create([
        'student_id' => $student->id,
        'position' => 'Ketua Sekbid',
        'academic_year_id' => $year->id,
        'status' => 'active',
    ]);
    MeritPoint::create([
        'student_id' => $studentUser->id,
        'awarded_by' => $teacher->id,
        'character_dimension_id' => $dimension->id,
        'points' => 5,
        'reason' => 'Panitia Pesantren Kilat',
    ]);

    DB::table('active_sessions')->insert([
        'user_id' => $studentUser->id,
        'session_id' => 'sess-active-1',
        'ip_address' => '127.0.0.1',
        'last_activity_at' => now()->subMinutes(5),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $category = PaymentCategory::factory()->create();
    Billing::create([
        'student_id' => $student->id,
        'payment_category_id' => $category->id,
        'academic_year_id' => $year->id,
        'amount' => 350000,
        'remaining_balance' => 350000,
        'due_date' => now()->addWeek()->toDateString(),
        'status' => 'unpaid',
    ]);

    $radar = $service->getStudentRadarChart($student->id);
    $axes = collect($radar['dimensions'])->pluck('value', 'axis');
    expect((float) $axes['Akademik'])->toBe(88.0)
        ->and((float) $axes['Ibadah & Diniyyah'])->toBe(90.0)
        ->and((float) $axes['Disiplin'])->toBe(90.0)
        ->and((float) $axes['Karakter & Akhlak'])->toBe(75.0)
        ->and((float) $axes['Kepemimpinan'])->toBe(95.0);

    $trend = $service->getValueTrend($student->id);
    expect($trend['points'])->toHaveCount(6)
        ->and($trend['trend_percentage'])->toBe('+20.0%')
        ->and($trend['is_positive'])->toBeTrue();

    $comparison = $service->getClassComparison();
    expect($comparison)->toHaveCount(1)
        ->and($comparison[0]['className'])->toBe('X-IPA-1')
        ->and($comparison[0]['averageGrade'])->toBe(88.0)
        ->and($comparison[0]['attendanceRate'])->toBe(100.0)
        ->and($comparison[0]['disciplineIndex'])->toBe(90.0);

    $live = $service->getLiveCounters();
    expect($live['active_students_online'])->toBe(1)
        ->and($live['attendance_today_percentage'])->toBe(100.0)
        ->and($live['active_cbt_exams'])->toBe(1)
        ->and($live['unpaid_billings_count'])->toBe(1);

    $todayHeatmap = $service->getAttendanceHeatmap()[now()->toDateString()];
    expect($todayHeatmap['rate'])->toBe(100.0)
        ->and($todayHeatmap['level'])->toBe(4);
});
