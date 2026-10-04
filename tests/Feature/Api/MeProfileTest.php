<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use App\Support\Access\RoleLabel;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/*
 * GET /api/v1/me is what the app's profile page shows. It used to fill the
 * identity card from constants (every teacher had the same NIP, every student
 * "XII MIPA 1"), because the endpoint only knew the role spelled 'student'
 * and sent "N/A" for a missing class.
 */

beforeEach(function () {
    Cache::forget('academic_year_current');
});

function activeYear(string $year = '2026/2027'): AcademicYear
{
    $active = AcademicYear::factory()->create(['year' => $year, 'semester' => 'Ganjil', 'is_active' => true]);
    Cache::forget('academic_year_current');

    return $active;
}

test('a student spelled siswa gets their own number and class', function () {
    activeYear();
    $classroom = Classroom::factory()->create(['name' => 'XI MIPA 2', 'academic_year' => '2026/2027']);
    $user = User::factory()->create(['role' => 'siswa', 'name' => 'Siti Aminah']);
    Student::factory()->create(['user_id' => $user->id, 'classroom_id' => $classroom->id, 'nis' => '12345', 'nisn' => '0099887766']);

    $data = $this->actingAs($user->fresh(), 'sanctum')->getJson('/api/v1/me')->assertOk()->json('data');

    expect($data['role_label'])->toBe('Siswa')
        ->and($data['student_data']['nis'])->toBe('12345')
        ->and($data['student_data']['nisn'])->toBe('0099887766')
        ->and($data['student_data']['classroom'])->toBe('XI MIPA 2')
        ->and($data['student_data']['classroom_id'])->toBe($classroom->id)
        ->and($data['academic_year'])->toMatchArray(['year' => '2026/2027', 'semester' => 'Ganjil']);
});

test('a student without a class gets null, not N/A', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $student->forceFill(['classroom_id' => null])->save();

    $data = $this->actingAs($user->fresh(), 'sanctum')->getJson('/api/v1/me')->assertOk()->json('data');

    expect($data['student_data']['classroom'])->toBeNull()
        ->and($data['student_data']['classroom_id'])->toBeNull();
});

test('no active academic year is null', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $data = $this->actingAs($user->fresh(), 'sanctum')->getJson('/api/v1/me')->assertOk()->json('data');

    expect($data['academic_year'])->toBeNull()
        ->and($data['role_label'])->toBe('Administrator')
        ->and($data['student_data'])->toBeNull()
        ->and($data['children'])->toBeNull();
});

test('a parent sees only their own children', function () {
    $parent = User::factory()->create(['role' => 'orang_tua']);
    $classroom = Classroom::factory()->create(['name' => 'X IPS 1']);
    $child = Student::factory()->create([
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
        'user_id' => User::factory()->create(['role' => 'student', 'name' => 'Anak Sendiri'])->id,
    ]);
    Student::factory()->create([
        'parent_id' => User::factory()->create(['role' => 'parent'])->id,
        'user_id' => User::factory()->create(['role' => 'student', 'name' => 'Anak Keluarga Lain'])->id,
    ]);

    $data = $this->actingAs($parent->fresh(), 'sanctum')->getJson('/api/v1/me')->assertOk()->json('data');

    expect($data['role_label'])->toBe('Wali Murid')
        ->and($data['children'])->toBe([['uuid' => $child->uuid, 'name' => 'Anak Sendiri', 'classroom' => 'X IPS 1']]);
});

test('a parent without linked children gets an empty list', function () {
    $parent = User::factory()->create(['role' => 'wali_murid']);

    $data = $this->actingAs($parent->fresh(), 'sanctum')->getJson('/api/v1/me')->assertOk()->json('data');

    expect($data['children'])->toBe([]);
});

test('a teacher gets their employee number and only this year\'s homeroom', function () {
    activeYear();
    $teacher = User::factory()->create(['role' => 'guru']);
    Employee::create(['user_id' => $teacher->id, 'employee_number' => '198001012005011001', 'position' => 'Guru Fisika']);
    $current = Classroom::factory()->create(['name' => 'XI MIPA 2', 'academic_year' => '2026/2027', 'teacher_id' => $teacher->id]);
    Classroom::factory()->create(['name' => 'XI MIPA 2', 'academic_year' => '2025/2026', 'teacher_id' => $teacher->id]);
    Classroom::factory()->create(['name' => 'XI MIPA 3', 'academic_year' => '2026/2027']);

    $data = $this->actingAs($teacher->fresh(), 'sanctum')->getJson('/api/v1/me')->assertOk()->json('data');

    expect($data['role_label'])->toBe('Guru')
        ->and($data['employee_data'])->toBe(['employee_number' => '198001012005011001', 'position' => 'Guru Fisika'])
        ->and($data['homeroom_classrooms'])->toBe([['id' => $current->id, 'name' => 'XI MIPA 2']]);
});

test('staff without an employee record get null, not a made-up number', function () {
    $user = User::factory()->create(['role' => 'kepala_sekolah']);

    $data = $this->actingAs($user->fresh(), 'sanctum')->getJson('/api/v1/me')->assertOk()->json('data');

    expect($data['employee_data'])->toBeNull()
        ->and($data['role_label'])->toBe('Kepala Sekolah');
});

test('me needs a token', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});

test('staff can sign in with their NIP', function () {
    $teacher = User::factory()->create(['role' => 'guru', 'email' => 'guru@sekolah.test']);
    Employee::create(['user_id' => $teacher->id, 'employee_number' => '198001012005011001', 'position' => 'Guru Fisika']);

    $this->postJson('/api/v1/login', [
        'email' => '198001012005011001',
        'password' => 'password',
        'device_name' => 'PHPUnit',
    ])->assertOk()->assertJsonPath('data.user.email', 'guru@sekolah.test');
});

test('an unknown identifier is refused, not an error', function () {
    $this->postJson('/api/v1/login', [
        'email' => '000000000000000000',
        'password' => 'password',
        'device_name' => 'PHPUnit',
    ])->assertUnauthorized();
});

test('every spelling of a role reads the same', function () {
    expect(RoleLabel::of('student'))->toBe(RoleLabel::of('siswa'))
        ->and(RoleLabel::of('parent'))->toBe('Wali Murid')
        ->and(RoleLabel::of('orangtua'))->toBe('Wali Murid')
        ->and(RoleLabel::of('teacher'))->toBe('Guru')
        ->and(RoleLabel::of('kepsek'))->toBe('Kepala Sekolah')
        ->and(RoleLabel::of('admin'))->not->toBe(RoleLabel::of('kepala_sekolah'))
        ->and(RoleLabel::of('koordinator_lab'))->toBe('Koordinator Lab');
});

test('mobile config carries the school identity, empty fields as null', function () {
    config(['school.accreditation' => null]);

    $school = $this->getJson('/api/v1/mobile/config')->assertOk()->json('school');

    expect($school['name'])->toBe(config('school.name'))
        ->and($school['npsn'])->toBe(config('school.npsn'))
        ->and($school['accreditation'])->toBeNull();
});
