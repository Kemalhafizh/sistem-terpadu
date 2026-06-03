<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonPlan;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    // 1. Setup Users
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Ahmad Guru']);
    $this->guruLain = User::factory()->create(['role' => 'guru', 'name' => 'Siti Guru']);
    $this->wakakur = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Bambang Wakakur']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Sumardi Kepsek']);
    $this->student = User::factory()->create(['role' => 'student']);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);

    // Associate subject with teachers
    $this->guru->subjects()->attach($this->subject->id);
    $this->guruLain->subjects()->attach($this->subject->id);
});

// =========================================================
// 📊 DASHBOARD & AUTHORIZATION TESTS
// =========================================================

test('dashboard is protected correctly per role (staff/teachers/admins can access, students cannot)', function () {
    // 1. Guru can access
    $response = $this->actingAs($this->guru)->get(route('curriculum.index'));
    $response->assertStatus(200);

    // 2. Waka Kurikulum can access
    $response = $this->actingAs($this->wakakur)->get(route('curriculum.index'));
    $response->assertStatus(200);

    // 3. Kepala Sekolah can access
    $response = $this->actingAs($this->kepsek)->get(route('curriculum.index'));
    $response->assertStatus(200);

    // 4. Student is forbidden
    $response = $this->actingAs($this->student)->get(route('curriculum.index'));
    $response->assertStatus(403);
});

test('downloading the global completion report is restricted to managers and leaders', function () {
    // 1. Waka Kurikulum can download
    $response = $this->actingAs($this->wakakur)->get(route('curriculum.report'));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');

    // 2. Kepala Sekolah can download
    $response = $this->actingAs($this->kepsek)->get(route('curriculum.report'));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');

    // 3. Guru is forbidden
    $response = $this->actingAs($this->guru)->get(route('curriculum.report'));
    $response->assertStatus(403);
});

// =========================================================
// 📘 SYLLABUS WORKFLOW TESTS
// =========================================================

test('teachers can upload a syllabus as draft and it enforces period uniqueness', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('silabus_fisika_x.pdf', 1000);

    // 1. Upload Syllabus
    $response = $this->actingAs($this->guru)->post(route('curriculum.syllabi.store'), [
        'subject_id' => $this->subject->id,
        'grade_level' => 'X',
        'semester' => 'ganjil',
        'file' => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('syllabi', [
        'subject_id' => $this->subject->id,
        'grade_level' => 'X',
        'semester' => 'ganjil',
        'uploaded_by' => $this->guru->id,
        'status' => 'draft',
    ]);

    // 2. Attempt Duplicate Upload (Unique constraint)
    $responseDuplicate = $this->actingAs($this->guru)->post(route('curriculum.syllabi.store'), [
        'subject_id' => $this->subject->id,
        'grade_level' => 'X',
        'semester' => 'ganjil',
        'file' => $file,
    ]);

    $responseDuplicate->assertRedirect();
    $responseDuplicate->assertSessionHas('error');
});

test('waka kurikulum can review a syllabus, but guru cannot', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('silabus_fisika_xi.pdf', 1000);

    $syllabus = Syllabus::create([
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'grade_level' => 'XI',
        'semester' => 'genap',
        'uploaded_by' => $this->guru->id,
        'file_path' => 'uploads/curriculum/syllabi/dummy.pdf',
        'status' => 'draft',
    ]);

    // 1. Guru attempts review -> forbidden
    $responseGuru = $this->actingAs($this->guru)->post(route('curriculum.syllabi.review', $syllabus->id), [
        'status' => 'approved',
        'notes' => 'Sesuai kurikulum K-13',
    ]);
    $responseGuru->assertStatus(403);

    // 2. Waka Kurikulum reviews -> success
    $responseWaka = $this->actingAs($this->wakakur)->post(route('curriculum.syllabi.review', $syllabus->id), [
        'status' => 'approved',
        'notes' => 'Sesuai kurikulum K-13',
    ]);
    $responseWaka->assertRedirect();
    $responseWaka->assertSessionHas('success');

    $this->assertDatabaseHas('syllabi', [
        'id' => $syllabus->id,
        'status' => 'approved',
        'notes' => 'Sesuai kurikulum K-13',
        'approved_by' => $this->wakakur->id,
    ]);
});

test('syllabus can be deleted by owner if draft, but not if approved', function () {
    $syllabusDraft = Syllabus::create([
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'grade_level' => 'X',
        'semester' => 'ganjil',
        'uploaded_by' => $this->guru->id,
        'file_path' => 'uploads/curriculum/syllabi/draft.pdf',
        'status' => 'draft',
    ]);

    $syllabusApproved = Syllabus::create([
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'grade_level' => 'XI',
        'semester' => 'ganjil',
        'uploaded_by' => $this->guru->id,
        'file_path' => 'uploads/curriculum/syllabi/approved.pdf',
        'status' => 'approved',
    ]);

    // 1. Guru deletes own draft -> success
    $responseDeleteDraft = $this->actingAs($this->guru)->delete(route('curriculum.syllabi.destroy', $syllabusDraft->id));
    $responseDeleteDraft->assertRedirect();
    $responseDeleteDraft->assertSessionHas('success');
    $this->assertDatabaseMissing('syllabi', ['id' => $syllabusDraft->id]);

    // 2. Guru attempts deleting own approved syllabus -> forbidden/error
    $responseDeleteApproved = $this->actingAs($this->guru)->delete(route('curriculum.syllabi.destroy', $syllabusApproved->id));
    $responseDeleteApproved->assertRedirect();
    $responseDeleteApproved->assertSessionHas('error');
    $this->assertDatabaseHas('syllabi', ['id' => $syllabusApproved->id]);
});

// =========================================================
// 📝 LESSON PLAN (RPP) WORKFLOW TESTS
// =========================================================

test('guru can CRUD RPP draft, view own RPP, but cannot view other guru RPP', function () {
    // 1. Create RPP Draft
    $responseCreate = $this->actingAs($this->guru)->post(route('curriculum.lesson-plans.store'), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'topic' => 'Besaran dan Satuan',
        'week_number' => 2,
        'competency_standards' => 'KI 1: Memahami konsep pengukuran',
        'learning_objectives' => 'Siswa dapat mengukur besaran fisika',
        'learning_activities' => 'Praktikum mengukur dengan jangka sorong',
        'assessment_method' => 'Tes tertulis & praktikum',
        'resources' => 'Buku paket kelas X',
    ]);

    $responseCreate->assertRedirect(route('curriculum.lesson-plans.index'));
    $responseCreate->assertSessionHas('success');

    $rpp = LessonPlan::where('topic', 'Besaran dan Satuan')->first();
    $this->assertNotNull($rpp);
    $this->assertEquals('draft', $rpp->status);

    // 2. Guru can view own RPP
    $responseViewSelf = $this->actingAs($this->guru)->get(route('curriculum.lesson-plans.show', $rpp->uuid));
    $responseViewSelf->assertStatus(200);
    $responseViewSelf->assertSee('Besaran dan Satuan');

    // 3. Guru cannot view other Guru's RPP
    $rppLain = LessonPlan::create([
        'user_id' => $this->guruLain->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'topic' => 'Kinematika Gerak',
        'learning_objectives' => 'Siswa memahami gerak lurus',
        'learning_activities' => 'Diskusi kelompok',
        'status' => 'draft',
    ]);

    $responseViewOther = $this->actingAs($this->guru)->get(route('curriculum.lesson-plans.show', $rppLain->uuid));
    $responseViewOther->assertStatus(403);

    // 4. Update RPP Draft
    $responseUpdate = $this->actingAs($this->guru)->put(route('curriculum.lesson-plans.update', $rpp->uuid), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'topic' => 'Besaran dan Satuan Edisi Revisi',
        'week_number' => 2,
        'learning_objectives' => 'Siswa dapat mengukur dengan jangka sorong secara mandiri',
        'learning_activities' => 'Praktikum mengukur mandiri',
    ]);
    $responseUpdate->assertRedirect(route('curriculum.lesson-plans.index'));
    $responseUpdate->assertSessionHas('success');

    $this->assertDatabaseHas('lesson_plans', [
        'id' => $rpp->id,
        'topic' => 'Besaran dan Satuan Edisi Revisi',
    ]);

    // 5. Delete RPP Draft
    $responseDelete = $this->actingAs($this->guru)->delete(route('curriculum.lesson-plans.destroy', $rpp->uuid));
    $responseDelete->assertRedirect(route('curriculum.lesson-plans.index'));
    $responseDelete->assertSessionHas('success');

    $this->assertSoftDeleted('lesson_plans', [
        'id' => $rpp->id,
    ]);
});

test('submitted RPP is locked from edits/deletes by guru, and waka kurikulum can review it', function () {
    $rpp = LessonPlan::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'topic' => 'Gaya Gravitasi',
        'learning_objectives' => 'Siswa memahami gravitasi newton',
        'learning_activities' => 'Siswa berdiskusi',
        'status' => 'draft',
    ]);

    // 1. Submit RPP
    $responseSubmit = $this->actingAs($this->guru)->post(route('curriculum.lesson-plans.submit', $rpp->uuid));
    $responseSubmit->assertRedirect();
    $responseSubmit->assertSessionHas('success');

    $rpp->refresh();
    $this->assertEquals('submitted', $rpp->status);

    // 2. Attempt Edit on Locked RPP -> forbidden
    $responseEdit = $this->actingAs($this->guru)->get(route('curriculum.lesson-plans.edit', $rpp->uuid));
    $responseEdit->assertStatus(403);

    $responseUpdate = $this->actingAs($this->guru)->put(route('curriculum.lesson-plans.update', $rpp->uuid), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'topic' => 'Gaya Gravitasi Baru',
        'learning_objectives' => 'Siswa memahami gravitasi newton',
        'learning_activities' => 'Siswa berdiskusi',
    ]);
    $responseUpdate->assertStatus(403);

    // 3. Attempt Delete on Locked RPP -> forbidden
    $responseDelete = $this->actingAs($this->guru)->delete(route('curriculum.lesson-plans.destroy', $rpp->uuid));
    $responseDelete->assertStatus(403);

    // 4. Waka reviews RPP -> Approve
    $responseReview = $this->actingAs($this->wakakur)->post(route('curriculum.lesson-plans.review', $rpp->uuid), [
        'status' => 'approved',
        'reviewer_notes' => 'Sangat bagus, aktivitas pembelajaran terstruktur.',
    ]);
    $responseReview->assertRedirect(route('curriculum.lesson-plans.show', $rpp->uuid));
    $responseReview->assertSessionHas('success');

    $rpp->refresh();
    $this->assertEquals('approved', $rpp->status);
    $this->assertEquals('Sangat bagus, aktivitas pembelajaran terstruktur.', $rpp->reviewer_notes);
    $this->assertEquals($this->wakakur->id, $rpp->reviewed_by);
});

test('waka kurikulum can reject RPP (revisions needed), which unlocks edit access for guru', function () {
    $rpp = LessonPlan::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'topic' => 'Hukum Termodinamika',
        'learning_objectives' => 'Siswa memahami konversi energi',
        'learning_activities' => 'Siswa berdiskusi kelompok',
        'status' => 'submitted',
    ]);

    // 1. Waka reviews RPP -> revision_needed
    $responseReview = $this->actingAs($this->wakakur)->post(route('curriculum.lesson-plans.review', $rpp->uuid), [
        'status' => 'revision_needed',
        'reviewer_notes' => 'Harap tambahkan metode penilaian yang lebih detail.',
    ]);
    $responseReview->assertRedirect(route('curriculum.lesson-plans.show', $rpp->uuid));

    $rpp->refresh();
    $this->assertEquals('revision_needed', $rpp->status);

    // 2. Guru can now edit the RPP again
    $responseEdit = $this->actingAs($this->guru)->get(route('curriculum.lesson-plans.edit', $rpp->uuid));
    $responseEdit->assertStatus(200);

    // Update resets status back to draft
    $responseUpdate = $this->actingAs($this->guru)->put(route('curriculum.lesson-plans.update', $rpp->uuid), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'topic' => 'Hukum Termodinamika (Revisi)',
        'week_number' => 5,
        'learning_objectives' => 'Siswa memahami konversi energi',
        'learning_activities' => 'Siswa berdiskusi kelompok & menulis laporan ilmiah',
        'assessment_method' => 'Rubrik laporan & presentasi',
    ]);
    $responseUpdate->assertRedirect(route('curriculum.lesson-plans.index'));

    $rpp->refresh();
    $this->assertEquals('draft', $rpp->status);
    $this->assertEquals('Hukum Termodinamika (Revisi)', $rpp->topic);
});

// =========================================================
// 📄 PDF RENDER & DOWNLOAD TESTS
// =========================================================

test('authorized users can download a single RPP as PDF', function () {
    $rpp = LessonPlan::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'topic' => 'Optika Geometri',
        'learning_objectives' => 'Siswa memahami pemantulan cahaya',
        'learning_activities' => 'Eksperimen pembiasan kaca planparalel',
        'status' => 'approved',
    ]);

    // 1. Guru can download own approved RPP
    $response = $this->actingAs($this->guru)->get(route('curriculum.lesson-plans.pdf', $rpp->uuid));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');

    // 2. Waka Kurikulum can download it too
    $responseWaka = $this->actingAs($this->wakakur)->get(route('curriculum.lesson-plans.pdf', $rpp->uuid));
    $responseWaka->assertStatus(200);
    $responseWaka->assertHeader('content-type', 'application/pdf');

    // 3. Student cannot download it
    $responseStudent = $this->actingAs($this->student)->get(route('curriculum.lesson-plans.pdf', $rpp->uuid));
    $responseStudent->assertStatus(403);
});
