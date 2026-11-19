<?php

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\DapodikSyncItem;
use App\Models\Employee;
use App\Models\ProfileChangeRequest;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Cache::flush();
    Storage::fake('local');
    Notification::fake();

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->schoolYear = SchoolYear::create([
        'name' => '2026/2027',
        'start_date' => '2026-07-13',
        'end_date' => '2027-06-30',
        'status' => SchoolYear::STATUS_ACTIVE,
    ]);

    $this->tu = User::factory()->create(['role' => 'staf_tu', 'name' => 'Staf TU 1']);
    $this->tu2 = User::factory()->create(['role' => 'staf_tu', 'name' => 'Staf TU 2']);
    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Sekolah']);
});

test('k1 dapodik csv import creates classrooms, teachers, students, enrollments, shared parent accounts, and reports failed rows explicitly', function () {
    // 1. Import Classrooms CSV
    $classroomsCsv = implode("\n", [
        'Nama Rombel,Tingkat Kelas,Jurusan,Tahun Ajaran',
        'X-1,10,Umum,2026/2027',
        'XI-MIPA-1,XI,MIPA,2026/2027',
        ',99,Umum,2026/2027', // invalid row: empty name & invalid grade
    ]);

    $this->actingAs($this->tu)
        ->post(route('admin.integrations.dapodik.import'), [
            'type' => 'classrooms',
            'file' => UploadedFile::fake()->createWithContent('rombel.csv', $classroomsCsv),
            'create_accounts' => '1',
        ])->assertRedirect(route('admin.integrations.dapodik', ['type' => 'classrooms']));

    expect(Classroom::where('name', 'X-1')->exists())->toBeTrue()
        ->and(Classroom::where('name', 'XI-MIPA-1')->exists())->toBeTrue();

    // 2. Import Teachers CSV (with semicolon delimiter as exported by Indonesian Excel)
    $teachersCsv = implode("\n", [
        'Nama Lengkap;NUPTK;NIY;NIK;No HP;Jabatan;Status Kepegawaian',
        'Drs. Ahmad Subarjo;1234567890123456;NIY-901;3374010101800001;081234500001;Guru Fisika;GTY',
        'Tanpa Identitas;;;;;Guru;GTT', // invalid: missing NUPTK/NIY/NIK/HP
    ]);

    $this->actingAs($this->tu)
        ->post(route('admin.integrations.dapodik.import'), [
            'type' => 'teachers',
            'file' => UploadedFile::fake()->createWithContent('ptk.csv', $teachersCsv),
            'create_accounts' => '1',
        ])->assertRedirect(route('admin.integrations.dapodik', ['type' => 'teachers']))
        ->assertSessionHas('batch_credentials_token');

    expect(session('import_result')['created'])->toBe(1)
        ->and(session('import_result')['failed_count'])->toBe(1);

    $emp = Employee::where('nuptk', '1234567890123456')->firstOrFail();
    expect($emp->employee_number)->toBe('NIY-901')
        ->and($emp->nik)->toBe('3374010101800001')
        ->and($emp->user->must_change_password)->toBeTrue();

    // 3. Import Students CSV (2 siblings sharing parent phone + 1 row with unknown classroom)
    $studentsCsv = implode("\n", [
        'Nama Lengkap,NIS,NISN,NIK,JK,Tempat Lahir,Tanggal Lahir,Nama Ibu Kandung,Rombel Saat Ini,Nama Wali,No HP Ortu',
        'Aisyah Putri,260501,0012340501,3374010101100001,P,Semarang,2010-04-12,Siti Rahmah,X-1,Bapak Rahmat,081377770000',
        'Fauzan Putra,260502,0012340502,3374010101090002,L,Semarang,2009-05-20,Siti Rahmah,XI-MIPA-1,Bapak Rahmat,+6281377770000',
        'Siswa Salah Kelas,260503,0012340503,3374010101100003,L,Demak,2010-01-01,Ibu Ani,XII-TIDAK-ADA,Pak Ali,081388889999',
    ]);

    $response = $this->actingAs($this->tu)
        ->post(route('admin.integrations.dapodik.import'), [
            'type' => 'students',
            'file' => UploadedFile::fake()->createWithContent('peserta_didik.csv', $studentsCsv),
            'create_accounts' => '1',
        ]);

    $response->assertRedirect(route('admin.integrations.dapodik', ['type' => 'students']));
    $importSummary = session('import_result');

    expect($importSummary['created'])->toBe(2)
        ->and($importSummary['failed_count'])->toBe(1)
        ->and($importSummary['failed_rows'][0]['identifier'])->toContain('Siswa Salah Kelas');

    $s1 = Student::where('nisn', '0012340501')->firstOrFail();
    $s2 = Student::where('nisn', '0012340502')->firstOrFail();

    expect($s1->nik)->toBe('3374010101100001')
        ->and($s1->mother_name)->toBe('Siti Rahmah')
        ->and($s1->parent_id)->not->toBeNull()
        ->and($s1->parent_id)->toBe($s2->parent_id)
        ->and(ClassEnrollment::where('student_id', $s1->id)->where('school_year_id', $this->schoolYear->id)->exists())->toBeTrue();

    // Download batch credentials PDF using the session token
    $batchToken = (string) session('batch_credentials_token');
    expect($batchToken)->not->toBeEmpty();

    $this->actingAs($this->tu)
        ->get(route('users.import-credentials.download', $batchToken))
        ->assertOk();

    // Re-importing the same CSV is idempotent (updates instead of duplicating)
    $this->actingAs($this->tu)
        ->post(route('admin.integrations.dapodik.import'), [
            'type' => 'students',
            'file' => UploadedFile::fake()->createWithContent('peserta_didik.csv', $studentsCsv),
            'create_accounts' => '1',
        ]);

    expect(Student::whereIn('nisn', ['0012340501', '0012340502'])->count())->toBe(2)
        ->and(session('import_result')['updated'])->toBe(2)
        ->and(session('import_result')['created'])->toBe(0);
});

test('k4 direct contact update saves immediately without tu approval while identity change requires evidence, blocks self-approval, and populates dapodik sync queue', function () {
    $parent = User::factory()->create([
        'role' => 'parent',
        'name' => 'Wali Murid K4',
        'phone_number' => '081200001111',
        'email' => 'wali.lama@sulaone.test',
    ]);

    $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Nabila Az-Zahra']);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
        'nis' => '260701',
        'nisn' => '0011220701',
        'mother_name' => 'Siti Aisyah (Salah Eja)',
        'birth_place' => 'Semarang',
    ]);

    // 1. Direct contact update via API PATCH /api/v1/me/contact (immediate, no approval needed)
    Sanctum::actingAs($parent);
    $this->patchJson('/api/v1/me/contact', [
        'phone_number' => '+62 812-0000-2222',
        'email' => 'wali.baru@sulaone.test',
    ])->assertOk()
        ->assertJsonPath('data.phone_number', '081200002222')
        ->assertJsonPath('data.email', 'wali.baru@sulaone.test');

    $parent->refresh();
    expect($parent->phone_number)->toBe('081200002222')
        ->and($parent->email)->toBe('wali.baru@sulaone.test');

    // 2. Identity change request without evidence fails validation
    $this->postJson('/api/v1/profile-change-requests', [
        'target_type' => 'student',
        'target_id' => $student->id,
        'field_name' => 'mother_name',
        'new_value' => 'Hj. Siti Aisyah',
        'reason' => 'Sesuai Akta Kelahiran dan KK',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('evidence');

    // 3. Submit identity change request with KK evidence
    $submitRes = $this->postJson('/api/v1/profile-change-requests', [
        'target_type' => 'student',
        'target_id' => $student->id,
        'field_name' => 'mother_name',
        'new_value' => 'Hj. Siti Aisyah',
        'reason' => 'Sesuai Akta Kelahiran dan KK',
        'evidence' => UploadedFile::fake()->create('kk-keluarga.pdf', 200, 'application/pdf'),
    ])->assertCreated();

    $reqUuid = (string) $submitRes->json('data.uuid');
    $changeRequest = ProfileChangeRequest::where('uuid', $reqUuid)->firstOrFail();

    Storage::disk('local')->assertExists($changeRequest->evidence_path);

    // Unrelated student/parent cannot view evidence (IDOR check)
    $outsider = User::factory()->create(['role' => 'parent']);
    Sanctum::actingAs($outsider);
    $this->getJson("/api/v1/profile-change-requests/{$changeRequest->uuid}/evidence")
        ->assertForbidden();

    // Requester and TU can view evidence
    Sanctum::actingAs($parent);
    $this->get("/api/v1/profile-change-requests/{$changeRequest->uuid}/evidence")
        ->assertOk();

    // 4. Anti-self-approval: if TU submits an identity change for themselves, they cannot approve it
    Employee::create([
        'user_id' => $this->tu->id,
        'employee_number' => 'NIY-TU01',
        'nuptk' => '1111222233334444',
        'position' => 'Staf TU',
        'employment_status' => 'staf_tetap',
    ]);

    $this->actingAs($this->tu)->post(route('profile.change-requests.store'), [
        'target_type' => 'employee',
        'target_id' => $this->tu->employee->id,
        'field_name' => 'nik',
        'new_value' => '3374010101900009',
        'reason' => 'Perbaikan NIK KTP',
        'evidence' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
    ])->assertRedirect(route('profile.change-requests.index'));

    $tuOwnRequest = ProfileChangeRequest::where('requester_user_id', $this->tu->id)->firstOrFail();

    $this->actingAs($this->tu)
        ->post(route('admin.profile-change-requests.approve', $tuOwnRequest->uuid))
        ->assertForbidden();

    // Second TU staff can approve both requests (or bulk approve)
    $this->actingAs($this->tu2)
        ->post(route('admin.profile-change-requests.bulk-approve'), [
            'request_ids' => [$changeRequest->id, $tuOwnRequest->id],
            'review_notes' => 'Dokumen KK & KTP valid',
        ])->assertRedirect();

    $student->refresh();
    $this->tu->employee->refresh();

    expect($student->mother_name)->toBe('Hj. Siti Aisyah')
        ->and($this->tu->employee->nik)->toBe('3374010101900009');

    // 5. Both approved identity updates automatically entered the Dapodik Sync Queue (K1)
    $syncItems = DapodikSyncItem::where('status', DapodikSyncItem::STATUS_PENDING)->get();
    expect($syncItems)->toHaveCount(2);

    $studentSyncItem = $syncItems->firstWhere('profile_change_request_id', $changeRequest->id);
    expect($studentSyncItem)->not->toBeNull()
        ->and($studentSyncItem->dapodik_column_label)->toBe('Nama Ibu Kandung')
        ->and($studentSyncItem->new_value)->toBe('Hj. Siti Aisyah');

    // Operator marks one item as synced in Dapodik and dismisses the other
    $this->actingAs($this->tu)
        ->post(route('admin.integrations.dapodik.sync-queue.mark', $studentSyncItem->id), [
            'notes' => 'Sudah disalin ke aplikasi Dapodik lokal',
        ])->assertRedirect();

    expect($studentSyncItem->fresh()->status)->toBe(DapodikSyncItem::STATUS_SYNCED)
        ->and($studentSyncItem->fresh()->synced_by)->toBe($this->tu->id);
});
