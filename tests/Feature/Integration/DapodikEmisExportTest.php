<?php

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\User;
use App\Services\Integration\DapodikService;
use App\Services\Integration\EmisService;

/*
 * The Dapodik and EMIS exports filled gaps with invented values: every
 * student born in "Semarang" to a mother named "Ibu Kandung Siswa", every
 * teacher "Sudah Sertifikasi", the foundation's NIY under the NUPTK column,
 * "0 Juz" for students with no deposits, and the highest juz number as the
 * number memorised. They now export what the school recorded, leave the rest
 * empty, and the pre-flight check lists what to fill in.
 */

beforeEach(function () {
    $this->tu = User::factory()->create(['role' => 'staf_tu']);
    $this->classroom = Classroom::factory()->create(['name' => 'X-1']);
});

function dapodikStudent(array $attributes = []): Student
{
    return Student::factory()->create(['classroom_id' => test()->classroom->id, 'nisn' => (string) fake()->unique()->numerify('##########'), 'birth_date' => '2010-03-04', ...$attributes]);
}

test('ekspor siswa Dapodik memakai tempat lahir dan nama ibu yang tercatat', function () {
    dapodikStudent(['birth_place' => 'Kudus', 'mother_name' => 'Siti Aminah', 'gender' => 'P']);

    $row = app(DapodikService::class)->exportStudentData()[0];

    expect($row)->toMatchArray(['tempat_lahir' => 'Kudus', 'ibu_kandung' => 'Siti Aminah', 'gender' => 'P', 'kelas' => 'X-1'])
        ->and(app(DapodikService::class)->validateAgainstDapodik('students', [$row]))->toBe([]);
});

test('data yang belum diisi diekspor kosong dan ditandai, bukan diisi teks karangan', function () {
    // The address may legitimately mention Semarang; only the empty fields must stay empty.
    dapodikStudent(['classroom_id' => null, 'address' => 'Jl. Kaligawe Raya, Semarang']);

    $service = app(DapodikService::class);
    $rows = $service->exportStudentData();

    expect($rows[0])->toMatchArray(['tempat_lahir' => '', 'ibu_kandung' => '', 'kelas' => ''])
        ->and(array_values($rows[0]))->not->toContain('Semarang')->not->toContain('Ibu Kandung Siswa')->not->toContain('Belum ada kelas');

    $errors = $service->validateAgainstDapodik('students', $rows)[0]['errors'];
    expect($errors)->toContain('Tempat Lahir wajib diisi.')
        ->toContain('Nama Ibu Kandung wajib diisi.')
        ->toContain('Siswa belum dimasukkan ke Rombel Kelas.');

    $this->actingAs($this->tu)->get(route('admin.integrations.dapodik', ['type' => 'students']))
        ->assertOk()
        ->assertSee('Nama Ibu Kandung wajib diisi.')
        ->assertDontSee('Ibu Kandung Siswa');
});

test('ekspor PTK memisahkan NUPTK dari NIY', function () {
    Employee::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'employee_number' => 'NIY-0042', 'nuptk' => '1234567890123456', 'position' => 'Guru']);
    Employee::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'employee_number' => 'NIY-0043', 'position' => 'Guru']);

    $service = app(DapodikService::class);
    $rows = $service->exportTeacherData();

    expect($rows[0])->toMatchArray(['nuptk' => '1234567890123456', 'niy' => 'NIY-0042'])
        ->and($rows[1])->toMatchArray(['nuptk' => '', 'niy' => 'NIY-0043'])
        ->and($service->validateAgainstDapodik('teachers', $rows))->toHaveCount(1)
        ->and($service->generateDapodikExcel('teachers')->headings())->toHaveCount(count($rows[0]));
});

test('EMIS: capaian tahfidz menghitung juz yang disetor, kosong bila belum ada setoran', function () {
    $hafidz = dapodikStudent();
    $baru = dapodikStudent();
    $teacher = User::factory()->create(['role' => 'guru']);
    foreach ([[30, 'ziyadah'], [30, 'ziyadah'], [29, 'ziyadah'], [1, 'murajaah']] as [$juz, $type]) {
        TahfidzLog::create(['student_id' => $hafidz->id, 'teacher_id' => $teacher->id, 'surah_number' => 78, 'surah_name' => 'An-Naba', 'from_ayat' => 1, 'to_ayat' => 10, 'juz' => $juz, 'type' => $type, 'date' => '2026-09-01']);
    }

    $rows = collect(app(EmisService::class)->exportStudentData())->keyBy('nisn');

    // Used to read the highest juz number: "30 Juz" for someone who deposited juz 29 and 30.
    expect($rows[$hafidz->nisn]['capaian_tahfidz'])->toBe('2 Juz')
        ->and($rows[$baru->nisn]['capaian_tahfidz'])->toBe('');
});

test('EMIS: sertifikasi hanya "Sudah" bila ada dokumen sertifikasi, dan judul kolom sesuai isinya', function () {
    $certified = Employee::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'employee_number' => 'NIY-1', 'position' => 'Guru']);
    Employee::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'employee_number' => 'NIY-2', 'position' => 'Guru']);
    EmployeeDocument::create(['employee_id' => $certified->id, 'title' => 'Sertifikat Pendidik', 'type' => 'sertifikasi', 'file_path' => 'hrd/docs/serdik.pdf']);
    dapodikStudent();

    $service = app(EmisService::class);
    $teachers = $service->exportTeacherData();

    expect(array_column($teachers, 'sertifikasi'))->toBe(['Sudah', ''])
        ->and(json_encode($teachers))->not->toContain('Sudah Sertifikasi');

    // Eight headings over five student columns shifted every value into the wrong column.
    expect($service->generateEmisExcel('students')->headings())->toHaveCount(count($service->exportStudentData()[0]))
        ->and($service->generateEmisExcel('teachers')->headings())->toHaveCount(count($teachers[0]));
});

test('form siswa dan pegawai menyimpan data identitas Dapodik', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($admin)->post(route('students.store'), [
        'user_id' => $user->id, 'nis' => '2026001', 'nisn' => '0012345678', 'gender' => 'P', 'birth_date' => '2010-01-01',
        'birth_place' => 'Demak', 'mother_name' => 'Fatimah',
    ])->assertRedirect();

    expect(Student::where('user_id', $user->id)->sole())->birth_place->toBe('Demak')->mother_name->toBe('Fatimah');

    $teacher = User::factory()->create(['role' => 'guru']);
    $payload = ['user_id' => $teacher->id, 'employee_number' => 'NIY-77', 'position' => 'Guru', 'employment_status' => 'gty', 'hire_date' => '2026-01-01'];
    $this->actingAs($admin)->post(route('hrd.employees.store'), [...$payload, 'nuptk' => '12345'])->assertSessionHasErrors('nuptk');
    $this->actingAs($admin)->post(route('hrd.employees.store'), [...$payload, 'nuptk' => '1234567890123456'])->assertRedirect();

    expect(Employee::where('user_id', $teacher->id)->sole()->nuptk)->toBe('1234567890123456');
});

test('guru dan siswa tidak bisa membuka atau mengekspor data Dapodik', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]));

    $this->get(route('admin.integrations.dapodik'))->assertForbidden();
    $this->post(route('admin.integrations.dapodik.export'), ['type' => 'students'])->assertForbidden();
})->with(['guru', 'siswa', 'orang_tua']);
