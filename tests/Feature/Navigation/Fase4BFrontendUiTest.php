<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CounselingSession;
use App\Models\GraduationBatch;
use App\Models\GraduationDecision;
use App\Models\MeetingNote;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Models\User;
use App\Models\WarningLetter;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Services\WorkflowService;
use Database\Seeders\WorkflowDefinitionSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

beforeEach(function () {
    Cache::flush();
    Notification::fake();
});

test('academic_cycle navigation key has id en ar parity and appears once for authorized roles', function () {
    foreach (['id', 'en', 'ar'] as $locale) {
        $nav = require lang_path("{$locale}/navigation.php");
        expect($nav)->toHaveKey('academic_cycle')
            ->and(trim((string) $nav['academic_cycle']))->not->toBeEmpty();
    }

    $waka = User::factory()->create(['role' => 'waka_kurikulum', 'must_change_password' => false])->fresh();
    $guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false])->fresh();
    $tu = User::factory()->create(['role' => 'staf_tu', 'must_change_password' => false])->fresh();

    foreach ([$waka, $guru] as $authorizedUser) {
        $html = $this->actingAs($authorizedUser)->get('/dashboard')->assertOk()->getContent();
        $sidebarHtml = Str::between($html, 'id="sidebar-nav"', '</nav>');
        expect(substr_count($sidebarHtml, 'href="'.route('academic.cycle.index').'"'))->toBe(1);
    }

    $tuHtml = $this->actingAs($tu)->get('/dashboard')->assertOk()->getContent();
    $tuSidebar = Str::between($tuHtml, 'id="sidebar-nav"', '</nav>');
    expect(substr_count($tuSidebar, 'href="'.route('academic.cycle.index').'"'))->toBe(0);
});

test('academic cycle index renders interactive A5 graduation forms and basis metrics cleanly', function () {
    $waka = User::factory()->create(['role' => 'waka_kurikulum', 'must_change_password' => false])->fresh();
    $kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'must_change_password' => false])->fresh();

    $schoolYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_ACTIVE]);
    AcademicYear::create([
        'school_year_id' => $schoolYear->id,
        'year' => '2026/2027',
        'semester' => 'Genap',
        'is_active' => true,
        'start_date' => '2027-01-04',
        'end_date' => '2027-06-20',
    ]);

    $classroom = Classroom::factory()->create([
        'name' => 'XII-1',
        'grade_level' => 'XII',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
    ]);

    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Raka Pratama', 'must_change_password' => false]);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'classroom_id' => $classroom->id,
        'nis' => '270199',
        'status' => 'active',
    ]);

    $note = MeetingNote::create([
        'title' => 'Pleno Dewan Guru Kelulusan XII',
        'type' => 'rapat_guru',
        'meeting_date' => '2027-05-02',
        'start_time' => '09:00:00',
        'status' => 'finalized',
        'created_by' => $waka->id,
    ]);

    $batch = GraduationBatch::create([
        'school_year_id' => $schoolYear->id,
        'graduation_year' => 2027,
        'sk_number' => '421.3/SK-LULUS/099/2027',
        'meeting_note_id' => $note->id,
        'status' => GraduationBatch::STATUS_DRAFT,
        'created_by' => $waka->id,
    ]);

    $decision = GraduationDecision::create([
        'graduation_batch_id' => $batch->id,
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'decision' => GraduationDecision::DECISION_GRADUATED,
        'basis' => [
            'report_card_average' => 88.45,
            'completed_semesters' => 6,
            'below_kktp_count' => 0,
            'alpha_days' => 1,
            'thresholds_configured' => true,
        ],
        'note' => 'Memenuhi seluruh kriteria akademik',
    ]);

    $this->actingAs($waka)
        ->get(route('academic.cycle.index'))
        ->assertOk()
        ->assertSee('421.3/SK-LULUS/099/2027')
        ->assertSee('Pleno Dewan Guru Kelulusan XII')
        ->assertSee('Raka Pratama')
        ->assertSee('88.45')
        ->assertSee(route('academic.cycle.graduation-batches.store'), false)
        ->assertSee(route('academic.cycle.graduation-batches.bulk-decisions', $batch), false)
        ->assertSee(route('academic.cycle.graduation-decisions.review', $decision), false)
        ->assertSee(route('academic.cycle.graduation-batches.submit', $batch), false);

    $batch->update([
        'status' => GraduationBatch::STATUS_PENDING,
        'submitted_by' => $waka->id,
        'submitted_at' => now(),
    ]);

    $this->actingAs($kepsek)
        ->get(route('academic.cycle.index'))
        ->assertOk()
        ->assertSee(route('academic.cycle.graduation-batches.approve', $batch), false);
});

test('workflows show renders clean polymorphic cards for GraduationBatch and StudentMutation without strict lazy-load violations', function () {
    $this->seed(WorkflowDefinitionSeeder::class);

    $waka = User::factory()->create(['role' => 'waka_kurikulum', 'must_change_password' => false])->fresh();
    $tu = User::factory()->create(['role' => 'staf_tu', 'must_change_password' => false])->fresh();
    $kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'must_change_password' => false])->fresh();

    $schoolYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_ACTIVE]);
    $classroom = Classroom::factory()->create(['name' => 'XI-3', 'grade_level' => 'XI', 'school_year_id' => $schoolYear->id]);
    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Nadia Putri', 'must_change_password' => false]);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'classroom_id' => $classroom->id, 'nis' => '260888']);

    $gradBatch = GraduationBatch::create([
        'school_year_id' => $schoolYear->id,
        'graduation_year' => 2027,
        'sk_number' => '421.3/SK-LULUS/101/2027',
        'status' => GraduationBatch::STATUS_PENDING,
        'notes' => 'Mohon pengesahan SK kelulusan hasil rapat pleno.',
        'created_by' => $waka->id,
    ]);

    $mutation = StudentMutation::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'school_year_id' => $schoolYear->id,
        'type' => StudentMutation::TYPE_OUT,
        'mutation_date' => '2026-11-26',
        'reason' => 'Mengikuti perpindahan tugas orang tua ke Yogyakarta.',
        'target_school' => 'SMA Negeri 3 Yogyakarta',
        'letter_number' => '422/MUT/019/2026',
        'document_path' => 'mutations/surat-pindah.pdf',
        'status' => StudentMutation::STATUS_PENDING,
        'billing_review_status' => StudentMutation::BILLING_CLEARED,
        'requested_by' => $tu->id,
    ]);

    $workflowService = app(WorkflowService::class);
    WorkflowDefinition::firstOrCreate(
        ['code' => 'graduation_approval'],
        ['name' => 'Pengesahan SK Kelulusan Kelas XII', 'steps' => [['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48]]]
    );
    WorkflowDefinition::firstOrCreate(
        ['code' => 'mutation_approval'],
        ['name' => 'Persetujuan Mutasi Siswa', 'steps' => [['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48]]]
    );

    /** @var WorkflowRequest $gradReq */
    $gradReq = $workflowService->initiate('graduation_approval', $waka, $gradBatch, 'Pengajuan SK Kelulusan');
    /** @var WorkflowRequest $mutReq */
    $mutReq = $workflowService->initiate('mutation_approval', $tu, $mutation, 'Pengajuan Mutasi Keluar');

    $this->actingAs($kepsek)
        ->get(route('workflows.show', $gradReq))
        ->assertOk()
        ->assertSee('Batch Kelulusan Kelas XII')
        ->assertSee('421.3/SK-LULUS/101/2027')
        ->assertDontSee('Atribut Model Induk');

    $this->actingAs($kepsek)
        ->get(route('workflows.show', $mutReq))
        ->assertOk()
        ->assertSee('Mutasi Keluar Siswa')
        ->assertSee('SMA Negeri 3 Yogyakarta')
        ->assertSee('422/MUT/019/2026')
        ->assertSee(route('mutations.document', $mutation), false)
        ->assertDontSee('Atribut Model Induk');
});

test('points index and show render dynamic thresholds, manual SP form, counseling badge, and un-slopped markup', function () {
    $bk = User::factory()->create(['role' => 'bk', 'must_change_password' => false])->fresh();
    $classroom = Classroom::factory()->create(['name' => 'X-2', 'grade_level' => 'X']);
    $studentUser = User::factory()->create(['role' => 'student', 'name' => "Mu'adz Al-Farisi", 'must_change_password' => false]);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'classroom_id' => $classroom->id,
        'nis' => '260777',
    ]);

    $counseling = CounselingSession::create([
        'student_id' => $student->id,
        'counselor_id' => $bk->id,
        'requested_by' => $bk->id,
        'scheduled_at' => now()->addDay(),
        'type' => 'individual',
        'category' => 'pribadi',
        'status' => 'requested',
        'reason' => 'Pembinaan SP1',
    ]);

    $wl = WarningLetter::create([
        'student_id' => $student->id,
        'level' => 'SP1',
        'point_threshold' => 50,
        'issued_at' => now(),
        'issued_by' => $bk->id,
        'counseling_session_id' => $counseling->id,
    ]);

    $this->actingAs($bk)
        ->get(route('points.index'))
        ->assertOk()
        ->assertSee('SP1: 50')
        ->assertDontSee('rounded-[2.5rem]');

    $this->actingAs($bk)
        ->get(route('points.show', $student))
        ->assertOk()
        ->assertSee('SP1')
        ->assertSee('Konseling Wajib Terjadwal')
        ->assertSee(route('points.warning-letters.store', $student), false)
        ->assertSee(route('points.warning-letters.pdf', $wl), false)
        ->assertDontSee('rounded-[2.5rem]');
});
