<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TahfidzTarget;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/*
 * The 360 profile used to answer for any user: one without a student record
 * got a profile named "Siswa" with an average of 0.0, rank 0, "0 dari 0 juz",
 * 0% jamaah and discipline "Aman". Now a missing record is a 404 and a figure
 * with nothing behind it is null.
 */

beforeEach(function () {
    Cache::forget('academic_year_current');
    $this->year = AcademicYear::factory()->create(['year' => '2026/2027', 'is_active' => true]);
    Cache::forget('academic_year_current');
    $this->classroom = Classroom::factory()->create(['academic_year' => '2026/2027']);
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->record = Student::factory()->create(['user_id' => $this->student->id, 'classroom_id' => $this->classroom->id]);
});

test('student can view comprehensive 360 profile', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me');
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'biodata',
                'akademik_summary',
                'ibadah_summary',
                'disiplin_summary',
                'ekskul_list',
                'prestasi_list',
                'kesehatan_summary',
            ],
        ]);
});

test('student can view academic and ibadah breakdown', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me/academic');
    $response->assertStatus(200)->assertJson(['success' => true]);

    $resIbadah = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me/ibadah');
    $resIbadah->assertStatus(200)->assertJson(['success' => true]);
});

test('an account without a student record gets 404, not a made-up profile', function () {
    $orphan = User::factory()->create(['role' => 'siswa', 'must_change_password' => false]);

    $this->actingAs($orphan)->getJson('/api/v1/profile/student/me')
        ->assertNotFound()
        ->assertJsonPath('message', 'Data siswa untuk akun ini belum tercatat. Hubungi tata usaha sekolah.');
    $this->actingAs($orphan)->getJson('/api/v1/profile/student/me/academic')->assertNotFound();
});

test('nothing recorded yet is null, not zero', function () {
    $data = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me')->assertOk()->json('data');

    expect($data['akademik_summary']['average_score'])->toBeNull()
        ->and($data['akademik_summary']['rank_in_class'])->toBeNull()
        ->and($data['akademik_summary']['total_class_students'])->toBe(1)
        ->and($data['ibadah_summary']['target_juz'])->toBeNull()
        ->and($data['ibadah_summary']['tahfidz_juz_completed'])->toBeNull()
        ->and($data['ibadah_summary']['tahfidz_progress_percent'])->toBeNull()
        ->and($data['ibadah_summary']['sholat_jamaah_percent'])->toBeNull()
        // A real zero: no Mutaba'ah logged today means a streak of 0 days.
        ->and($data['ibadah_summary']['mutabaah_weekly_streak'])->toBe(0);
});

test('recorded grades and tahfidz targets are reported as they are', function () {
    $subject = Subject::factory()->create();
    $classmate = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    foreach ([[$this->record, 80], [$classmate, 90]] as [$who, $score]) {
        Grade::create([
            'student_id' => $who->id, 'subject_id' => $subject->id, 'academic_year_id' => $this->year->id,
            'type' => 'uh', 'score' => $score, 'recorded_by' => User::factory()->create(['role' => 'guru'])->id,
        ]);
    }
    TahfidzTarget::factory()->create([
        'student_id' => $this->record->id, 'academic_year_id' => $this->year->id, 'target_juz' => 4, 'achieved_juz' => 1,
    ]);

    $data = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me')->assertOk()->json('data');

    expect((float) $data['akademik_summary']['average_score'])->toBe(80.0)
        ->and($data['akademik_summary']['rank_in_class'])->toBe(2)
        ->and($data['akademik_summary']['total_class_students'])->toBe(2)
        ->and($data['ibadah_summary']['target_juz'])->toBe(4)
        ->and($data['ibadah_summary']['tahfidz_juz_completed'])->toBe(1)
        ->and($data['ibadah_summary']['tahfidz_progress_percent'])->toBe(25);
});

test('a parent of another family cannot open this profile', function () {
    $stranger = User::factory()->create(['role' => 'orang_tua', 'must_change_password' => false]);

    $this->actingAs($stranger)->getJson("/api/v1/profile/student/{$this->student->id}")->assertForbidden();
});

test('staff asking for a user who is not a student get 404', function () {
    $staff = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $notStudent = User::factory()->create(['role' => 'guru']);

    $this->actingAs($staff)->getJson("/api/v1/profile/student/{$notStudent->id}")->assertNotFound();
});
