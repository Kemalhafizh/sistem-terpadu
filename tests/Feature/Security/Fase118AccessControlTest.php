<?php

use App\Models\AcademicYear;
use App\Models\BullyingReport;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\IepDocument;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
 * the modules were guarded by `auth` alone, so any
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

    expect($html)->not->toContain(url('/treasury/'))
        ->not->toContain(route('iep.index'))
        ->not->toContain('href="'.route('crisis.bullying.index').'"')
        // Reporting is open to everyone, and the menu says so.
        ->toContain('href="'.route('crisis.bullying.report').'"');
})->with(['siswa', 'orang_tua', 'student', 'parent']);

test('sidebar siswa memuat modul belajarnya sendiri', function () {
    $student = Student::factory()->create();

    expect($this->actingAs($student->user)->get('/dashboard')->assertOk()->getContent())
        ->toContain(route('elearning.index'))
        ->toContain(route('cbt.exams.index'));
});

test('sidebar pimpinan tetap memuat menu yang boleh dibukanya', function () {
    $html = $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))->get('/dashboard')->assertOk()->getContent();

    expect($html)->toContain(route('iep.index'));
});

// 118.3: dead stubs without a route stay removed.
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
