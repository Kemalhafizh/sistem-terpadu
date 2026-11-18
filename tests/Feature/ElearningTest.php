<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    Storage::fake('public');
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();

    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    $this->subject = Subject::factory()->create();
});

test('pwa assets exist on disk', function () {
    $this->assertFileExists(public_path('manifest.json'));
    $this->get('/sw.js')->assertOk();
});

test('teacher can create virtual class', function () {
    $response = $this->actingAs($this->guru)->post(route('elearning.index'), [
        'name' => 'Fisika Modul X-A',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'description' => 'Kelas Virtual Fisika Termodinamika',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('elearning_classes', [
        'name' => 'Fisika Modul X-A',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
    ]);
});

test('teacher can add materials and assignments to virtual class', function () {
    $class = ElearningClass::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'name' => 'Fisika Modul X-A',
    ]);

    // Add Material
    $responseMaterial = $this->actingAs($this->guru)->post(route('elearning.material.store', $class->uuid), [
        'title' => 'Bab 1 Dinamika Rotasi',
        'description' => 'Materi tentang rotasi benda tegar',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);
    $responseMaterial->assertSessionHas('success');

    $this->assertDatabaseHas('elearning_materials', [
        'elearning_class_id' => $class->id,
        'title' => 'Bab 1 Dinamika Rotasi',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    // Add Assignment
    $responseAssignment = $this->actingAs($this->guru)->post(route('elearning.assignment.store', $class->uuid), [
        'title' => 'Kuis Rotasi Benda Tegar',
        'description' => 'Selesaikan soal ini dengan cermat.',
        'due_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'allow_late' => 1,
        'is_secure_mode' => 1,
        'max_violations' => 3,
    ]);
    $responseAssignment->assertSessionHas('success');

    $this->assertDatabaseHas('elearning_assignments', [
        'elearning_class_id' => $class->id,
        'title' => 'Kuis Rotasi Benda Tegar',
        'is_secure_mode' => true,
        'max_violations' => 3,
    ]);
});

test('student can view virtual class and submit assignment solution', function () {
    $class = ElearningClass::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'name' => 'Fisika Modul X-A',
    ]);

    $assignment = ElearningAssignment::create([
        'elearning_class_id' => $class->id,
        'title' => 'Tugas Mandiri',
        'description' => 'Kerjakan soal 1-5.',
        'due_at' => now()->addDays(2),
        'allow_late' => true,
        'is_secure_mode' => false,
        'max_violations' => 3,
    ]);

    // Student views the class
    $responseView = $this->actingAs($this->studentUser)->get(route('elearning.class.show', $class->uuid));
    $responseView->assertOk();

    // Student submits the assignment
    $file = UploadedFile::fake()->create('jawaban.pdf', 500); // 500 KB
    $responseSubmit = $this->actingAs($this->studentUser)->post(route('elearning.assignment.submit', $assignment->uuid), [
        'file' => $file,
    ]);

    $responseSubmit->assertSessionHas('success');

    $this->assertDatabaseHas('elearning_submissions', [
        'elearning_assignment_id' => $assignment->id,
        'student_id' => $this->student->id,
        'status' => 'submitted',
    ]);
});

test('visibility violations AJAX increment properly and lock student submission', function () {
    $class = ElearningClass::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'name' => 'Fisika Ujian Akhir',
    ]);

    $assignment = ElearningAssignment::create([
        'elearning_class_id' => $class->id,
        'title' => 'Ujian Akhir Semester',
        'description' => 'Ujian Akhir Semester Fisika.',
        'due_at' => now()->addDays(2),
        'allow_late' => false,
        'is_secure_mode' => true,
        'max_violations' => 2, // Lock out on 2nd violation
    ]);

    // 1st violation
    $response1 = $this->actingAs($this->studentUser)->post(route('elearning.assignment.violation', $assignment->uuid));
    $response1->assertOk();
    $response1->assertJson([
        'success' => true,
        'violation_count' => 1,
        'locked' => false,
    ]);

    $this->assertDatabaseHas('elearning_submissions', [
        'elearning_assignment_id' => $assignment->id,
        'student_id' => $this->student->id,
        'violation_count' => 1,
        'status' => 'submitted',
    ]);

    // 2nd violation -> Exceeds max_violations (2) -> Locks out!
    $response2 = $this->actingAs($this->studentUser)->post(route('elearning.assignment.violation', $assignment->uuid));
    $response2->assertOk();
    $response2->assertJson([
        'success' => true,
        'violation_count' => 2,
        'locked' => true,
    ]);

    $this->assertDatabaseHas('elearning_submissions', [
        'elearning_assignment_id' => $assignment->id,
        'student_id' => $this->student->id,
        'violation_count' => 2,
        'status' => 'locked_cheated',
    ]);
});

test('an admin cannot report exam violations as if they were a student', function () {
    $class = ElearningClass::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'name' => 'Ujian Penting',
    ]);

    $assignment = ElearningAssignment::create([
        'elearning_class_id' => $class->id,
        'title' => 'Ujian Susulan',
        'description' => 'Deskripsi.',
        'due_at' => now()->addDays(2),
        'allow_late' => false,
        'is_secure_mode' => true,
        'max_violations' => 3,
    ]);

    // The route is role:student; only superadmin bypasses role checks now.
    $this->actingAs($this->admin)->post(route('elearning.assignment.violation', $assignment->uuid))
        ->assertForbidden();
});
