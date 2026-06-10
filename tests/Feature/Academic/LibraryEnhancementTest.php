<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    // 1. Setup Users
    $this->guru = User::factory()->create(['role' => 'guru', 'phone_number' => '08123456789']);
    $this->guruLain = User::factory()->create(['role' => 'guru']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'phone_number' => '08987654321']);
    $this->parentUser = User::factory()->create(['role' => 'parent', 'phone_number' => '08112223334']);
    
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'parent_id' => $this->parentUser->id,
    ]);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);
    $this->student->update(['classroom_id' => $this->classroom->id]);

    // 3. Create Library Category
    $this->category = LibraryCategory::factory()->create([
        'name' => 'Fisika',
        'icon' => 'book'
    ]);

    // 4. Create Library Books
    $this->bookPhysical = LibraryBook::factory()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'title' => 'Fisika Dasar I',
        'author' => 'Halliday & Resnick',
        'category_id' => $this->category->id,
        'type' => 'physical',
        'is_physical_available' => true,
        'created_by' => $this->guru->id,
    ]);

    $this->bookDigital = LibraryBook::factory()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'title' => 'Pengenalan Quantum Physics',
        'author' => 'Stephen Hawking',
        'category_id' => $this->category->id,
        'type' => 'digital',
        'file_path' => 'library/files/quantum.pdf',
        'created_by' => $this->guru->id,
    ]);
});

// =========================================================
// 📊 STATISTICS DASHBOARD TESTS
// =========================================================

test('authorized users can view library statistics', function () {
    $response = $this->actingAs($this->guru)
        ->get(route('library.statistics'));

    $response->assertStatus(200);
    $response->assertViewHas('stats');
});

test('students cannot view library statistics', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('library.statistics'));

    $response->assertStatus(403);
});

// =========================================================
// 📖 READING HISTORY TESTS
// =========================================================

test('students can view their own reading history', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('library.reading-history'));

    $response->assertStatus(200);
    $response->assertViewHas('readingStats');
});

test('students cannot view other students reading history', function () {
    $anotherStudentUser = User::factory()->create(['role' => 'student']);
    $anotherStudent = Student::factory()->create(['user_id' => $anotherStudentUser->id]);

    $response = $this->actingAs($this->studentUser)
        ->get(route('library.reading-history.detail', $anotherStudent));

    $response->assertStatus(403);
});

test('staff can view any student reading history', function () {
    $response = $this->actingAs($this->guru)
        ->get(route('library.reading-history.detail', $this->student));

    $response->assertStatus(200);
    $response->assertViewHas('readingStats');
});

// =========================================================
// 💡 RECOMMENDATIONS TESTS
// =========================================================

test('students can view book recommendations', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('library.recommendations'));

    $response->assertStatus(200);
    $response->assertViewHas('recommendedBooks');
});

// =========================================================
// ⏳ OVERDUE REPORT & WHATSAPP REMINDER TESTS
// =========================================================

test('authorized users can view overdue report', function () {
    $response = $this->actingAs($this->guru)
        ->get(route('library.overdue-report'));

    $response->assertStatus(200);
    $response->assertViewHas('overdueLoans');
});

test('students cannot view overdue report', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('library.overdue-report'));

    $response->assertStatus(403);
});

test('authorized users can send overdue reminder via WhatsApp', function () {
    // Create an overdue loan
    $loan = LibraryLoan::factory()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'student_id' => $this->student->id,
        'library_book_id' => $this->bookPhysical->id,
        'borrowed_at' => now()->subDays(10),
        'due_at' => now()->subDays(3),
        'status' => 'overdue',
        'fine_amount' => 3000,
        'created_by' => $this->guru->id,
    ]);

    $response = $this->actingAs($this->guru)
        ->post(route('library.loans.remind', $loan));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Assert a WhatsAppLog was created for the parent
    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '628112223334', // normalized parent phone
        'type' => 'library_overdue_reminder',
        'status' => 'sent',
    ]);
});
