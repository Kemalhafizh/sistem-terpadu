<?php

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\ScheduleTime;
use App\Models\SessionAttendance;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\ClassSessionService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Cache::flush();
    Storage::fake('local');
    Notification::fake();

    $this->travelTo(Carbon::parse('2026-10-14 08:00:00'));

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->homeroom = User::factory()->create(['role' => 'guru', 'name' => 'Wali Kelas X-1']);
    $this->otherTeacher = User::factory()->create(['role' => 'guru', 'name' => 'Wali Kelas X-2']);
    $this->kesiswaan = User::factory()->create(['role' => 'waka_kesiswaan', 'name' => 'Waka Kesiswaan']);

    $this->classroom = Classroom::factory()->create([
        'name' => 'X-1',
        'academic_year' => '2026/2027',
        'teacher_id' => $this->homeroom->id,
    ]);

    $this->otherClassroom = Classroom::factory()->create([
        'name' => 'X-2',
        'academic_year' => '2026/2027',
        'teacher_id' => $this->otherTeacher->id,
    ]);

    $this->parent = User::factory()->create(['role' => 'parent', 'name' => 'Wali Siswa']);
    $this->otherParent = User::factory()->create(['role' => 'parent', 'name' => 'Wali Siswa Lain']);

    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Siswa X-1']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'parent_id' => $this->parent->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '260111',
        'nisn' => '0011122233',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('1-day sick leave auto-approves by default and syncs today daily attendance and class session roster', function () {
    Sanctum::actingAs($this->parent);

    $response = $this->postJson('/api/v1/parent/leave-requests', [
        'student_id' => $this->student->id,
        'type' => 'sakit',
        'start_date' => '2026-10-14',
        'end_date' => '2026-10-14',
        'reason' => 'Demam tinggi sejak pagi',
    ])->assertCreated();

    $leaveUuid = (string) $response->json('data.uuid');
    $leave = StudentLeaveRequest::where('uuid', $leaveUuid)->firstOrFail();

    expect($leave->status)->toBe(StudentLeaveRequest::STATUS_AUTO_APPROVED)
        ->and($leave->auto_approved)->toBeTrue();

    // Daily attendance row for today is automatically created with status S
    $daily = Attendance::where('student_id', $this->student->id)
        ->whereDate('date', '2026-10-14')
        ->whereNull('schedule_id')
        ->firstOrFail();

    expect($daily->status)->toBe('S')
        ->and($daily->student_leave_request_id)->toBe($leave->id);

    // When a subject teacher starts a ClassSession today, roster pre-fills 'sakit' instead of 'alpha'
    $preset = SchedulePreset::create(['name' => 'Reguler', 'is_active' => true]);
    ScheduleTime::create([
        'schedule_preset_id' => $preset->id,
        'session_number' => 1,
        'start_time' => '07:30',
        'end_time' => '09:00',
    ]);

    $subject = Subject::factory()->create(['name' => 'Fisika']);
    $schedule = Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom->id,
        'subject_id' => $subject->id,
        'teacher_id' => $this->homeroom->id,
        'day' => 'Rabu',
        'session_start' => 1,
        'session_end' => 1,
    ]);

    $session = app(ClassSessionService::class)->start($this->homeroom, $schedule->id, 'Hukum Newton');

    expect($session->status)->toBe(ClassSession::STATUS_ACTIVE);

    $sessionAtt = SessionAttendance::where('class_session_id', $session->id)
        ->where('student_id', $this->student->id)
        ->firstOrFail();

    expect($sessionAtt->status)->toBe(SessionAttendance::STATUS_SAKIT)
        ->and($sessionAtt->student_leave_request_id)->toBe($leave->id);
});

test('sick leave over 2 days requires medical letter and multi-day approved leave never writes future-dated attendance until scheduler runs on that day', function () {
    Sanctum::actingAs($this->parent);

    // 3-day sick leave (Oct 14 to Oct 16) without attachment fails validation
    $this->postJson('/api/v1/parent/leave-requests', [
        'student_id' => $this->student->id,
        'type' => 'sakit',
        'start_date' => '2026-10-14',
        'end_date' => '2026-10-16',
        'reason' => 'Rawat jalan tipes 3 hari',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('attachment');

    // Submit with private medical letter PDF
    $createRes = $this->postJson('/api/v1/parent/leave-requests', [
        'student_id' => $this->student->id,
        'type' => 'sakit',
        'start_date' => '2026-10-14',
        'end_date' => '2026-10-16',
        'reason' => 'Rawat jalan tipes 3 hari',
        'attachment' => UploadedFile::fake()->create('surat-dokter.pdf', 150, 'application/pdf'),
    ])->assertCreated();

    $leave = StudentLeaveRequest::where('uuid', $createRes->json('data.uuid'))->firstOrFail();
    expect($leave->status)->toBe(StudentLeaveRequest::STATUS_PENDING)
        ->and($leave->days_count)->toBe(3);

    Storage::disk('local')->assertExists($leave->attachment_path);

    // Overlapping request for the same student is rejected
    $this->postJson('/api/v1/parent/leave-requests', [
        'student_id' => $this->student->id,
        'type' => 'izin',
        'start_date' => '2026-10-15',
        'end_date' => '2026-10-15',
        'reason' => 'Acara keluarga',
    ])->assertStatus(422);

    // Other classroom teacher cannot approve or view attachment (IDOR check)
    Sanctum::actingAs($this->otherTeacher);
    $this->postJson("/api/v1/teacher/leave-requests/{$leave->uuid}/approve")
        ->assertForbidden();
    $this->getJson("/api/v1/student-leave-requests/{$leave->uuid}/attachment")
        ->assertForbidden();

    Sanctum::actingAs($this->otherParent);
    $this->getJson("/api/v1/student-leave-requests/{$leave->uuid}/attachment")
        ->assertForbidden();

    // Homeroom teacher can view attachment and approve
    Sanctum::actingAs($this->homeroom);
    $this->get("/api/v1/student-leave-requests/{$leave->uuid}/attachment")
        ->assertOk();

    $this->postJson("/api/v1/teacher/leave-requests/{$leave->uuid}/approve", [
        'decision_notes' => 'Semoga lekas sembuh',
    ])->assertOk()
        ->assertJsonPath('data.status', StudentLeaveRequest::STATUS_APPROVED);

    // Only Oct 14 (today) is materialized in attendances; Oct 15 & Oct 16 (future) must NOT exist yet
    expect(Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-14')->exists())->toBeTrue()
        ->and(Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-15')->exists())->toBeFalse()
        ->and(Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-16')->exists())->toBeFalse();

    // Travel to Oct 15 and run scheduler command
    $this->travelTo(Carbon::parse('2026-10-15 06:30:00'));
    $this->artisan('student-leaves:process')->assertExitCode(0);

    expect(Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-15')->exists())->toBeTrue()
        ->and(Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-16')->exists())->toBeFalse();

    // Revoking the leave reverts synced attendance rows
    Sanctum::actingAs($this->homeroom);
    $this->postJson("/api/v1/teacher/leave-requests/{$leave->uuid}/revoke", [
        'decision_notes' => 'Siswa ternyata masuk sekolah',
    ])->assertOk()
        ->assertJsonPath('data.status', StudentLeaveRequest::STATUS_REJECTED);

    expect(Attendance::where('student_leave_request_id', $leave->id)->count())->toBe(0);
});

test('parent can cancel pending leave request and teacher cannot self-approve own child leave request', function () {
    // Make homeroom teacher also the parent of a student in their own class
    $teacherChildUser = User::factory()->create(['role' => 'student', 'name' => 'Anak Wali Kelas']);
    $teacherChild = Student::factory()->create([
        'user_id' => $teacherChildUser->id,
        'parent_id' => $this->homeroom->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '260199',
        'nisn' => '0011122299',
    ]);

    $selfLeave = StudentLeaveRequest::create([
        'student_id' => $teacherChild->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'submitted_by' => $this->homeroom->id,
        'type' => StudentLeaveRequest::TYPE_IZIN,
        'start_date' => '2026-10-14',
        'end_date' => '2026-10-14',
        'days_count' => 1,
        'reason' => 'Urusi dokumen keluarga',
        'status' => StudentLeaveRequest::STATUS_PENDING,
    ]);

    // Homeroom teacher is blocked from self-approving their own child's leave
    Sanctum::actingAs($this->homeroom);
    $this->postJson("/api/v1/teacher/leave-requests/{$selfLeave->uuid}/approve")
        ->assertForbidden();

    // Waka Kesiswaan can approve it
    Sanctum::actingAs($this->kesiswaan);
    $this->postJson("/api/v1/teacher/leave-requests/{$selfLeave->uuid}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', StudentLeaveRequest::STATUS_APPROVED);

    // Parent submits an izin request and cancels it while pending
    Sanctum::actingAs($this->parent);
    $res = $this->postJson('/api/v1/parent/leave-requests', [
        'student_id' => $this->student->id,
        'type' => 'izin',
        'start_date' => '2026-10-17',
        'end_date' => '2026-10-17',
        'reason' => 'Menghadiri wisuda kakak',
    ])->assertCreated();

    $pendingUuid = (string) $res->json('data.uuid');

    $this->postJson("/api/v1/parent/leave-requests/{$pendingUuid}/cancel")
        ->assertOk()
        ->assertJsonPath('data.status', StudentLeaveRequest::STATUS_CANCELLED);
});

test('pending leave request older than configured escalation hours is escalated to waka kesiswaan', function () {
    Setting::set('student_leave_escalation_hours', '24');

    $leave = StudentLeaveRequest::create([
        'student_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'submitted_by' => $this->parent->id,
        'type' => StudentLeaveRequest::TYPE_IZIN,
        'start_date' => '2026-10-15',
        'end_date' => '2026-10-15',
        'days_count' => 1,
        'reason' => 'Izin lomba luar kota',
        'status' => StudentLeaveRequest::STATUS_PENDING,
    ]);

    $this->travel(25)->hours();
    $this->artisan('student-leaves:process')->assertExitCode(0);

    $leave->refresh();
    expect($leave->escalated_at)->not->toBeNull()
        ->and($leave->escalated_to)->toBe($this->kesiswaan->id);
});
