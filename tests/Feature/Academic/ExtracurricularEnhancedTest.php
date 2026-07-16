<?php

/** @var TestCase|stdClass $this */

use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    Storage::fake('public');

    // 1. Setup Roles
    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Agus Admin']);
    $this->waka = User::factory()->create(['role' => 'waka_kesiswaan', 'name' => 'Koko Kesiswaan']);
    $this->guruAdvisor = User::factory()->create(['role' => 'guru', 'name' => 'Budi Advisor']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Ahmad Siswa']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2025/2026']);
    
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'status' => 'active'
    ]);

    // 3. Setup Extracurricular
    $this->ekskul = Extracurricular::create([
        'name' => 'Pramuka SMA ISA 1',
        'description' => 'Kegiatan kepramukaan wajib.',
        'advisor_id' => $this->guruAdvisor->id,
        'schedule_day' => 'Jumat',
        'schedule_time' => '15:00 - 17:00',
        'location' => 'Lapangan Utama',
        'max_members' => 50,
    ]);

    // Join Student to Extracurricular
    $this->member = ExtracurricularMember::create([
        'extracurricular_id' => $this->ekskul->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);
});

test('admin or waka can CRUD extracurriculars', function () {
    $this->actingAs($this->waka);

    // 1. Create
    $response = $this->post(route('ekskul.store'), [
        'name' => 'Paskibra SMA ISA 1',
        'description' => 'Pasukan Pengibar Bendera.',
        'advisor_id' => $this->guruAdvisor->id,
        'schedule_day' => 'Sabtu',
        'schedule_time' => '08:00 - 10:00',
        'location' => 'Lapangan Utama',
        'max_members' => 30,
    ]);

    $response->assertRedirect(route('ekskul.manage'));
    $this->assertDatabaseHas('extracurriculars', [
        'name' => 'Paskibra SMA ISA 1',
    ]);

    $paskibra = Extracurricular::where('name', 'Paskibra SMA ISA 1')->first();

    // 2. Edit/Update
    $updateResponse = $this->put(route('ekskul.update', $paskibra->id), [
        'name' => 'Paskibra Sultan Agung',
        'advisor_id' => $this->guruAdvisor->id,
        'schedule_day' => 'Sabtu',
        'schedule_time' => '08:00 - 11:00',
        'location' => 'Lapangan Utama',
        'max_members' => 40,
    ]);

    $updateResponse->assertRedirect(route('ekskul.manage'));
    $this->assertDatabaseHas('extracurriculars', [
        'id' => $paskibra->id,
        'name' => 'Paskibra Sultan Agung',
    ]);

    // 3. Delete
    $deleteResponse = $this->delete(route('ekskul.destroy', $paskibra->id));
    $deleteResponse->assertRedirect(route('ekskul.manage'));
    $this->assertDatabaseMissing('extracurriculars', [
        'id' => $paskibra->id,
    ]);
});

test('advisor can log achievements and input grading in batch', function () {
    $this->actingAs($this->guruAdvisor);

    // 1. Log Achievement
    $achResponse = $this->post(route('ekskul.achievements.store', $this->ekskul->id), [
        'title' => 'Juara 2 Lomba Futsal Kota',
        'level' => 'kota',
        'event_date' => '2026-02-15',
        'organizer' => 'KONI Semarang',
        'participants' => [$this->student->id],
        'notes' => 'Prestasi perdana.',
    ]);

    $achResponse->assertRedirect();
    $this->assertDatabaseHas('extracurricular_achievements', [
        'extracurricular_id' => $this->ekskul->id,
        'title' => 'Juara 2 Lomba Futsal Kota',
    ]);

    // 2. Batch Grading
    $gradeResponse = $this->post(route('ekskul.grading.store', $this->ekskul->id), [
        'grades' => [
            [
                'member_id' => $this->member->id,
                'grade' => 'A',
                'description' => 'Siswa sangat aktif mengikuti kegiatan.',
            ]
        ]
    ]);

    $gradeResponse->assertRedirect();
    $this->assertDatabaseHas('extracurricular_grades', [
        'extracurricular_member_id' => $this->member->id,
        'academic_year_id' => $this->academicYear->id,
        'grade' => 'A',
    ]);
});

test('pembina can access reports and export pdf', function () {
    $this->actingAs($this->guruAdvisor);

    $this->get(route('ekskul.attendance-report', $this->ekskul->id))->assertStatus(200);
    $this->get(route('ekskul.semester-report', $this->ekskul->id))->assertStatus(200);
    $this->get(route('ekskul.export-pdf', $this->ekskul->id))->assertStatus(200);
    $this->get(route('ekskul.schedule'))->assertStatus(200);
});

test('unauthorized roles are blocked from administrative panels', function () {
    $this->actingAs($this->studentUser);

    // Student should be blocked from create ekskul form
    $this->get(route('ekskul.create'))->assertStatus(403);
});
