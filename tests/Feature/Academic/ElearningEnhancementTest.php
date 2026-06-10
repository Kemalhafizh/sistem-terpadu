<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ElearningClass;
use App\Models\ElearningMaterial;
use App\Models\ElearningAssignment;
use App\Models\ElearningAnnouncement;
use App\Models\ElearningMaterialComment;
use App\Models\ElearningMaterialView;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ElearningService;
use Tests\TestCase;

beforeEach(function () {
    // 1. Setup Users
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Pak Budi']);
    $this->guruLain = User::factory()->create(['role' => 'guru', 'name' => 'Bu Ani']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
    ]);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);
    
    // Explicitly set classroom for the student to avoid factory collision
    $this->student->update(['classroom_id' => $this->classroom->id]);
    
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);

    // 3. Create E-Learning Class
    $this->elearningClass = ElearningClass::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'name' => 'Fisika Kelas X-A',
        'description' => 'Kelas Fisika Virtual',
        'is_archived' => false,
    ]);

    // 4. Create Material
    $this->material = ElearningMaterial::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'elearning_class_id' => $this->elearningClass->id,
        'title' => 'Materi Gerak Lurus',
        'description' => 'Pembahasan Gerak Lurus Beraturan',
    ]);

    // 5. Create Assignment
    $this->assignment = ElearningAssignment::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'elearning_class_id' => $this->elearningClass->id,
        'title' => 'Tugas Gerak Lurus',
        'description' => 'Kerjakan soal 1-5',
        'due_at' => now()->addDays(2),
        'allow_late' => true,
        'is_secure_mode' => false,
    ]);
});

// =========================================================
// 📣 ANNOUNCEMENT TESTS
// =========================================================

test('teacher can post announcement to elearning class', function () {
    $response = $this->actingAs($this->guru)
        ->post(route('elearning.announcement.store', $this->elearningClass->uuid), [
            'content' => 'Pengumuman: Besok kuis mendadak ya.',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('elearning_announcements', [
        'elearning_class_id' => $this->elearningClass->id,
        'user_id' => $this->guru->id,
        'content' => 'Pengumuman: Besok kuis mendadak ya.',
    ]);
});

test('unauthorized users cannot post announcement', function () {
    $response = $this->actingAs($this->studentUser)
        ->post(route('elearning.announcement.store', $this->elearningClass->uuid), [
            'content' => 'Siswa mencoba posting pengumuman.',
        ]);

    // Role check middleware handles this and returns 403
    $response->assertStatus(403);
});

test('teacher can delete their announcement', function () {
    $announcement = ElearningAnnouncement::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'elearning_class_id' => $this->elearningClass->id,
        'user_id' => $this->guru->id,
        'content' => 'Pengumuman lama.',
    ]);

    $response = $this->actingAs($this->guru)
        ->delete(route('elearning.announcement.destroy', [$this->elearningClass->uuid, $announcement->uuid]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('elearning_announcements', ['id' => $announcement->id]);
});

// =========================================================
// 📖 MATERIAL VIEW LOGGING & DISCUSSION TESTS
// =========================================================

test('student viewing material detail triggers view logging', function () {
    $this->assertDatabaseMissing('elearning_material_views', [
        'student_id' => $this->student->id,
        'elearning_material_id' => $this->material->id,
    ]);

    $response = $this->actingAs($this->studentUser)
        ->get(route('elearning.material.show', [$this->elearningClass->uuid, $this->material->uuid]));

    $response->assertStatus(200);
    $this->assertDatabaseHas('elearning_material_views', [
        'student_id' => $this->student->id,
        'elearning_material_id' => $this->material->id,
    ]);
});

test('user can post discussion comment on learning material', function () {
    $response = $this->actingAs($this->studentUser)
        ->post(route('elearning.comment.store', $this->material->uuid), [
            'content' => 'Apakah rumus ini benar, Pak?',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('elearning_material_comments', [
        'elearning_material_id' => $this->material->id,
        'user_id' => $this->studentUser->id,
        'content' => 'Apakah rumus ini benar, Pak?',
    ]);
});

test('user can delete their own comment, and teacher can delete any comment', function () {
    // 1. Student creates comment
    $comment = ElearningMaterialComment::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'elearning_material_id' => $this->material->id,
        'user_id' => $this->studentUser->id,
        'content' => 'Komentar siswa.',
    ]);

    // 2. Guru lain tries to delete it -> forbidden (403)
    $response = $this->actingAs($this->guruLain)
        ->delete(route('elearning.comment.destroy', [$this->material->uuid, $comment->uuid]));
    $response->assertStatus(403);

    // 3. Student deletes their own comment -> success
    $response = $this->actingAs($this->studentUser)
        ->delete(route('elearning.comment.destroy', [$this->material->uuid, $comment->uuid]));
    $response->assertRedirect();
    $this->assertDatabaseMissing('elearning_material_comments', ['id' => $comment->id]);

    // 4. Create another comment
    $comment2 = ElearningMaterialComment::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'elearning_material_id' => $this->material->id,
        'user_id' => $this->studentUser->id,
        'content' => 'Komentar siswa kedua.',
    ]);

    // 5. Guru (teacher of the class) deletes it -> success
    $response = $this->actingAs($this->guru)
        ->delete(route('elearning.comment.destroy', [$this->material->uuid, $comment2->uuid]));
    $response->assertRedirect();
    $this->assertDatabaseMissing('elearning_material_comments', ['id' => $comment2->id]);
});

// =========================================================
// 📈 PROGRESS TRACKING & STATISTICS TESTS
// =========================================================

test('teacher can view class progress and student checklist details', function () {
    // 1. View class progress overview
    $response = $this->actingAs($this->guru)
        ->get(route('elearning.class.progress', $this->elearningClass->uuid));
    $response->assertStatus(200);
    $response->assertViewHas('progressData');

    // 2. View specific student progress checklist
    $response = $this->actingAs($this->guru)
        ->get(route('elearning.class.student-progress', [$this->elearningClass->uuid, $this->student->uuid]));
    $response->assertStatus(200);
    $response->assertViewHas('progressData');
});

// =========================================================
// 📦 CLASS MANAGEMENT: DUPLICATION & ARCHIVING TESTS
// =========================================================

test('teacher can duplicate virtual classroom', function () {
    $response = $this->actingAs($this->guru)
        ->post(route('elearning.class.duplicate', $this->elearningClass->uuid));

    // Redirects to duplicated class detail page
    $response->assertRedirect();

    $duplicatedClass = ElearningClass::where('name', 'Salinan ' . $this->elearningClass->name)->first();
    $this->assertNotNull($duplicatedClass);
    
    // Verify cloned components count
    $this->assertEquals(1, $duplicatedClass->materials()->count());
    $this->assertEquals(1, $duplicatedClass->assignments()->count());
});

test('teacher can archive and unarchive virtual classroom', function () {
    // 1. Verify initially not archived
    $this->assertFalse($this->elearningClass->is_archived);

    // 2. Archive class
    $response = $this->actingAs($this->guru)
        ->post(route('elearning.class.archive', $this->elearningClass->uuid));
    
    $response->assertRedirect();
    $this->elearningClass->refresh();
    $this->assertTrue($this->elearningClass->is_archived);

    // 3. Unarchive class
    $response = $this->actingAs($this->guru)
        ->post(route('elearning.class.unarchive', $this->elearningClass->uuid));
    
    $response->assertRedirect();
    $this->elearningClass->refresh();
    $this->assertFalse($this->elearningClass->is_archived);
});
