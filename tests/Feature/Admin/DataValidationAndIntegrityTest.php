<?php

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Set up temp directory for CSV files
    $this->tempDir = storage_path('framework/testing/legacy_mock');
    if (File::exists($this->tempDir)) {
        File::deleteDirectory($this->tempDir);
    }
    File::makeDirectory($this->tempDir, 0777, true, true);
});

afterEach(function () {
    // Clean up temp directory
    if (File::exists($this->tempDir)) {
        File::deleteDirectory($this->tempDir);
    }
});

test('data:validate command returns exit code 0 when database is clean', function () {
    // Run validation on fresh empty database
    $this->artisan('data:validate')
        ->expectsOutputToContain('Integritas data sempurna')
        ->assertExitCode(0);
});

test('data:validate command fails and detects duplicates and orphaned records', function () {
    // 1. Invalid/Empty fields user
    DB::table('users')->insert([
        'name' => '', // Empty name
        'email' => 'invalid-email-format', // Invalid email format
        'password' => 'secret',
        'role' => 'siswa',
        'uuid' => 'test-uuid-1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create a classroom to prevent foreign key errors
    $classroom = Classroom::create([
        'name' => 'X-IPA-1',
        'grade_level' => 'X',
        'major' => 'MIPA',
        'academic_year' => '2026/2027',
    ]);

    // Create a student user
    $cleanStudent = User::factory()->create(['role' => 'siswa']);
    $student = Student::create([
        'user_id' => $cleanStudent->id,
        'classroom_id' => $classroom->id,
        'nis' => '33333',
        'nisn' => '44444',
        'gender' => 'P',
    ]);

    $subject = DB::table('subjects')->insertGetId([
        'name' => 'Fisika',
        'code' => 'FIS',
        'kkm' => 75,
        'category' => 'Nasional',
    ]);
    $acYear = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);

    // 2. Boundary grade issue (Grade > 100)
    Grade::create([
        'student_id' => $student->id,
        'subject_id' => $subject,
        'academic_year_id' => $acYear->id,
        'type' => 'uh',
        'score' => 150, // Invalid boundary score
        'recorded_by' => $cleanStudent->id,
    ]);

    // 3. Future attendance issue
    Attendance::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $acYear->id,
        'date' => now()->addDay()->toDateString(), // Future date
        'status' => 'H',
        'recorded_by' => $cleanStudent->id,
    ]);

    // Run validation command and assert exit code is 1 (failure)
    $this->artisan('data:validate')
        ->expectsOutputToContain('Ditemukan 4 isu integritas data')
        ->assertExitCode(1);
});

test('data:migrate-legacy command successfully imports students and employees from CSV files', function () {
    // 1. Seed dependencies
    Classroom::create([
        'name' => 'X-IPA-1',
        'grade_level' => 'X',
        'major' => 'MIPA',
        'academic_year' => '2026/2027',
    ]);

    // 2. Create mock CSV files
    $studentCsv = $this->tempDir.'/students.csv';
    $studentContent = "name,email,nis,nisn,classroom_name,gender,birth_date,address,parent_name,parent_email,parent_phone\n".
                      'Siswa A,siswa.a@gmail.com,S001,N001,X-IPA-1,L,2010-05-15,Semarang,Wali A,wali.a@gmail.com,08123456789';
    File::put($studentCsv, $studentContent);

    $employeeCsv = $this->tempDir.'/employees.csv';
    $employeeContent = "name,email,employee_number,position,employment_status,base_salary,hourly_rate\n".
                        'Guru B,guru.b@gmail.com,E001,Guru Fisika,gty,3500000,50000';
    File::put($employeeCsv, $employeeContent);

    // 3. Run migration command pointing to the directory
    $this->artisan('data:migrate-legacy', ['--source' => $this->tempDir])
        ->expectsConfirmation('Apakah Anda yakin ingin memproses impor data murid ini?', 'yes')
        ->expectsConfirmation('Apakah Anda yakin ingin memproses impor data pegawai ini?', 'yes')
        ->assertExitCode(0);

    // 4. Assert database states
    // Student User
    $studentUser = User::where('email', 'siswa.a@gmail.com')->first();
    expect($studentUser)->not->toBeNull();
    expect($studentUser->role)->toEqual('siswa');
    expect($studentUser->must_change_password)->toBeTrue();
    expect(Hash::check('SistemTerpadu123', $studentUser->password))->toBeTrue();

    // Student Record
    $studentRecord = Student::where('nis', 'S001')->first();
    expect($studentRecord)->not->toBeNull();
    expect($studentRecord->nisn)->toEqual('N001');
    expect($studentRecord->user_id)->toEqual($studentUser->id);

    // Parent User
    $parentUser = User::where('email', 'wali.a@gmail.com')->first();
    expect($parentUser)->not->toBeNull();
    expect($parentUser->role)->toEqual('orang_tua');

    // Employee
    $employeeUser = User::where('email', 'guru.b@gmail.com')->first();
    expect($employeeUser)->not->toBeNull();
    expect($employeeUser->role)->toEqual('guru');

    $employeeRecord = Employee::where('employee_number', 'E001')->first();
    expect($employeeRecord)->not->toBeNull();
    expect($employeeRecord->position)->toEqual('Guru Fisika');
    expect($employeeRecord->base_salary)->toEqual(3500000);
});

test('data:migrate-legacy command rolls back transaction on error', function () {
    Classroom::create([
        'name' => 'X-IPA-1',
        'grade_level' => 'X',
        'major' => 'MIPA',
        'academic_year' => '2026/2027',
    ]);

    // Create CSV containing one valid student and one invalid student (unknown classroom)
    $studentCsv = $this->tempDir.'/students.csv';
    $studentContent = "name,email,nis,nisn,classroom_name,gender,birth_date,address,parent_name,parent_email,parent_phone\n".
                      "Siswa Valid,valid@gmail.com,S002,N002,X-IPA-1,L,2010-05-15,Semarang,Wali A,wali.a@gmail.com,08123456789\n".
                      'Siswa Invalid,invalid@gmail.com,S003,N003,NON_EXISTENT_CLASS,P,2010-05-15,Semarang,Wali B,wali.b@gmail.com,08123456789';
    File::put($studentCsv, $studentContent);

    // Run import and expect it to fail verification (unknown classroom)
    $this->artisan('data:migrate-legacy', ['--source' => $this->tempDir])
        ->assertExitCode(1);

    // Verify database transaction rollback: first student (Siswa Valid) must NOT be imported
    expect(User::where('email', 'valid@gmail.com')->exists())->toBeFalse();
    expect(Student::where('nis', 'S002')->exists())->toBeFalse();
});
