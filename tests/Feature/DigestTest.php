<?php

use App\Models\AcademicYear;
use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Ticket;
use App\Models\SmartAlert;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\ComplianceMilestone;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\StudentPointLog;
use App\Models\ElearningClass;
use App\Models\ElearningAssignment;
use App\Models\ElearningSubmission;
use App\Models\ScheduledReport;
use App\Services\DigestService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScheduledDigestMail;

beforeEach(function () {
    ScheduledReport::truncate();
    Attendance::truncate();
    Ticket::truncate();
    Billing::truncate();
    SmartAlert::truncate();
    Payment::truncate();
    PaymentCategory::truncate();
    ComplianceMilestone::truncate();
    Grade::truncate();
    StudentPointLog::truncate();
    ElearningSubmission::truncate();
    ElearningAssignment::truncate();
    AcademicYear::truncate();
});

test('unauthenticated guests are blocked from managing scheduled reports', function () {
    $this->get(route('admin.scheduled-reports.index'))->assertRedirect('/login');
    $this->post(route('admin.scheduled-reports.store'), [])->assertRedirect('/login');
});

test('non-admin users are blocked from managing scheduled reports', function () {
    $teacher = User::factory()->create(['role' => 'guru']);

    $this->actingAs($teacher)->get(route('admin.scheduled-reports.index'))->assertStatus(403);
    $this->actingAs($teacher)->post(route('admin.scheduled-reports.store'), [])->assertStatus(403);
});

test('admin can manage scheduled reports CRUD successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // 1. Store
    $response = $this->actingAs($admin)->post(route('admin.scheduled-reports.store'), [
        'name' => 'Digest Harian Admin Utama',
        'report_type' => 'daily_admin',
        'cron_expression' => '0 7 * * *',
        'delivery_method' => 'email',
        'recipients' => [
            ['type' => 'email', 'value' => 'admin@smaisa1.sch.id']
        ],
        'attach_pdf' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.scheduled-reports.index'));
    $this->assertDatabaseHas('scheduled_reports', [
        'name' => 'Digest Harian Admin Utama',
        'report_type' => 'daily_admin',
        'delivery_method' => 'email',
    ]);

    $report = ScheduledReport::first();

    // 2. Update
    $responseUpdate = $this->actingAs($admin)->put(route('admin.scheduled-reports.update', $report->id), [
        'name' => 'Digest Harian Admin Diubah',
        'report_type' => 'daily_admin',
        'cron_expression' => '0 8 * * *',
        'delivery_method' => 'both',
        'recipients' => [
            ['type' => 'email', 'value' => 'it-admin@smaisa1.sch.id']
        ],
        'attach_pdf' => 1,
        'is_active' => 1,
    ]);

    $responseUpdate->assertRedirect(route('admin.scheduled-reports.index'));
    $this->assertEquals('Digest Harian Admin Diubah', $report->fresh()->name);
    $this->assertEquals('0 8 * * *', $report->fresh()->cron_expression);

    // 3. Delete
    $responseDelete = $this->actingAs($admin)->delete(route('admin.scheduled-reports.destroy', $report->id));
    $responseDelete->assertRedirect(route('admin.scheduled-reports.index'));
    $this->assertDatabaseMissing('scheduled_reports', ['id' => $report->id]);
});

test('daily admin digest compiles correct metrics from SISTA core modules', function () {
    // Seed test data
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);
    $class = Classroom::factory()->create();
    $student->update(['classroom_id' => $class->id]);

    $teacher = User::factory()->create(['role' => 'guru']);
    $academicYear = AcademicYear::factory()->create([
        'year' => '2025/2026',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => now()->startOfYear()->toDateString(),
        'end_date' => now()->endOfYear()->toDateString(),
    ]);

    // 1. 2 Alpas today
    Attendance::create([
        'student_id' => $student->id,
        'classroom_id' => $class->id,
        'date' => now()->toDateString(),
        'status' => 'A',
        'academic_year_id' => $academicYear->id,
        'recorded_by' => $teacher->id,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'classroom_id' => $class->id,
        'date' => now()->toDateString(),
        'status' => 'A',
        'academic_year_id' => $academicYear->id,
        'recorded_by' => $teacher->id,
    ]);

    // 2. 1 Helpdesk Ticket today
    $ticket = new Ticket();
    $ticket->reporter_id = $studentUser->id;
    $ticket->title = 'Wifi terputus di kelas X-A';
    $ticket->category = 'it';
    $ticket->priority = 'medium';
    $ticket->status = 'open';
    $ticket->description = 'Tolong koneksikan ulang.';
    $ticket->save();

    // 3. 1 SPP Billing today
    $paymentCategory = new PaymentCategory();
    $paymentCategory->id = 1;
    $paymentCategory->name = 'SPP';
    $paymentCategory->default_amount = 500000;
    $paymentCategory->is_monthly = true;
    $paymentCategory->save();

    $billing = new Billing();
    $billing->student_id = $student->id;
    $billing->payment_category_id = $paymentCategory->id;
    $billing->academic_year_id = $academicYear->id;
    $billing->due_date = now()->addDays(5)->toDateString();
    $billing->amount = 500000;
    $billing->remaining_balance = 500000;
    $billing->status = 'unpaid';
    $billing->save();

    // 4. 1 active SmartAlert today
    $alert = new SmartAlert();
    $alert->type = 'finance';
    $alert->alertable_type = Student::class;
    $alert->alertable_id = $student->id;
    $alert->severity = 'critical';
    $alert->status = 'active';
    $alert->title = 'Tunggakan SPP Berjalan';
    $alert->description = 'Segera bayar.';
    $alert->save();

    $service = app(DigestService::class);
    $data = $service->generateDailyAdminDigest();

    $this->assertEquals(2, $data['alpa_count']);
    $this->assertEquals(1, $data['new_tickets']);
    $this->assertEquals(1, $data['new_billings']);
    $this->assertEquals(1, $data['active_alerts']);
});

test('weekly parent digest fetches correct assignments and grades for student', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);
    
    $subject = new Subject();
    $subject->code = 'KIM-X';
    $subject->name = 'Kimia';
    $subject->category = 'Nasional';
    $subject->kkm = 75;
    $subject->save();
    $class = Classroom::factory()->create();
    $student->update(['classroom_id' => $class->id]);

    $teacher = User::factory()->create(['role' => 'guru']);
    $academicYear = AcademicYear::factory()->create([
        'year' => '2025/2026',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => now()->startOfYear()->toDateString(),
        'end_date' => now()->endOfYear()->toDateString(),
    ]);

    // 1. Grade this week
    $grade = new Grade();
    $grade->student_id = $student->id;
    $grade->subject_id = $subject->id;
    $grade->recorded_by = $teacher->id;
    $grade->type = 'uh';
    $grade->score = 92;
    $grade->academic_year_id = $academicYear->id;
    $grade->save();

    // 2. Counseling BK points this week
    StudentPointLog::create([
        'student_id' => $student->id,
        'points' => 10,
        'description' => 'Terlambat upacara',
        'created_by' => 1,
    ]);

    // 3. Elearning pending assignment
    $elearningClass = ElearningClass::create([
        'subject_id' => $subject->id,
        'classroom_id' => $class->id,
        'teacher_id' => 1,
        'name' => 'Kimia Kelas X',
        'description' => 'Kelas E-Learning Kimia.',
    ]);

    $assignment = new ElearningAssignment();
    $assignment->elearning_class_id = $elearningClass->id;
    $assignment->title = 'Tugas Stoikiometri';
    $assignment->description = 'Selesaikan nomor 1-5.';
    $assignment->due_at = now()->addDays(2)->toDateTimeString();
    $assignment->save();

    $service = app(DigestService::class);
    $data = $service->generateWeeklyParentDigest($student);

    $this->assertEquals($student->name, $data['student_name']);
    $this->assertEquals(10, $data['bk_points']);
    $this->assertCount(1, $data['recent_grades']);
    $this->assertEquals(92, $data['recent_grades'][0]->score);
    $this->assertCount(1, $data['pending_assignments']);
    $this->assertEquals('Tugas Stoikiometri', $data['pending_assignments'][0]->title);
});

test('scheduled report dispatch sends out emails successfully', function () {
    Mail::fake();

    $report = ScheduledReport::create([
        'name' => 'Test Report',
        'report_type' => 'daily_admin',
        'cron_expression' => '* * * * *',
        'recipients' => [
            ['type' => 'email', 'value' => 'boss@smaisa1.sch.id']
        ],
        'delivery_method' => 'email',
        'attach_pdf' => false,
        'is_active' => true,
    ]);

    $service = app(DigestService::class);
    $service->sendDigest($report);

    Mail::assertSent(ScheduledDigestMail::class, function ($mail) use ($report) {
        return $mail->hasTo('boss@smaisa1.sch.id') && $mail->digestName === $report->name;
    });

    $this->assertNotNull($report->fresh()->last_sent_at);
});

test('console command scheduler matches cron minutes and triggers due digests', function () {
    Mail::fake();

    // Create due report (* * * * * matches any minute)
    $report = ScheduledReport::create([
        'name' => 'Due Report Harian',
        'report_type' => 'daily_admin',
        'cron_expression' => '* * * * *',
        'recipients' => [
            ['type' => 'email', 'value' => 'staff@smaisa1.sch.id']
        ],
        'delivery_method' => 'email',
        'attach_pdf' => false,
        'is_active' => true,
    ]);

    // Create inactive report (should NOT send)
    $inactive = ScheduledReport::create([
        'name' => 'Inactive Report Harian',
        'report_type' => 'daily_admin',
        'cron_expression' => '* * * * *',
        'recipients' => [
            ['type' => 'email', 'value' => 'staff@smaisa1.sch.id']
        ],
        'delivery_method' => 'email',
        'attach_pdf' => false,
        'is_active' => false,
    ]);

    // Run command
    $this->artisan('sista:send-digests')
        ->expectsOutput('Starting scheduled reports dispatcher...')
        ->expectsOutput("Report 'Due Report Harian' is due. Sending...")
        ->expectsOutput("Successfully sent report 'Due Report Harian'.")
        ->assertExitCode(0);

    Mail::assertSent(ScheduledDigestMail::class, function ($mail) {
        return $mail->hasTo('staff@smaisa1.sch.id') && $mail->digestName === 'Due Report Harian';
    });

    Mail::assertNotSent(ScheduledDigestMail::class, function ($mail) {
        return $mail->digestName === 'Inactive Report Harian';
    });
});
