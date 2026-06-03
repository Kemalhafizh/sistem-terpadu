<?php

use App\Models\User;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\ImportLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');

    // Create users with different roles
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->staf = User::factory()->create(['role' => 'staf_tu']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
});

// ── ACCESS CONTROL ───────────────────────────────────────────────────────

test('guest cannot access import export hub', function () {
    $this->get(route('import-export.index'))->assertRedirect('/login');
});

test('unauthorized roles cannot access import export hub', function () {
    $this->actingAs($this->guru)->get(route('import-export.index'))->assertStatus(403);
    $this->actingAs($this->studentUser)->get(route('import-export.index'))->assertStatus(403);
});

test('authorized roles can view import export hub index page', function () {
    $response = $this->actingAs($this->admin)->get(route('import-export.index'));
    $response->assertStatus(200);
    $response->assertViewIs('import-export.index');
    $response->assertViewHas('modules');
});

// ── UPLOAD & PREVIEW ─────────────────────────────────────────────────────

test('admin can upload a csv file and view column mapping page', function () {
    $csvContent = "Nama Lengkap,Email,NIS,NISN,Gender,Kelas,Alamat\n"
        . "Ahsan Hadi,ahsan@sista.test,1001,990001,L,X-A,Semarang\n"
        . "Safira Putri,safira@sista.test,1002,990002,P,X-A,Demak\n";

    $file = UploadedFile::fake()->createWithContent('siswa.csv', $csvContent);

    $response = $this->actingAs($this->admin)->post(route('import-export.upload'), [
        'module' => 'students',
        'file' => $file,
    ]);

    $response->assertStatus(200);
    $response->assertViewIs('import-export.mapping');
    $response->assertViewHas('headers');
    $response->assertViewHas('preview');
    $response->assertViewHas('totalRows');
    $response->assertViewHas('dbColumns');
});

// ── EXECUTE & BATCH ROLLBACK ─────────────────────────────────────────────

test('admin can execute dynamic student import and perform rollback', function () {
    // 1. Seed classroom
    $classroom = Classroom::create([
        'name' => 'X-A',
        'grade_level' => 'X',
        'major' => 'Umum',
        'academic_year' => '2025/2026',
    ]);

    $csvContent = "Nama Lengkap,Email,NIS,NISN,Gender,Kelas,Alamat\n"
        . "Ahsan Hadi,ahsan@sista.test,1001,990001,L,X-A,Semarang\n"
        . "Safira Putri,safira@sista.test,1002,990002,P,X-A,Demak\n";

    $file = UploadedFile::fake()->createWithContent('siswa.csv', $csvContent);

    // Save temporary file in temp_imports
    $tempPath = $file->store('temp_imports');

    // Mappings: matches SISTA DB field -> column index
    $mapping = [
        'name' => 0,
        'email' => 1,
        'nis' => 2,
        'nisn' => 3,
        'gender' => 4,
        'classroom' => 5,
        'address' => 6,
    ];

    // Verify temp file exists
    expect(Storage::exists($tempPath))->toBeTrue();

    // 2. Post to execute
    $response = $this->actingAs($this->admin)->post(route('import-export.execute'), [
        'module' => 'students',
        'file_path' => $tempPath,
        'file_name' => 'siswa.csv',
        'mapping' => $mapping,
    ]);

    $response->assertRedirect(route('import-export.history'));
    $response->assertSessionHas('success');

    // 3. Assert Database creations
    $this->assertDatabaseHas('users', ['email' => 'ahsan@sista.test', 'role' => 'student']);
    $this->assertDatabaseHas('users', ['email' => 'safira@sista.test', 'role' => 'student']);
    $this->assertDatabaseHas('students', ['nis' => '1001', 'nisn' => '990001', 'gender' => 'L', 'classroom_id' => $classroom->id]);
    $this->assertDatabaseHas('students', ['nis' => '1002', 'nisn' => '990002', 'gender' => 'P', 'classroom_id' => $classroom->id]);

    // Temp file should be deleted automatically
    expect(Storage::exists($tempPath))->toBeFalse();

    // 4. Assert Import Log is completed
    $importLog = ImportLog::first();
    expect($importLog->status)->toBe('completed');
    expect($importLog->total_rows)->toBe(2);
    expect($importLog->success_count)->toBe(2);
    expect($importLog->error_count)->toBe(0);

    // 5. Run Rollback
    $rollbackResponse = $this->actingAs($this->admin)->post(route('import-export.rollback', $importLog->id));
    $rollbackResponse->assertRedirect();
    $rollbackResponse->assertSessionHas('success');

    // 6. Verify Rollback deleted everything successfully
    $this->assertDatabaseMissing('users', ['email' => 'ahsan@sista.test']);
    $this->assertDatabaseMissing('users', ['email' => 'safira@sista.test']);
    $this->assertDatabaseMissing('students', ['nis' => '1001']);
    $this->assertDatabaseMissing('students', ['nis' => '1002']);

    $importLog->refresh();
    expect($importLog->status)->toBe('rolled_back');
});

test('admin can execute dynamic classroom import', function () {
    $csvContent = "Nama Kelas,Tingkat Kelas,Jurusan,Tahun Akademik\n"
        . "X IPA 1,X,IPA,2025/2026\n"
        . "XI IPS 1,XI,IPS,2025/2026\n";

    $file = UploadedFile::fake()->createWithContent('kelas.csv', $csvContent);
    $tempPath = $file->store('temp_imports');

    $mapping = [
        'name' => 0,
        'grade_level' => 1,
        'major' => 2,
        'academic_year' => 3,
    ];

    $response = $this->actingAs($this->admin)->post(route('import-export.execute'), [
        'module' => 'classrooms',
        'file_path' => $tempPath,
        'file_name' => 'kelas.csv',
        'mapping' => $mapping,
    ]);

    $response->assertRedirect(route('import-export.history'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('classrooms', ['name' => 'X IPA 1', 'grade_level' => 'X', 'major' => 'IPA', 'academic_year' => '2025/2026']);
    $this->assertDatabaseHas('classrooms', ['name' => 'XI IPS 1', 'grade_level' => 'XI', 'major' => 'IPS', 'academic_year' => '2025/2026']);
});

// ── UNIVERSAL EXPORTS ────────────────────────────────────────────────────

test('admin can export students to json format', function () {
    $classroom = Classroom::create([
        'name' => 'X-A',
        'grade_level' => 'X',
        'major' => 'Umum',
        'academic_year' => '2025/2026',
    ]);
    $user = User::create([
        'name' => 'Randi',
        'email' => 'randi@sista.test',
        'password' => bcrypt('password'),
        'role' => 'student'
    ]);
    Student::create([
        'user_id' => $user->id,
        'classroom_id' => $classroom->id,
        'nis' => '8899',
        'nisn' => '889900',
        'gender' => 'L',
        'address' => 'Semarang',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->admin)->post(route('import-export.export'), [
        'module' => 'students',
        'format' => 'json'
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'module',
        'exported_at',
        'headings',
        'data'
    ]);

    $data = $response->json();
    expect($data['module'])->toBe('students');
    expect($data['data'][0][0])->toBe('Randi');
    expect($data['data'][0][2])->toBe('8899');
});

test('admin can export students to pdf format', function () {
    $classroom = Classroom::create([
        'name' => 'X-A',
        'grade_level' => 'X',
        'major' => 'Umum',
        'academic_year' => '2025/2026',
    ]);
    $user = User::create([
        'name' => 'Randi',
        'email' => 'randi@sista.test',
        'password' => bcrypt('password'),
        'role' => 'student'
    ]);
    Student::create([
        'user_id' => $user->id,
        'classroom_id' => $classroom->id,
        'nis' => '8899',
        'nisn' => '889900',
        'gender' => 'L',
        'address' => 'Semarang',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->admin)->post(route('import-export.export'), [
        'module' => 'students',
        'format' => 'pdf'
    ]);

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
