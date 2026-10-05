<?php

use App\Models\AcademicYear;
use App\Models\ApprovalChain;
use App\Models\ApprovalRequest;
use App\Models\BullyingReport;
use App\Models\CatSession;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\IepDocument;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use App\Models\WasteLog;
use App\Models\WhiteboardSession;
use App\Models\WorkflowBlueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
 * FASE 118: the FASE 45-62 modules were guarded by `auth` alone, so any
 * signed-in account (students and parents included) could open and change
 * treasury, crisis, HR and data-engineering pages; records were looked up by
 * id without checking whose they were; and some forms answered "saved"
 * without saving.
 */

function bullyingReport(): BullyingReport
{
    return BullyingReport::create([
        'ticket_number' => (string) Str::uuid(), 'description' => 'Diejek di kantin', 'type' => 'verbal',
        'severity' => 'ringan', 'status' => 'reported',
    ]);
}

function employeeFor(User $user): Employee
{
    return Employee::create(['user_id' => $user->id, 'employee_number' => 'NIY.'.$user->id, 'position' => 'Staf', 'employment_status' => 'gty']);
}

// 118.5.2: students and parents are refused the internal modules.
dataset('internal pages', [
    'treasury index' => ['get', 'treasury.accounts.index', []],
    'treasury store' => ['post', 'treasury.accounts.store', []],
    'petty cash store' => ['post', 'treasury.petty-cash.store', []],
    'bullying list' => ['get', 'crisis.bullying.index', []],
    'bullying statistics' => ['get', 'crisis.bullying.statistics', []],
    'recruitment kanban' => ['get', 'recruitment.kanban', []],
    'recruitment vacancies' => ['get', 'recruitment.vacancies', []],
    'data streams' => ['get', 'data_engineering.streams', []],
    'report scheduler store' => ['post', 'report_scheduler.store', []],
]);

test('siswa dan orang tua ditolak di modul internal', function (string $role, string $method, string $route, array $params) {
    $this->actingAs(User::factory()->create(['role' => $role]))
        ->{$method}(route($route, $params))
        ->assertForbidden();
})->with(['siswa', 'orang_tua', 'student', 'parent'])->with('internal pages');

test('detail laporan bullying tertutup untuk siswa dan orang tua, terbuka untuk BK', function () {
    $report = bullyingReport();

    foreach (['siswa', 'orang_tua'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))->get(route('crisis.bullying.show', $report->id))->assertForbidden();
    }
    $this->actingAs(User::factory()->create(['role' => 'bk']))->get(route('crisis.bullying.show', $report->id))->assertOk();
});

test('peran yang berhak membuka modulnya', function (string $role, string $route) {
    $this->actingAs(User::factory()->create(['role' => $role]))->get(route($route))->assertOk();
})->with([
    ['staf_tu', 'treasury.accounts.index'],
    ['kepala_sekolah', 'treasury.accounts.index'],
    ['bk', 'crisis.bullying.index'],
    ['waka_kesiswaan', 'crisis.bullying.statistics'],
    ['kepala_sekolah', 'recruitment.kanban'],
    ['admin', 'data_engineering.streams'],
    ['kepala_sekolah', 'data_engineering.streams'],
]);

test('admin tidak otomatis tembus: guru dan BK ditolak di treasury', function () {
    foreach (['guru', 'bk'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))->get(route('treasury.accounts.index'))->assertForbidden();
    }
    // Viewing is not changing: the principal sees treasury but cannot add an account.
    $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))->post(route('treasury.accounts.store'), [])->assertForbidden();
});

test('siapa pun yang login boleh melapor bullying', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)->get(route('crisis.bullying.report'))->assertOk();
    $this->actingAs($user)->post(route('crisis.bullying.store'), [
        'description' => 'Dipalak di gerbang', 'type' => 'pemerasan', 'severity' => 'sedang', 'is_anonymous' => '1',
    ])->assertRedirect(route('crisis.bullying.report'));

    expect(BullyingReport::sole())->reporter_id->toBeNull()->is_anonymous->toBeTrue();
})->with(['siswa', 'orang_tua']);

test('laporan bullying yang tidak anonim menyimpan pelapornya', function () {
    $student = User::factory()->create(['role' => 'siswa']);

    $this->actingAs($student)->post(route('crisis.bullying.store'), [
        'description' => 'Dipukul', 'type' => 'fisik', 'severity' => 'berat', 'is_anonymous' => '0',
    ])->assertRedirect();

    expect(BullyingReport::sole()->reporter_id)->toBe($student->id);
});

// 118.5.3: the sidebar no longer shows internal menus to students and parents.
test('sidebar siswa dan orang tua tidak memuat menu internal', function (string $role) {
    $user = User::factory()->create(['role' => $role]);
    if (in_array($role, ['siswa', 'student'], true)) {
        Student::factory()->create(['user_id' => $user->id]);
    }
    $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

    expect($html)->not->toContain(route('recruitment.kanban'))
        ->not->toContain(route('data_engineering.streams'))
        ->not->toContain(url('/treasury/'))
        ->not->toContain(route('visitors.dashboard'))
        ->not->toContain(route('iep.index'));
})->with(['siswa', 'orang_tua', 'student', 'parent']);

test('sidebar siswa memuat modul belajarnya sendiri', function () {
    $student = Student::factory()->create();

    expect($this->actingAs($student->user)->get('/dashboard')->assertOk()->getContent())
        ->toContain(route('micro-lessons.index'))
        ->toContain(route('internships.portal'));
});

test('sidebar pimpinan tetap memuat menu yang boleh dibukanya', function () {
    $html = $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))->get('/dashboard')->assertOk()->getContent();

    expect($html)->toContain(route('recruitment.kanban'))
        ->toContain(route('data_engineering.streams'));
});

// 118.5.4: the waste log is really saved.
test('log sampah benar-benar tersimpan dan tampil di tracker', function () {
    $clerk = User::factory()->create(['role' => 'staf_tu']);
    $employee = employeeFor($clerk);

    $this->actingAs($clerk)->get(route('waste.tracker'))->assertOk()->assertSee('Belum ada log sampah.');

    $this->actingAs($clerk)->post(route('waste.store'), [
        'log_date' => now()->toDateString(), 'waste_category' => 'Plastik', 'weight_kg' => '12.5', 'disposal_method' => 'bank_sampah',
    ])->assertRedirect(route('waste.tracker'))->assertSessionHas('success', 'Log sampah tersimpan.');

    $log = WasteLog::sole();
    expect($log->waste_category)->toBe('Plastik')
        ->and((float) $log->weight_kg)->toBe(12.5)
        ->and($log->recorded_by)->toBe($employee->id);

    $this->actingAs($clerk)->get(route('waste.tracker'))->assertSee('Plastik')->assertSee($clerk->name);
});

test('log sampah tanpa data pegawai atau dengan data salah tidak tersimpan', function () {
    $clerk = User::factory()->create(['role' => 'staf_tu']);
    $valid = ['log_date' => now()->toDateString(), 'waste_category' => 'Kertas', 'weight_kg' => '3', 'disposal_method' => 'recycled'];

    $this->actingAs($clerk)->post(route('waste.store'), $valid)->assertSessionHasErrors('log_date');
    employeeFor($clerk);
    $this->actingAs($clerk)->post(route('waste.store'), ['weight_kg' => '0'] + $valid)->assertSessionHasErrors('weight_kg');
    $this->actingAs($clerk)->post(route('waste.store'), ['disposal_method' => 'dibakar'] + $valid)->assertSessionHasErrors('disposal_method');
    $this->actingAs($clerk)->post(route('waste.store'), ['log_date' => now()->addDay()->toDateString()] + $valid)->assertSessionHasErrors('log_date');

    expect(WasteLog::count())->toBe(0);
});

test('siswa dan kepala sekolah tidak mencatat log sampah', function () {
    $this->actingAs(User::factory()->create(['role' => 'siswa']))->get(route('waste.tracker'))->assertForbidden();

    $principal = User::factory()->create(['role' => 'kepala_sekolah']);
    employeeFor($principal);
    $this->actingAs($principal)->get(route('waste.tracker'))->assertOk()->assertDontSee(route('waste.store'));
    $this->actingAs($principal)->post(route('waste.store'), ['log_date' => now()->toDateString(), 'waste_category' => 'x', 'weight_kg' => 1, 'disposal_method' => 'landfill'])
        ->assertForbidden();

    expect(WasteLog::count())->toBe(0);
});

// 118.3: forms without a backend say so instead of pretending.
test('aksi yang belum punya backend menjawab 501, bukan sukses palsu', function () {
    $clerk = User::factory()->create(['role' => 'staf_tu']);

    $this->actingAs($clerk)->post(route('carbon.store'), ['kwh' => 120])
        ->assertStatus(501)
        ->assertSee('Fitur ini belum tersedia. Perubahan belum bisa disimpan.')
        ->assertSessionMissing('success');

    $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('report_scheduler.store'))->assertStatus(501);
});

test('route stub yang dihapus tidak ada lagi', function () {
    expect(Route::has('canteen.menu_planner'))->toBeFalse()
        ->and(Route::has('users.show'))->toBeFalse();
});

// 118.2: records are looked up by id and owner.
test('Student::visibleTo membatasi data per pemanggil', function () {
    $year = AcademicYear::factory()->create(['year' => '2026/2027', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'guru']);
    $taught = Classroom::factory()->create(['academic_year' => $year->year]);
    Schedule::factory()->create(['teacher_id' => $teacher->id, 'classroom_id' => $taught->id, 'academic_year_id' => $year->id]);
    $parent = User::factory()->create(['role' => 'orang_tua']);
    $mine = Student::factory()->create(['classroom_id' => $taught->id, 'parent_id' => $parent->id]);
    $other = Student::factory()->create();
    // Taught last year only: not visible this year.
    $lastYear = AcademicYear::factory()->create(['year' => '2025/2026']);
    $old = Classroom::factory()->create(['academic_year' => $lastYear->year]);
    Schedule::factory()->create(['teacher_id' => $teacher->id, 'classroom_id' => $old->id, 'academic_year_id' => $lastYear->id]);
    $formerPupil = Student::factory()->create(['classroom_id' => $old->id]);

    $ids = fn (User $user) => Student::visibleTo($user)->pluck('id')->sort()->values()->all();

    expect($ids($teacher))->toBe([$mine->id])
        ->and($ids($parent))->toBe([$mine->id])
        ->and($ids($mine->user))->toBe([$mine->id])
        ->and($ids(User::factory()->create(['role' => 'bk'])))->toHaveCount(3)
        ->and($ids(User::factory()->create(['role' => 'merchant'])))->toBe([])
        ->and($formerPupil->isVisibleTo($teacher))->toBeFalse()
        ->and($other->isVisibleTo($parent))->toBeFalse();
});

test('orang tua hanya melihat mastery anaknya sendiri', function () {
    $parent = User::factory()->create(['role' => 'orang_tua']);
    $child = Student::factory()->create(['parent_id' => $parent->id]);
    $stranger = Student::factory()->create();

    $this->actingAs($parent)->get(route('adaptive.mastery.student', $child->id))->assertOk();
    $this->actingAs($parent)->get(route('adaptive.mastery.student', $stranger->id))->assertNotFound();
    $this->actingAs($parent)->get(route('adaptive.mastery.student', 999999))->assertNotFound();
});

test('siswa hanya membuka sesi CAT miliknya', function () {
    $student = Student::factory()->create();
    $session = fn (Student $owner) => CatSession::create([
        'uuid' => (string) Str::uuid(), 'student_id' => $owner->id, 'exam_id' => Exam::factory()->create()->id,
        'current_theta' => 0, 'started_at' => now(), 'status' => 'in_progress',
    ]);
    $own = $session($student);
    $foreign = $session(Student::factory()->create());

    $this->actingAs($student->user)->get(route('adaptive.cat.take', $own->id))->assertOk();
    $this->actingAs($student->user)->get(route('adaptive.cat.take', $foreign->id))->assertNotFound();
    $this->actingAs($student->user)->get(route('adaptive.cat.result', $foreign->id))->assertNotFound();
});

test('guru hanya membuka IEP siswa di kelas yang diajarnya', function () {
    $year = AcademicYear::factory()->create(['year' => '2026/2027', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'guru']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id, 'academic_year' => $year->year]);
    $caseManager = employeeFor(User::factory()->create(['role' => 'bk']));
    $iep = fn (Student $student) => IepDocument::create([
        'student_id' => $student->id, 'case_manager_id' => $caseManager->id, 'start_date' => '2026-07-15', 'review_date' => '2026-12-15',
        'present_level_of_performance' => 'Membaca lancar', 'annual_goals' => [], 'required_accommodations' => [],
    ]);
    $own = $iep(Student::factory()->create(['classroom_id' => $classroom->id]));
    $foreign = $iep(Student::factory()->create());

    $this->actingAs($teacher)->get(route('iep.show', $own))->assertOk();
    $this->actingAs($teacher)->get(route('iep.show', $foreign))->assertNotFound();
    $this->actingAs($caseManager->user)->get(route('iep.show', $foreign))->assertOk();
});

test('orang tua hanya melaporkan izin untuk anaknya sendiri', function () {
    $parent = User::factory()->create(['role' => 'orang_tua']);
    $stranger = Student::factory()->create();

    $this->actingAs($parent)->postJson(route('mobile.parent.report-absence'), [
        'student_id' => $stranger->id, 'status' => 'sakit', 'reason' => 'Demam',
    ])->assertNotFound();
});

test('hanya approver yang ditunjuk yang memutus persetujuan', function () {
    $requester = User::factory()->create(['role' => 'guru']);
    $approver = User::factory()->create(['role' => 'kepala_sekolah']);
    $blueprint = WorkflowBlueprint::create(['name' => 'Cuti', 'process_type' => 'leave_request', 'bpmn_schema' => [], 'created_by' => $approver->id]);
    $chain = ApprovalChain::create(['workflow_blueprint_id' => $blueprint->id, 'requester_id' => $requester->id]);
    $step = ApprovalRequest::create(['approval_chain_id' => $chain->id, 'approver_id' => $approver->id, 'node_key' => 'kepsek']);

    foreach ([$requester, User::factory()->create(['role' => 'waka_kurikulum']), User::factory()->create(['role' => 'admin'])] as $other) {
        $this->actingAs($other)->postJson(route('workflow.approvals.decision', $step), ['decision' => 'approved'])->assertForbidden();
    }

    expect($step->fresh()->decision)->toBe('pending');
});

test('hanya host yang menyimpan kanvas whiteboard', function () {
    $host = User::factory()->create(['role' => 'guru']);
    $session = WhiteboardSession::create(['session_id' => (string) Str::uuid(), 'title' => 'Fiqih', 'host_id' => $host->id]);

    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->postJson(route('interactive.whiteboard.save', $session), ['canvas_state_json' => '{}'])
        ->assertForbidden();

    expect($session->fresh()->canvas_state_json)->toBeNull();
});
