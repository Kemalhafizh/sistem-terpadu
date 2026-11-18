<?php

use App\Models\AcademicCalendar;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\ElectiveSurvey;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\PromotionBatch;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\ElectiveSurveyService;
use App\Services\Academic\EnrollmentService;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;

function createWakaUser(): User
{
    $user = User::factory()->create([
        'role' => 'guru',
        'account_type' => 'guru',
        'must_change_password' => false,
    ]);
    $pos = Position::where('code', 'waka_kurikulum')->firstOrFail();
    PositionAssignment::create([
        'position_id' => $pos->id,
        'user_id' => $user->id,
        'starts_on' => now()->subDays(5)->toDateString(),
    ]);

    return $user->fresh();
}

function createKepsekUser(): User
{
    $user = User::factory()->create([
        'role' => 'kepala_sekolah',
        'account_type' => 'guru',
        'must_change_password' => false,
    ]);
    $pos = Position::where('code', 'kepala_sekolah')->firstOrFail();
    PositionAssignment::create([
        'position_id' => $pos->id,
        'user_id' => $user->id,
        'starts_on' => now()->subDays(5)->toDateString(),
    ]);

    return $user->fresh();
}

test('copied academic calendar requires Waka review before Kepala Sekolah can approve it', function () {
    $waka = createWakaUser();
    $kepsek = createKepsekUser();

    $oldYear = SchoolYear::create([
        'name' => '2025/2026',
        'status' => SchoolYear::STATUS_ACTIVE,
    ]);
    $oldCalendar = AcademicCalendar::create([
        'school_year_id' => $oldYear->id,
        'status' => AcademicCalendar::STATUS_PUBLISHED,
        'needs_review' => false,
    ]);
    $oldCalendar->events()->create([
        'title' => 'Awal Tahun Ajaran',
        'event_type' => 'academic',
        'milestone' => 'awal_ta',
        'start_date' => '2025-07-14',
        'end_date' => '2025-07-14',
        'created_by' => $waka->id,
        'is_active' => true,
    ]);

    $this->actingAs($waka)
        ->post(route('academic.cycle.school-years.store'), [
            'name' => '2026/2027',
            'start_date' => '2026-07-13',
            'end_date' => '2027-06-20',
            'copy_from_school_year_id' => $oldYear->id,
        ])
        ->assertRedirect(route('academic.cycle.index'));

    $newYear = SchoolYear::where('name', '2026/2027')->firstOrFail();
    $newCalendar = $newYear->calendar;

    expect($newCalendar)->not->toBeNull()
        ->and($newCalendar->needs_review)->toBeTrue()
        ->and($newYear->semesters()->count())->toBe(2)
        ->and($newYear->milestoneDate('awal_ta')?->toDateString())->toBe('2026-07-14');

    // Kepala Sekolah cannot approve while needs_review is true
    $this->actingAs($kepsek)
        ->post(route('academic.cycle.calendars.approve', $newCalendar))
        ->assertSessionHasErrors('calendar');

    $newCalendar->update(['needs_review' => false]);

    $this->actingAs($kepsek)
        ->post(route('academic.cycle.calendars.approve', $newCalendar))
        ->assertRedirect(route('academic.cycle.index'));

    expect($newCalendar->fresh()->status)->toBe(AcademicCalendar::STATUS_PUBLISHED)
        ->and($newCalendar->fresh()->approved_by)->toBe($kepsek->id);
});

test('EnrollmentService guarantees at most one active enrollment per student and syncs students.classroom_id', function () {
    $service = app(EnrollmentService::class);

    $year1 = SchoolYear::create(['name' => '2025/2026', 'status' => SchoolYear::STATUS_ACTIVE]);
    $year2 = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_ACTIVE]);

    $classX = Classroom::factory()->create(['name' => 'X-1', 'grade_level' => 'X', 'school_year_id' => $year1->id]);
    $classXI = Classroom::factory()->create(['name' => 'XI-1', 'grade_level' => 'XI', 'school_year_id' => $year2->id]);

    $student = Student::factory()->create(['classroom_id' => $classX->id]);

    $first = $service->enroll($student, $classX, $year1, '2025-07-14');
    $second = $service->enroll($student, $classXI, $year2, '2026-07-13');

    expect($first->fresh()->status)->toBe(ClassEnrollment::STATUS_PROMOTED)
        ->and($second->fresh()->status)->toBe(ClassEnrollment::STATUS_ACTIVE)
        ->and(ClassEnrollment::where('student_id', $student->id)->where('status', ClassEnrollment::STATUS_ACTIVE)->count())->toBe(1)
        ->and($student->fresh()->classroom_id)->toBe($classXI->id);
});

test('ElectiveSurveyService maps student choices, returns null match ratio for unsubmitted students, and supports manual Waka plotting', function () {
    $surveyService = app(ElectiveSurveyService::class);
    $enrollmentService = app(EnrollmentService::class);

    $currentYear = SchoolYear::create(['name' => '2025/2026', 'status' => SchoolYear::STATUS_ACTIVE]);
    $nextYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_DRAFT]);

    $classX1 = Classroom::factory()->create(['name' => 'X-1', 'grade_level' => 'X', 'school_year_id' => $currentYear->id]);
    $classXI3 = Classroom::factory()->create(['name' => 'XI-3', 'grade_level' => 'XI', 'school_year_id' => $nextYear->id]);

    $fisika = Subject::factory()->create(['name' => 'Fisika', 'code' => 'FIS', 'display_group' => 'B']);
    $informatika = Subject::factory()->create(['name' => 'Informatika', 'code' => 'INF', 'display_group' => 'B']);
    $ekonomi = Subject::factory()->create(['name' => 'Ekonomi', 'code' => 'EKO', 'display_group' => 'B']);

    $classXI3->electiveSubjects()->sync([$fisika->id, $informatika->id]);

    $studentA = Student::factory()->create(['classroom_id' => $classX1->id]);
    $studentB = Student::factory()->create(['classroom_id' => $classX1->id]);

    $enrollmentService->enroll($studentA, $classX1, $currentYear);
    $enrollmentService->enroll($studentB, $classX1, $currentYear);

    $survey = ElectiveSurvey::create([
        'school_year_id' => $nextYear->id,
        'title' => 'Survei Peminatan Kelas XI TA 2026/2027',
        'min_choices' => 2,
        'max_choices' => 2,
        'ranked' => false,
        'status' => ElectiveSurvey::STATUS_OPEN,
    ]);
    $survey->subjects()->sync([$fisika->id, $informatika->id, $ekonomi->id]);

    // Student A submits choices via API
    Sanctum::actingAs($studentA->user);
    $this->putJson('/api/v1/student/elective-survey/choices', [
        'survey_id' => $survey->id,
        'subject_ids' => [$fisika->id, $informatika->id],
    ])->assertOk()
        ->assertJsonCount(2, 'data.my_choices');

    $stats = $surveyService->statistics($survey);
    expect($stats['total_submitted_students'])->toBe(1)
        ->and(count($stats['unsubmitted_students']))->toBe(1)
        ->and($stats['unsubmitted_students'][0]['student_id'])->toBe($studentB->id);

    $matchA = $surveyService->classroomMatch($survey, $studentA, $classXI3);
    $matchB = $surveyService->classroomMatch($survey, $studentB, $classXI3);

    expect($matchA['ratio'])->toBe(100.0)
        ->and($matchB['ratio'])->toBeNull();

    // Waka plots both Student A and Student B manually
    $waka = createWakaUser();
    $surveyService->placeStudent($nextYear, $studentA, $classXI3, $waka, 'Sesuai minat Fisika & Informatika');
    $surveyService->placeStudent($nextYear, $studentB, $classXI3, $waka, 'Diplot oleh Waka Kurikulum');

    // Before announcement, next-classroom returns null
    Sanctum::actingAs($studentA->user);
    $this->getJson('/api/v1/student/next-classroom')
        ->assertOk()
        ->assertJsonPath('data', null);

    // Announce survey
    $this->actingAs($waka)
        ->post(route('academic.cycle.surveys.announce', $survey))
        ->assertRedirect(route('academic.cycle.index'));

    // After announcement, student and parent can see next classroom & elective subjects
    Sanctum::actingAs($studentA->user);
    $this->getJson('/api/v1/student/next-classroom')
        ->assertOk()
        ->assertJsonPath('data.classroom_name', 'XI-3')
        ->assertJsonCount(2, 'data.elective_subjects');

    $parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    $studentA->update(['parent_id' => $parent->id]);

    Sanctum::actingAs($parent);
    $this->getJson("/api/v1/parent/child/{$studentA->uuid}/elective-survey")
        ->assertOk()
        ->assertJsonPath('data.next_classroom.classroom_name', 'XI-3')
        ->assertJsonCount(2, 'data.survey.my_choices');
});

test('promotion workflow enforces Grade X placement, supports Principal hold, and executes idempotently', function () {
    $enrollmentService = app(EnrollmentService::class);
    $surveyService = app(ElectiveSurveyService::class);

    $waka = createWakaUser();
    $kepsek = createKepsekUser();
    $waliKelas = User::factory()->create(['role' => 'guru', 'account_type' => 'guru', 'must_change_password' => false]);

    $fromYear = SchoolYear::create(['name' => '2025/2026', 'status' => SchoolYear::STATUS_ACTIVE]);
    $toYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_DRAFT]);

    $classX1 = Classroom::factory()->create([
        'name' => 'X-1',
        'grade_level' => 'X',
        'school_year_id' => $fromYear->id,
        'teacher_id' => $waliKelas->id,
    ]);
    $classXI1 = Classroom::factory()->create([
        'name' => 'XI-1',
        'grade_level' => 'XI',
        'school_year_id' => $toYear->id,
    ]);

    $student1 = Student::factory()->create(['classroom_id' => $classX1->id]);
    $student2 = Student::factory()->create(['classroom_id' => $classX1->id]);

    $enrollmentService->enroll($student1, $classX1, $fromYear, '2025-07-14');
    $enrollmentService->enroll($student2, $classX1, $fromYear, '2025-07-14');

    // Only student1 is plotted to XI-1 initially
    $surveyService->placeStudent($toYear, $student1, $classXI1, $waka);

    $batch = $enrollmentService->createPromotionBatch($fromYear, $toYear, $waka);
    expect($batch->decisions()->count())->toBe(2);

    // Waka tries to decide batch while student2 (naik from X) has no target classroom -> blocked
    expect(fn () => $enrollmentService->decideBatch($batch->fresh(), $waka))
        ->toThrow(ValidationException::class);

    // Homeroom teacher reviews student2 as tinggal kelas in X-1
    $decision2 = $batch->decisions()->where('student_id', $student2->id)->firstOrFail();
    $enrollmentService->reviewByHomeroom($decision2, $waliKelas, 'tinggal', 'Perlu pengulangan di kelas X', $classX1->id);

    // Now Waka can decide the batch
    $decided = $enrollmentService->decideBatch($batch->fresh(), $waka);
    expect($decided->status)->toBe(PromotionBatch::STATUS_DECIDED);

    // Principal holds the batch with a reason -> reverts to ditinjau_wali and blocks execution
    $held = $enrollmentService->holdBatch($decided, $kepsek, 'Perlu verifikasi ulang nilai remedial siswa kedua');
    expect($held->status)->toBe(PromotionBatch::STATUS_REVIEWED)
        ->and($held->held_by)->toBe($kepsek->id);

    expect(fn () => $enrollmentService->executeBatch($held, $waka))
        ->toThrow(ValidationException::class);

    // Waka re-decides after verification and executes atomically
    $redecided = $enrollmentService->decideBatch($held->fresh(), $waka);
    $executed = $enrollmentService->executeBatch($redecided, $waka);

    expect($executed->status)->toBe(PromotionBatch::STATUS_EXECUTED)
        ->and($student1->fresh()->classroom_id)->toBe($classXI1->id)
        ->and($student2->fresh()->classroom_id)->toBe($classX1->id);

    // Calling executeBatch a second time is idempotent and does not duplicate enrollments
    $executedAgain = $enrollmentService->executeBatch($executed->fresh(), $waka);
    expect($executedAgain->status)->toBe(PromotionBatch::STATUS_EXECUTED)
        ->and(ClassEnrollment::where('student_id', $student1->id)->where('school_year_id', $toYear->id)->count())->toBe(1);
});
