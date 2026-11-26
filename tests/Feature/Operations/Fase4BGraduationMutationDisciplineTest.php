<?php

use App\Events\NewNotificationEvent;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\AlumniData;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\CounselingSession;
use App\Models\DapodikSyncItem;
use App\Models\Grade;
use App\Models\GraduationBatch;
use App\Models\GraduationDecision;
use App\Models\MeetingNote;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\ReportCard;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Models\Subject;
use App\Models\User;
use App\Models\WarningLetter;
use App\Notifications\GraduationAnnouncedNotification;
use App\Notifications\WarningLetterIssuedNotification;
use App\Services\Academic\EnrollmentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

function createFase4BWakaKurikulum(): User
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

function createFase4BKepsek(): User
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

beforeEach(function () {
    Cache::flush();
    Notification::fake();
});

test('A5 graduation batch reads milestone date from AcademicCalendar and leaves unrecorded student basis and decision as honest null', function () {
    $waka = createFase4BWakaKurikulum();

    $schoolYear = SchoolYear::create([
        'name' => '2026/2027',
        'start_date' => '2026-07-13',
        'end_date' => '2027-06-19',
        'status' => SchoolYear::STATUS_ACTIVE,
    ]);
    $semester1 = AcademicYear::create([
        'school_year_id' => $schoolYear->id,
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => false,
        'start_date' => '2026-07-13',
        'end_date' => '2026-12-19',
    ]);
    $semester2 = AcademicYear::create([
        'school_year_id' => $schoolYear->id,
        'year' => '2026/2027',
        'semester' => 'Genap',
        'is_active' => true,
        'start_date' => '2027-01-04',
        'end_date' => '2027-06-19',
    ]);

    $calendar = AcademicCalendar::create([
        'school_year_id' => $schoolYear->id,
        'status' => AcademicCalendar::STATUS_PUBLISHED,
        'needs_review' => false,
    ]);
    $calendar->events()->create([
        'title' => 'Pengumuman Kelulusan Kelas XII',
        'event_type' => 'academic',
        'milestone' => 'kelulusan',
        'academic_year_id' => $semester2->id,
        'start_date' => '2027-05-05',
        'end_date' => '2027-05-05',
        'created_by' => $waka->id,
        'is_active' => true,
    ]);

    $classXII = Classroom::factory()->create([
        'name' => 'XII-1',
        'grade_level' => 'XII',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
    ]);
    $classXI = Classroom::factory()->create([
        'name' => 'XI-1',
        'grade_level' => 'XI',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
    ]);

    $studentWithData = Student::factory()->create(['classroom_id' => $classXII->id, 'status' => 'active']);
    $studentNoData = Student::factory()->create(['classroom_id' => $classXII->id, 'status' => 'active']);
    $gradeXIStudent = Student::factory()->create(['classroom_id' => $classXI->id, 'status' => 'active']);

    $enrollmentService = app(EnrollmentService::class);
    $enrollmentService->enroll($studentWithData, $classXII, $schoolYear, '2026-07-13');
    $enrollmentService->enroll($studentNoData, $classXII, $schoolYear, '2026-07-13');
    $enrollmentService->enroll($gradeXIStudent, $classXI, $schoolYear, '2026-07-13');

    $math = Subject::factory()->create(['name' => 'Matematika', 'code' => 'MTK', 'kkm' => 75]);
    $physics = Subject::factory()->create(['name' => 'Fisika', 'code' => 'FIS', 'kkm' => 75]);

    Grade::create([
        'student_id' => $studentWithData->id,
        'subject_id' => $math->id,
        'academic_year_id' => $semester1->id,
        'type' => 'pas',
        'score' => 88,
        'recorded_by' => $waka->id,
    ]);
    Grade::create([
        'student_id' => $studentWithData->id,
        'subject_id' => $physics->id,
        'academic_year_id' => $semester2->id,
        'type' => 'pas',
        'score' => 70, // below KKM 75
        'recorded_by' => $waka->id,
    ]);
    ReportCard::create([
        'student_id' => $studentWithData->id,
        'academic_year_id' => $semester1->id,
        'classroom_id' => $classXII->id,
        'total_score' => 88,
        'average_score' => 88,
    ]);
    ReportCard::create([
        'student_id' => $studentWithData->id,
        'academic_year_id' => $semester2->id,
        'classroom_id' => $classXII->id,
        'total_score' => 78,
        'average_score' => 78,
    ]);
    Attendance::create([
        'student_id' => $studentWithData->id,
        'classroom_id' => $classXII->id,
        'academic_year_id' => $semester2->id,
        'date' => '2027-02-10',
        'status' => 'A',
        'recorded_by' => $waka->id,
    ]);

    $this->actingAs($waka)
        ->post(route('academic.cycle.graduation-batches.store'), [
            'school_year_id' => $schoolYear->id,
        ])
        ->assertRedirect(route('academic.cycle.index'));

    $batch = GraduationBatch::where('school_year_id', $schoolYear->id)->firstOrFail();
    expect($batch->graduation_year)->toBe(2027)
        ->and($batch->graduation_date?->toDateString())->toBe('2027-05-05')
        ->and($batch->status)->toBe(GraduationBatch::STATUS_DRAFT)
        ->and($batch->decisions()->count())->toBe(2); // Grade XI student excluded

    $withDataDecision = $batch->decisions()->where('student_id', $studentWithData->id)->firstOrFail();
    $noDataDecision = $batch->decisions()->where('student_id', $studentNoData->id)->firstOrFail();

    expect($withDataDecision->decision)->toBeNull()
        ->and((float) $withDataDecision->basis['report_card_average'])->toBe(83.0)
        ->and($withDataDecision->basis['completed_semesters'])->toBe(2)
        ->and($withDataDecision->basis['below_kktp_count'])->toBe(1)
        ->and($withDataDecision->basis['alpha_days'])->toBe(1);

    expect($noDataDecision->decision)->toBeNull()
        ->and($noDataDecision->basis['report_card_average'])->toBeNull()
        ->and($noDataDecision->basis['completed_semesters'])->toBe(0)
        ->and($noDataDecision->basis['below_kktp_count'])->toBeNull()
        ->and($noDataDecision->basis['alpha_days'])->toBeNull();
});

test('A5 graduation enforces finalized plenary meeting note, integrates with Unified Approval Inbox, prevents self-approval, supports revision, and executes to Alumni/Buku Induk/Tracer/Dapodik', function () {
    $waka = createFase4BWakaKurikulum();
    $kepsek = createFase4BKepsek();
    $waliXII1 = User::factory()->create(['role' => 'guru', 'account_type' => 'guru', 'must_change_password' => false]);
    $otherTeacher = User::factory()->create(['role' => 'teacher', 'account_type' => 'guru', 'must_change_password' => false]);
    $tu = User::factory()->create(['role' => 'staf_tu', 'account_type' => 'karyawan', 'must_change_password' => false]);

    $schoolYear = SchoolYear::create([
        'name' => '2026/2027',
        'start_date' => '2026-07-13',
        'end_date' => '2027-06-19',
        'status' => SchoolYear::STATUS_ACTIVE,
    ]);
    AcademicYear::create([
        'school_year_id' => $schoolYear->id,
        'year' => '2026/2027',
        'semester' => 'Genap',
        'is_active' => true,
        'start_date' => '2027-01-04',
        'end_date' => '2027-06-19',
    ]);

    $classXII1 = Classroom::factory()->create([
        'name' => 'XII-1',
        'grade_level' => 'XII',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
        'teacher_id' => $waliXII1->id,
    ]);

    $parentA = User::factory()->create(['role' => 'parent', 'phone_number' => '081234560001', 'must_change_password' => false]);
    $studentUserA = User::factory()->create(['role' => 'student', 'name' => 'Rizky Pratama', 'email' => 'rizky@sulaone.test', 'must_change_password' => false]);
    $studentA = Student::factory()->create([
        'user_id' => $studentUserA->id,
        'parent_id' => $parentA->id,
        'classroom_id' => $classXII1->id,
        'nis' => '241001',
        'nisn' => '0088112233',
        'status' => 'active',
    ]);

    $parentB = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    $studentUserB = User::factory()->create(['role' => 'student', 'name' => 'Dimas Aditya', 'must_change_password' => false]);
    $studentB = Student::factory()->create([
        'user_id' => $studentUserB->id,
        'parent_id' => $parentB->id,
        'classroom_id' => $classXII1->id,
        'nis' => '241002',
        'nisn' => '0088112244',
        'status' => 'active',
    ]);

    app(EnrollmentService::class)->enroll($studentA, $classXII1, $schoolYear, '2026-07-13');
    app(EnrollmentService::class)->enroll($studentB, $classXII1, $schoolYear, '2026-07-13');

    // Unauthorized roles blocked from creating graduation batch
    $this->actingAs($tu)
        ->post(route('academic.cycle.graduation-batches.store'), ['school_year_id' => $schoolYear->id])
        ->assertForbidden();
    $this->actingAs($studentUserA)
        ->post(route('academic.cycle.graduation-batches.store'), ['school_year_id' => $schoolYear->id])
        ->assertForbidden();

    // Waka creates draft batch
    $this->actingAs($waka)
        ->post(route('academic.cycle.graduation-batches.store'), [
            'school_year_id' => $schoolYear->id,
            'graduation_date' => '2027-05-03',
        ])
        ->assertRedirect(route('academic.cycle.index'));

    $batch = GraduationBatch::where('school_year_id', $schoolYear->id)->firstOrFail();
    $decisionA = $batch->decisions()->where('student_id', $studentA->id)->firstOrFail();
    $decisionB = $batch->decisions()->where('student_id', $studentB->id)->firstOrFail();

    // Non-homeroom teacher cannot review XII-1 student decision
    $this->actingAs($otherTeacher)
        ->post(route('academic.cycle.graduation-decisions.review', $decisionA), [
            'decision' => GraduationDecision::DECISION_GRADUATED,
        ])
        ->assertForbidden();

    // Homeroom teacher reviews studentA -> lulus
    $this->actingAs($waliXII1)
        ->post(route('academic.cycle.graduation-decisions.review', $decisionA), [
            'decision' => GraduationDecision::DECISION_GRADUATED,
            'note' => 'Tuntas seluruh kompetensi dan akhlak baik.',
        ])
        ->assertRedirect(route('academic.cycle.index'));

    // Submitting while studentB is still undecided fails validation
    $draftMeeting = MeetingNote::create([
        'title' => 'Rapat Pleno Kelulusan Kelas XII TA 2026/2027',
        'type' => 'rapat_guru',
        'meeting_date' => '2027-05-02',
        'start_time' => '09:00:00',
        'created_by' => $waka->id,
        'status' => 'draft',
    ]);

    $this->actingAs($waka)
        ->post(route('academic.cycle.graduation-batches.submit', $batch), [
            'meeting_note_id' => $draftMeeting->id,
        ])
        ->assertSessionHasErrors('decisions');

    // Waka bulk-updates decisions (studentA = lulus, studentB = tidak_lulus)
    $this->actingAs($waka)
        ->post(route('academic.cycle.graduation-batches.bulk-decisions', $batch), [
            'decisions' => [
                $studentA->id => ['student_id' => $studentA->id, 'decision' => 'lulus'],
                $studentB->id => ['student_id' => $studentB->id, 'decision' => 'tidak_lulus', 'note' => 'Belum menyelesaikan ujian susulan'],
            ],
        ])
        ->assertRedirect(route('academic.cycle.index'));

    // Submitting with non-finalized meeting note fails validation
    $this->actingAs($waka)
        ->post(route('academic.cycle.graduation-batches.submit', $batch), [
            'meeting_note_id' => $draftMeeting->id,
        ])
        ->assertSessionHasErrors('meeting_note_id');

    // Finalize meeting note (J8) and submit
    $draftMeeting->update(['status' => 'finalized']);

    $this->actingAs($waka)
        ->post(route('academic.cycle.graduation-batches.submit', $batch), [
            'meeting_note_id' => $draftMeeting->id,
            'sk_number' => '421.3/SK-LULUS/088/2027',
            'sk_date' => '2027-05-03',
            'notes' => 'Mohon pengesahan SK kelulusan angkatan 2027',
        ])
        ->assertRedirect(route('academic.cycle.index'));

    expect($batch->fresh()->status)->toBe(GraduationBatch::STATUS_PENDING);
    $workflowRequest = $batch->workflowRequests()->latest('id')->firstOrFail();

    // Appears in Kepala Sekolah's Unified Approval Inbox (Web & API)
    $this->actingAs($kepsek)
        ->get(route('workflows.pending'))
        ->assertOk()
        ->assertSee('Pengesahan SK Kelulusan Kelas XII')
        ->assertSee('1 lulus, 1 belum lulus');

    Sanctum::actingAs($kepsek);
    $inboxApi = $this->getJson('/api/v1/approvals/pending?module=workflow')->assertOk();
    expect(collect($inboxApi->json('data.items'))->pluck('subtitle')->implode(' | '))
        ->toContain('TA 2026/2027')
        ->toContain('421.3/SK-LULUS/088/2027');

    // Cannot execute before Kepala Sekolah approves
    $this->actingAs($waka)
        ->post(route('academic.cycle.graduation-batches.execute', $batch))
        ->assertSessionHasErrors('status');

    // Self-approval guard: even if Waka were temporarily assigned kepala_sekolah, submitter cannot approve own batch
    $kepsekPos = Position::where('code', 'kepala_sekolah')->firstOrFail();
    $tempAssignment = PositionAssignment::create([
        'position_id' => $kepsekPos->id,
        'user_id' => $waka->id,
        'starts_on' => now()->subDay()->toDateString(),
        'is_acting' => true,
    ]);
    $this->actingAs($waka->fresh())
        ->post(route('academic.cycle.graduation-batches.approve', $batch), [
            'sk_number' => '421.3/SK-LULUS/088/2027',
        ])
        ->assertForbidden();
    $tempAssignment->delete();

    // Revision & Resubmit cycle via WorkflowController
    $this->actingAs($kepsek)
        ->post(route('workflows.revise', $workflowRequest), [
            'remarks' => 'Lampirkan nomor agenda rapat pleno pada catatan pengajuan.',
        ]);
    expect($batch->fresh()->status)->toBe(GraduationBatch::STATUS_REVISION);

    $this->actingAs($waka->fresh())
        ->post(route('academic.cycle.graduation-batches.submit', $batch->fresh()), [
            'notes' => 'Nomor agenda pleno 014/PLENO/2027 telah dilampirkan.',
        ])
        ->assertRedirect(route('academic.cycle.index'));
    expect($batch->fresh()->status)->toBe(GraduationBatch::STATUS_PENDING);

    // Kepala Sekolah approves SK Kelulusan
    $this->actingAs($kepsek)
        ->post(route('academic.cycle.graduation-batches.approve', $batch->fresh()), [
            'sk_number' => '421.3/SK-LULUS/088/2027',
            'sk_date' => '2027-05-03',
            'remarks' => 'Disahkan sesuai keputusan rapat pleno dewan guru.',
        ])
        ->assertRedirect(route('academic.cycle.index'));

    expect($batch->fresh()->status)->toBe(GraduationBatch::STATUS_APPROVED)
        ->and($batch->fresh()->approved_by)->toBe($kepsek->id)
        ->and($workflowRequest->fresh()->status)->toBe('approved');

    // Execute & Announce cohort graduation
    $this->actingAs($waka->fresh())
        ->post(route('academic.cycle.graduation-batches.execute', $batch->fresh()))
        ->assertRedirect(route('academic.cycle.index'));

    $executed = $batch->fresh();
    expect($executed->status)->toBe(GraduationBatch::STATUS_EXECUTED)
        ->and($executed->announced_at)->not->toBeNull()
        ->and($studentA->fresh()->status)->toBe('alumni')
        ->and($studentB->fresh()->status)->toBe('active');

    // Enrollment of studentA closed as `lulus`, while studentB stays `aktif`
    $enrollmentA = ClassEnrollment::where('student_id', $studentA->id)->firstOrFail();
    $enrollmentB = ClassEnrollment::where('student_id', $studentB->id)->firstOrFail();
    expect($enrollmentA->status)->toBe(ClassEnrollment::STATUS_GRADUATED)
        ->and($enrollmentA->ended_on?->toDateString())->toBe('2027-05-03')
        ->and($enrollmentA->ended_reason)->toContain('421.3/SK-LULUS/088/2027')
        ->and($enrollmentB->status)->toBe(ClassEnrollment::STATUS_ACTIVE);

    // AlumniData created with SK number & DapodikSyncItem queued
    $alumniA = AlumniData::where('student_id', $studentA->id)->firstOrFail();
    expect($alumniA->graduation_year)->toBe(2027)
        ->and($alumniA->sk_number)->toBe('421.3/SK-LULUS/088/2027')
        ->and(AlumniData::where('student_id', $studentB->id)->exists())->toBeFalse()
        ->and(DapodikSyncItem::where('entity_type', 'student')->where('entity_id', $studentA->id)->exists())->toBeTrue();

    // Notifications dispatched to both students and parents
    Notification::assertSentTo($studentUserA, GraduationAnnouncedNotification::class);
    Notification::assertSentTo($parentA, GraduationAnnouncedNotification::class);
    Notification::assertSentTo($studentUserB, GraduationAnnouncedNotification::class);
    Notification::assertSentTo($parentB, GraduationAnnouncedNotification::class);

    // K2 Buku Induk displays SK Kelulusan & K3 Tracer Study is immediately accessible to graduated alumnus
    $this->actingAs($studentUserA)
        ->get(route('students.ledger', $studentA))
        ->assertOk()
        ->assertSee('421.3/SK-LULUS/088/2027');

    $this->actingAs($studentUserA)
        ->post(route('alumni.tracer-study.submit'), [
            'current_activity' => 'study',
            'institution_name' => 'Universitas Diponegoro',
            'study_major' => 'Informatika',
            'entry_year' => 2027,
            'relevance_score' => 5,
            'satisfaction_score' => 5,
        ])
        ->assertRedirect(route('alumni.tracer-study.form'));

    // Idempotent second execution call & cycle index view renders cleanly
    $this->actingAs($waka->fresh())
        ->post(route('academic.cycle.graduation-batches.execute', $executed))
        ->assertRedirect(route('academic.cycle.index'));
    expect(AlumniData::where('student_id', $studentA->id)->count())->toBe(1);

    $this->actingAs($waka->fresh())
        ->get(route('academic.cycle.index'))
        ->assertOk()
        ->assertSee('421.3/SK-LULUS/088/2027')
        ->assertSee('Rapat Pleno Kelulusan Kelas XII TA 2026/2027');
});

test('A6 outward mutation requires Principal approval, closes enrollment as mutasi_keluar, reviews ongoing billings, and deactivates student and eligible parent accounts', function () {
    Storage::fake('local');
    Event::fake([NewNotificationEvent::class]);

    $tu = User::factory()->create(['role' => 'staf_tu', 'account_type' => 'karyawan', 'must_change_password' => false]);
    $bendahara = User::factory()->create(['role' => 'bendahara', 'account_type' => 'karyawan', 'must_change_password' => false]);
    $wakaKesiswaan = User::factory()->create(['role' => 'waka_kesiswaan', 'account_type' => 'guru', 'must_change_password' => false]);
    $kepsek = createFase4BKepsek();

    $schoolYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_ACTIVE]);
    $semester = AcademicYear::create([
        'school_year_id' => $schoolYear->id,
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-13',
        'end_date' => '2026-12-19',
    ]);
    $classroom = Classroom::factory()->create([
        'name' => 'XI-2',
        'grade_level' => 'XI',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
    ]);

    $parent = User::factory()->create([
        'role' => 'parent',
        'account_type' => 'orang_tua',
        'phone_number' => '081299998888',
        'account_status' => 'aktif',
        'must_change_password' => false,
    ]);
    $studentUser = User::factory()->create([
        'role' => 'student',
        'account_type' => 'siswa',
        'name' => 'Fajar Nugroho',
        'account_status' => 'aktif',
        'must_change_password' => false,
    ]);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
        'nis' => '252010',
        'status' => 'active',
    ]);

    app(EnrollmentService::class)->enroll($student, $classroom, $schoolYear, '2026-07-13');

    // Create 1 partial current billing + 1 future unpaid billing (0 payments)
    $sppCategory = PaymentCategory::create([
        'name' => 'SPP Bulanan',
        'default_amount' => 500000,
        'is_monthly' => true,
    ]);
    $currentBilling = Billing::create([
        'student_id' => $student->id,
        'payment_category_id' => $sppCategory->id,
        'academic_year_id' => $semester->id,
        'month' => 'Oktober',
        'amount' => 500000,
        'remaining_balance' => 200000,
        'due_date' => '2026-10-10',
        'status' => 'partial',
    ]);
    Payment::create([
        'billing_id' => $currentBilling->id,
        'amount_paid' => 300000,
        'payment_date' => '2026-10-05',
        'payment_method' => 'cash',
        'receipt_number' => 'INV-20261005-0001',
        'created_by' => $tu->id,
    ]);
    $futureBilling = Billing::create([
        'student_id' => $student->id,
        'payment_category_id' => $sppCategory->id,
        'academic_year_id' => $semester->id,
        'month' => 'Desember',
        'amount' => 500000,
        'remaining_balance' => 500000,
        'due_date' => '2026-12-10',
        'status' => 'unpaid',
    ]);

    // Issue Sanctum token for student before mutation to verify revocation upon approval
    $studentToken = $studentUser->createToken('android')->plainTextToken;
    expect($studentUser->tokens()->count())->toBe(1);

    // 1. Staf TU submits outward mutation
    $this->actingAs($tu)
        ->post(route('mutations.store'), [
            'type' => 'out',
            'student_id' => $student->id,
            'mutation_date' => '2026-10-25',
            'reason' => 'Ikut pindah tugas dinas orang tua ke Surabaya',
            'target_school' => 'SMA Negeri 5 Surabaya',
            'letter_number' => '421.3/MUT-OUT/012/2026',
            'document' => UploadedFile::fake()->create('permohonan-pindah.pdf', 120, 'application/pdf'),
        ])
        ->assertRedirect(route('mutations.index'));

    $mutation = StudentMutation::where('student_id', $student->id)->firstOrFail();

    // While pending: student is STILL active, enrollment is STILL aktif, and pending mutation is NOT in Buku Induk!
    expect($mutation->status)->toBe(StudentMutation::STATUS_PENDING)
        ->and($mutation->billing_review_status)->toBe(StudentMutation::BILLING_NEEDS_SETTLEMENT)
        ->and((float) $mutation->billing_review_summary['total_outstanding_amount'])->toBe(700000.0)
        ->and($mutation->billing_review_summary['future_unpaid_count'])->toBe(1)
        ->and($student->fresh()->status)->toBe('active')
        ->and($studentUser->fresh()->account_status)->toBe('aktif');

    Event::assertDispatched(NewNotificationEvent::class, function (NewNotificationEvent $e) use ($wakaKesiswaan) {
        return $e->user->id === $wakaKesiswaan->id && $e->notification['type'] === 'student_mutation_submitted';
    });

    $this->actingAs($tu)
        ->get(route('students.ledger', $student))
        ->assertOk()
        ->assertDontSee('SMA Negeri 5 Surabaya');

    // Non-principal cannot approve
    $this->actingAs($tu)->post(route('mutations.approve', $mutation))->assertForbidden();
    $this->actingAs($wakaKesiswaan)->post(route('mutations.approve', $mutation))->assertForbidden();

    // Appears in Kepala Sekolah's Unified Approval Inbox & can be approved directly inline!
    $workflowRequest = $mutation->workflowRequests()->firstOrFail();
    $this->actingAs($kepsek)
        ->get(route('workflows.pending'))
        ->assertOk()
        ->assertSee('Persetujuan Mutasi Siswa')
        ->assertSee('Fajar Nugroho')
        ->assertSee('SMA Negeri 5 Surabaya');

    $this->actingAs($kepsek)
        ->post(route('workflows.approve', $workflowRequest), [
            'remarks' => 'Disetujui pindah sekolah.',
        ])
        ->assertRedirect(route('workflows.pending'));

    // 2. Verify atomic effects after Principal approval
    $mutation->refresh();
    $enrollment = ClassEnrollment::where('student_id', $student->id)->firstOrFail();

    expect($mutation->status)->toBe(StudentMutation::STATUS_APPROVED)
        ->and($mutation->approved_by)->toBe($kepsek->id)
        ->and($mutation->executed_at)->not->toBeNull()
        ->and($enrollment->status)->toBe(ClassEnrollment::STATUS_MUTATED_OUT)
        ->and($enrollment->ended_on?->toDateString())->toBe('2026-10-25')
        ->and($student->fresh()->status)->toBe('mutated')
        ->and($studentUser->fresh()->account_status)->toBe('nonaktif')
        ->and($studentUser->tokens()->count())->toBe(0)
        // Parent stays active while 200k partial + 500k future unpaid billings are unsettled!
        ->and($parent->fresh()->account_status)->toBe('aktif')
        ->and(DapodikSyncItem::where('entity_type', 'student')->where('entity_id', $student->id)->exists())->toBeTrue();

    // Student account is blocked by EnsureActiveAccount
    Sanctum::actingAs($studentUser->fresh());
    $this->getJson('/api/v1/discipline/summary')
        ->assertForbidden()
        ->assertJsonPath('code', 'ACCOUNT_INACTIVE');

    // Approved mutation now appears in Buku Induk
    $this->actingAs($tu)
        ->get(route('students.ledger', $student))
        ->assertOk()
        ->assertSee('SMA Negeri 5 Surabaya');

    // 3. Bendahara cancels future unpaid billing & settles remaining partial billing -> parent account auto-deactivates!
    $this->actingAs($bendahara)
        ->post(route('mutations.review-billings', $mutation), [
            'cancel_future_unpaid' => true,
            'notes' => 'SPP Desember dibatalkan karena pindah Oktober.',
        ])
        ->assertRedirect(route('mutations.index'));

    expect(Billing::find($futureBilling->id))->toBeNull()
        ->and($mutation->fresh()->billing_review_status)->toBe(StudentMutation::BILLING_NEEDS_SETTLEMENT) // 200k October partial still unpaid
        ->and($parent->fresh()->account_status)->toBe('aktif');

    // Settle remaining 200k on October billing and re-review
    Payment::create([
        'billing_id' => $currentBilling->id,
        'amount_paid' => 200000,
        'payment_date' => '2026-10-26',
        'payment_method' => 'cash',
        'receipt_number' => 'INV-20261026-0002',
        'created_by' => $bendahara->id,
    ]);
    $currentBilling->updateStatus();

    $this->actingAs($bendahara)
        ->post(route('mutations.review-billings', $mutation->fresh()), [
            'notes' => 'Seluruh kewajiban SPP sampai Oktober telah lunas.',
        ])
        ->assertRedirect(route('mutations.index'));

    expect($mutation->fresh()->billing_review_status)->toBe(StudentMutation::BILLING_CLEARED)
        ->and($parent->fresh()->account_status)->toBe('nonaktif')
        ->and($parent->fresh()->deactivated_reason)->toBe('semua_anak_keluar');
});

test('A6 inward mutation provisions new transfer student and parent accounts, enrolls into target classroom on Principal approval, and handles rejection cleanly', function () {
    $tu = User::factory()->create(['role' => 'staf_tu', 'account_type' => 'karyawan', 'must_change_password' => false]);
    $kepsek = createFase4BKepsek();

    $schoolYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_ACTIVE]);
    AcademicYear::create([
        'school_year_id' => $schoolYear->id,
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-13',
        'end_date' => '2026-12-19',
    ]);
    $targetClassroom = Classroom::factory()->create([
        'name' => 'X-4',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
    ]);

    // 1. Submit transfer-in candidate
    $this->actingAs($tu)
        ->post(route('mutations.store'), [
            'type' => 'in',
            'classroom_id' => $targetClassroom->id,
            'school_year_id' => $schoolYear->id,
            'mutation_date' => '2026-09-01',
            'reason' => 'Pindah mengikuti domisili keluarga ke Semarang',
            'target_school' => 'SMA Negeri 2 Kudus',
            'letter_number' => '421.3/MUT-IN/004/2026',
            'student_name' => 'Alya Rahmawati',
            'nis' => '261990',
            'nisn' => '0099776655',
            'gender' => 'P',
            'mother_name' => 'Ibu Rahmawati',
            'parent_name' => 'Bapak Rahmat',
            'parent_phone' => '081377776666',
        ])
        ->assertRedirect(route('mutations.index'));

    // Before approval: Student does not exist yet
    expect(Student::where('nis', '261990')->exists())->toBeFalse();
    $mutationIn = StudentMutation::where('letter_number', '421.3/MUT-IN/004/2026')->firstOrFail();
    expect($mutationIn->status)->toBe(StudentMutation::STATUS_PENDING);

    // 2. Principal approves transfer-in
    $this->actingAs($kepsek)
        ->post(route('mutations.approve', $mutationIn), [
            'remarks' => 'Diterima di rombel X-4.',
        ])
        ->assertRedirect(route('mutations.index'));

    $createdStudent = Student::where('nis', '261990')->firstOrFail();
    $enrollment = ClassEnrollment::where('student_id', $createdStudent->id)->firstOrFail();

    expect($mutationIn->fresh()->status)->toBe(StudentMutation::STATUS_APPROVED)
        ->and($mutationIn->fresh()->student_id)->toBe($createdStudent->id)
        ->and($createdStudent->status)->toBe('active')
        ->and($createdStudent->classroom_id)->toBe($targetClassroom->id)
        ->and($createdStudent->user->name)->toBe('Alya Rahmawati')
        ->and($createdStudent->user->must_change_password)->toBeTrue()
        ->and($createdStudent->parent)->not->toBeNull()
        ->and($enrollment->status)->toBe(ClassEnrollment::STATUS_ACTIVE)
        ->and($enrollment->classroom_id)->toBe($targetClassroom->id)
        ->and($enrollment->school_year_id)->toBe($schoolYear->id)
        ->and($enrollment->started_on?->toDateString())->toBe('2026-09-01');

    // 3. Rejected transfer-in does not create student or enrollment
    $this->actingAs($tu)
        ->post(route('mutations.store'), [
            'type' => 'in',
            'classroom_id' => $targetClassroom->id,
            'mutation_date' => '2026-09-10',
            'reason' => 'Berkas belum lengkap',
            'target_school' => 'SMA Luar Kota',
            'student_name' => 'Calon Ditolak',
            'nis' => '261999',
        ])
        ->assertRedirect(route('mutations.index'));

    $rejectedMutation = StudentMutation::where('reason', 'Berkas belum lengkap')->firstOrFail();
    $this->actingAs($kepsek)
        ->post(route('mutations.reject', $rejectedMutation), [
            'reason' => 'Daya tampung rombel X-4 sudah penuh dan surat pindah asli belum dilampirkan.',
        ])
        ->assertRedirect(route('mutations.index'));

    expect($rejectedMutation->fresh()->status)->toBe(StudentMutation::STATUS_REJECTED)
        ->and(Student::where('nis', '261999')->exists())->toBeFalse();

    // 4. Mutations index & create views render both approved and rejected records cleanly
    $this->actingAs($tu)
        ->get(route('mutations.index'))
        ->assertOk()
        ->assertSee('Alya Rahmawati')
        ->assertSee('Calon Ditolak');

    $this->actingAs($tu)
        ->get(route('mutations.create'))
        ->assertOk()
        ->assertSee('X-4');
});

test('E1 dynamic SP thresholds and stages are governed by school settings, trigger linked mandatory BK counseling and parent/staff notifications, and handle sign-safe rewards', function () {
    $wakaKesiswaan = User::factory()->create(['role' => 'waka_kesiswaan', 'account_type' => 'guru', 'must_change_password' => false]);
    $bk = User::factory()->create(['role' => 'bk', 'account_type' => 'guru', 'must_change_password' => false]);
    $canonicalTeacher = User::factory()->create(['role' => 'teacher', 'account_type' => 'guru', 'must_change_password' => false]);
    $waliKelas = User::factory()->create(['role' => 'guru', 'account_type' => 'guru', 'must_change_password' => false]);
    $parent = User::factory()->create(['role' => 'parent', 'phone_number' => '081255554444', 'must_change_password' => false]);
    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Bagas Saputra', 'must_change_password' => false]);

    $classroom = Classroom::factory()->create([
        'name' => 'X-2',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'teacher_id' => $waliKelas->id,
    ]);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
        'nis' => '260205',
        'status' => 'active',
    ]);

    // 1. Regular teacher and student cannot alter school-wide SP thresholds
    $this->actingAs($canonicalTeacher)
        ->put(route('points.thresholds.update'), [
            'attention_threshold' => 15,
            'sp1_threshold' => 30,
            'sp2_threshold' => 60,
            'sp3_threshold' => 90,
        ])
        ->assertForbidden();

    // Invalid non-increasing thresholds fail validation
    $this->actingAs($wakaKesiswaan)
        ->put(route('points.thresholds.update'), [
            'attention_threshold' => 40,
            'sp1_threshold' => 30,
            'sp2_threshold' => 60,
            'sp3_threshold' => 50,
        ])
        ->assertSessionHasErrors(['sp1_threshold', 'sp3_threshold']);

    // 2. Waka Kesiswaan configures custom school thresholds (15 / 30 / 60 / 90, counseling at 25)
    $this->actingAs($wakaKesiswaan)
        ->put(route('points.thresholds.update'), [
            'attention_threshold' => 15,
            'sp1_threshold' => 30,
            'sp2_threshold' => 60,
            'sp3_threshold' => 90,
            'mandatory_counseling_threshold' => 25,
        ])
        ->assertRedirect();

    expect(WarningLetter::thresholds())->toBe(['SP1' => 30, 'SP2' => 60, 'SP3' => 90])
        ->and(WarningLetter::attentionThreshold())->toBe(15)
        ->and(WarningLetter::mandatoryCounselingThreshold())->toBe(25);

    // 3. Canonical `teacher` alias records +25 penalty -> triggers mandatory counseling (>= 25), status Perlu Perhatian (>= 15, < 30), no SP yet
    $this->actingAs($canonicalTeacher)
        ->post(route('points.store'), [
            'student_id' => $student->id,
            'points' => 25,
            'type' => 'penalty',
            'description' => 'Meninggalkan kelas tanpa izin guru piket',
        ])
        ->assertRedirect();

    $session = CounselingSession::where('student_id', $student->id)->first();
    expect($session)->not->toBeNull()
        ->and($session->counselor_id)->toBe($bk->id)
        ->and(WarningLetter::where('student_id', $student->id)->count())->toBe(0);

    // 4. BK records +10 penalty -> total 35 (>= custom SP1 30) -> auto-generates SP1 linked to mandatory CounselingSession & notifies parent/homeroom/kesiswaan
    $this->actingAs($bk)
        ->post(route('points.store'), [
            'student_id' => $student->id,
            'points' => 10,
            'type' => 'penalty',
            'description' => 'Terlambat upacara bendera',
        ])
        ->assertRedirect();

    $sp1 = WarningLetter::where('student_id', $student->id)->where('level', 'SP1')->firstOrFail();
    expect($sp1->point_threshold)->toBe(30)
        ->and($sp1->counseling_session_id)->toBe($session->id)
        ->and($sp1->parent_notified_at)->not->toBeNull();

    Notification::assertSentTo($parent, WarningLetterIssuedNotification::class);
    Notification::assertSentTo($waliKelas, WarningLetterIssuedNotification::class);
    Notification::assertSentTo($wakaKesiswaan, WarningLetterIssuedNotification::class);

    // Mobile API summary reflects custom thresholds & next warning level SP2 (60)
    Sanctum::actingAs($studentUser);
    $summary = $this->getJson('/api/v1/discipline/summary')->assertOk()->json('data');
    expect($summary['total_points'])->toBe(35)
        ->and($summary['status'])->toBe('SP1 (Waspada)')
        ->and($summary['next_warning_level'])->toBe('SP2')
        ->and($summary['next_warning_threshold'])->toBe(60)
        ->and($summary['thresholds'])->toBe(['SP1' => 30, 'SP2' => 60, 'SP3' => 90]);

    // 5. Sign-safe manual reward (+10 input with type=reward reduces net balance from 35 -> 25, never 45!)
    $this->actingAs($canonicalTeacher)
        ->post(route('points.store'), [
            'student_id' => $student->id,
            'points' => 10,
            'type' => 'reward',
            'description' => 'Juara 2 Lomba Pidato Tingkat Kota',
        ])
        ->assertRedirect();

    expect($student->fresh()->total_points)->toBe(25);

    // 6. Web UI views (points index & detail with multiple logs + SP1 + counseling link) render cleanly
    $this->actingAs($wakaKesiswaan)
        ->get(route('points.index'))
        ->assertOk()
        ->assertSee('SP1: 30');

    $this->actingAs($bk)
        ->get(route('points.show', $student))
        ->assertOk()
        ->assertSee('SP1')
        ->assertSee('Konseling Wajib Terjadwal');

    // 7. Official SP PDF export streams valid PDF for authorized staff & blocks student web access
    $pdfResp = $this->actingAs($bk)
        ->get(route('points.warning-letters.pdf', $sp1))
        ->assertOk();
    expect(str_starts_with($pdfResp->getContent(), '%PDF-'))->toBeTrue();

    $this->actingAs($studentUser)
        ->get(route('points.warning-letters.pdf', $sp1))
        ->assertForbidden();
});
