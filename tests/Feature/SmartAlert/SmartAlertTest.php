<?php

use App\Models\AcademicYear;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\PaymentCategory;
use App\Models\Room;
use App\Models\SmartAlert;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\UksMedicine;
use App\Models\User;
use App\Services\SmartAlertService;
use Carbon\Carbon;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);

    // Admin
    $this->admin = User::factory()->create(['role' => 'admin']);

    // BK Teacher
    $this->bk = User::factory()->create(['role' => 'bk']);

    // Teacher
    $this->teacher = User::factory()->create(['role' => 'guru']);

    // Student
    $this->studentUser = User::factory()->create(['role' => 'siswa']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    $this->service = new SmartAlertService;
});

// ================================================================
// DETECT AT-RISK STUDENTS TESTS
// ================================================================

test('at-risk student alert is created when average grade is low', function () {
    // Create low grades (average 60)
    Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'tugas',
        'score' => 60.00,
        'recorded_by' => $this->teacher->id,
    ]);

    $alerts = $this->service->detectAtRiskStudents();

    expect($alerts)->toHaveCount(1);
    expect($alerts->first()->type)->toBe('at_risk_student');
    expect($alerts->first()->severity)->toBe('warning');
    expect($alerts->first()->alertable_id)->toBe($this->student->id);
});

// ================================================================
// DETECT ATTENDANCE DECLINE TESTS
// ================================================================

test('attendance decline alert is created when attendance rate consecutively drops', function () {
    // Create attendance records
    // Week 3 (highest presence - 100%)
    for ($i = 15; $i <= 17; $i++) {
        Attendance::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'date' => Carbon::now()->subDays($i)->toDateString(),
            'status' => 'H', // Present
            'recorded_by' => $this->teacher->id,
        ]);
    }

    // Week 2 (lower presence - 50%)
    Attendance::create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom->id,
        'date' => Carbon::now()->subDays(9)->toDateString(),
        'status' => 'H', // Present
        'recorded_by' => $this->teacher->id,
    ]);
    Attendance::create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom->id,
        'date' => Carbon::now()->subDays(10)->toDateString(),
        'status' => 'A', // Absent
        'recorded_by' => $this->teacher->id,
    ]);

    // Week 1 (lowest presence - 0%)
    for ($i = 1; $i <= 2; $i++) {
        Attendance::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'date' => Carbon::now()->subDays($i)->toDateString(),
            'status' => 'A', // Absent
            'recorded_by' => $this->teacher->id,
        ]);
    }

    $alerts = $this->service->detectAttendanceDecline();

    expect($alerts)->toHaveCount(1);
    expect($alerts->first()->type)->toBe('attendance_decline');
    expect($alerts->first()->severity)->toBe('critical'); // <50% in final week
});

// ================================================================
// PREDICT PAYMENT DEFAULT TESTS
// ================================================================

test('payment default alert is created for multiple overdue monthly billings', function () {
    $category = PaymentCategory::create([
        'name' => 'SPP Bulanan',
        'default_amount' => 500000,
        'is_monthly' => true,
    ]);

    // Create 2 overdue unpaid billings (4 and 3 months ago)
    Billing::create([
        'student_id' => $this->student->id,
        'payment_category_id' => $category->id,
        'academic_year_id' => $this->academicYear->id,
        'month' => '01',
        'amount' => 500000,
        'remaining_balance' => 500000,
        'due_date' => Carbon::now()->subMonths(4)->toDateString(),
        'status' => 'unpaid',
    ]);

    Billing::create([
        'student_id' => $this->student->id,
        'payment_category_id' => $category->id,
        'academic_year_id' => $this->academicYear->id,
        'month' => '02',
        'amount' => 500000,
        'remaining_balance' => 500000,
        'due_date' => Carbon::now()->subMonths(3)->toDateString(),
        'status' => 'unpaid',
    ]);

    $alerts = $this->service->predictPaymentDefault();

    expect($alerts)->toHaveCount(1);
    expect($alerts->first()->type)->toBe('payment_default');
    expect($alerts->first()->severity)->toBe('warning'); // exactly 2
});

// ================================================================
// DETECT TEACHER JOURNAL GAPS TESTS
// ================================================================

test('teacher journal gap alert is created for inactive journal filling', function () {
    // Create an old teaching journal for this teacher (10 working days ago)
    TeachingJournal::create([
        'user_id' => $this->teacher->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => Carbon::now()->subDays(14)->toDateString(),
        'topic' => 'Kinetika',
        'status' => 'submitted',
    ]);

    $alerts = $this->service->detectTeacherJournalGap();

    expect($alerts)->toHaveCount(1);
    expect($alerts->first()->type)->toBe('journal_gap');
    expect($alerts->first()->alertable_id)->toBe($this->teacher->id);
});

// ================================================================
// DETECT CRITICAL MEDICINE STOCK TESTS
// ================================================================

test('critical medicine alert is created for low stock or expired products', function () {
    // Expired medicine
    $medExpired = UksMedicine::create([
        'name' => 'Paracetamol',
        'category' => 'Obat Umum',
        'stock' => 10,
        'min_stock' => 5,
        'expiry_date' => Carbon::now()->subDays(1)->toDateString(),
    ]);

    // Low stock medicine
    $medLow = UksMedicine::create([
        'name' => 'Antiseptik',
        'category' => 'P3K',
        'stock' => 2,
        'min_stock' => 5,
        'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
    ]);

    $alerts = $this->service->detectCriticalMedicineStock();

    expect($alerts)->toHaveCount(2);

    $expiredAlert = $alerts->firstWhere('alertable_id', $medExpired->id);
    expect($expiredAlert->severity)->toBe('critical');

    $lowAlert = $alerts->firstWhere('alertable_id', $medLow->id);
    expect($lowAlert->severity)->toBe('warning');
});

// ================================================================
// DETECT OVERDUE ASSETS TESTS
// ================================================================

test('overdue asset alert is created for late sarpras returns', function () {
    $category = AssetCategory::create([
        'name' => 'Elektronik',
        'code' => 'CAT-ELEK',
    ]);

    $room = Room::create([
        'name' => 'Lab Komputer',
    ]);

    $asset = Asset::create([
        'asset_code' => 'AST-PROJ-001',
        'name' => 'Proyektor BenQ',
        'category_id' => $category->id,
        'room_id' => $room->id,
        'purchase_date' => now()->toDateString(),
        'purchase_price' => 5000000,
        'condition' => 'good',
        'status' => 'available',
        'is_borrowable' => true,
        'created_by' => $this->admin->id,
    ]);

    $loan = AssetLoan::create([
        'uuid' => (string) Str::uuid(),
        'asset_id' => $asset->id,
        'borrower_id' => $this->teacher->id,
        'borrowed_at' => Carbon::now()->subDays(5),
        'expected_return_at' => Carbon::now()->subDays(2),
        'status' => 'borrowed',
    ]);

    $alerts = $this->service->detectOverdueAssets();

    expect($alerts)->toHaveCount(1);
    expect($alerts->first()->type)->toBe('overdue_asset');
    expect($alerts->first()->alertable_id)->toBe($loan->id);
});

// ================================================================
// AUTHORIZATION TESTS
// ================================================================

test('admin can access smart alerts dashboard', function () {
    $this->actingAs($this->admin)->get(route('smart-alerts.index'))->assertOk();
});

test('student cannot access smart alerts dashboard', function () {
    $this->actingAs($this->studentUser)->get(route('smart-alerts.index'))->assertForbidden();
});

// ================================================================
// ACTION & RESOLUTION TESTS
// ================================================================

test('authorized staff can acknowledge and resolve alerts', function () {
    $alert = SmartAlert::create([
        'type' => 'critical_medicine',
        'severity' => 'critical',
        'title' => 'Obat Paracetamol Habis',
        'description' => 'Segera restock',
        'alertable_type' => UksMedicine::class,
        'alertable_id' => 1,
        'status' => 'active',
    ]);

    // 1. Acknowledge
    $responseAck = $this->actingAs($this->admin)->post(route('smart-alerts.acknowledge', $alert));
    $responseAck->assertRedirect();

    $alert->refresh();
    expect($alert->status)->toBe('acknowledged');
    expect($alert->acknowledged_by)->toBe($this->admin->id);

    // 2. Resolve
    $responseRes = $this->actingAs($this->admin)->post(route('smart-alerts.resolve', $alert), [
        'resolution_notes' => 'Obat paracetamol telah dibeli sebanyak 5 strip baru.',
    ]);
    $responseRes->assertRedirect();

    $alert->refresh();
    expect($alert->status)->toBe('resolved');
    expect($alert->resolution_notes)->toBe('Obat paracetamol telah dibeli sebanyak 5 strip baru.');
    expect($alert->resolved_by)->toBe($this->admin->id);
});
