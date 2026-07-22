<?php

use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->librarian = User::factory()->create(['role' => 'staf_tu']);

    $studentUser = User::factory()->create(['name' => 'Ahmad Dahlan']);
    $this->student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'nis' => '2026999888',
    ]);

    $category = LibraryCategory::create([
        'name' => 'Sains & Teknologi',
        'icon' => 'book',
    ]);

    $this->book = LibraryBook::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Fisika Kuantum Praktis',
        'author' => 'Dr. Habibie',
        'category_id' => $category->id,
        'type' => 'physical',
        'barcode' => 'BK-999000111',
        'nfc_uid' => 'NFC-99-00-11',
        'is_physical_available' => true,
        'created_by' => $this->admin->id,
    ]);
});

test('librarian can access quick circulation terminal view', function () {
    $response = $this->actingAs($this->librarian)->get(route('library.circulation.quick-terminal'));
    $response->assertStatus(200);
    $response->assertSee('Terminal Sirkulasi Barcode');
    $response->assertSee('Mode Peminjaman (Borrow)');
});

test('scan api supports book lookup by barcode or nfc uid', function () {
    // Lookup by barcode
    $responseBarcode = $this->actingAs($this->librarian)->post(route('library.circulation.scan'), [
        'code' => 'BK-999000111',
    ]);
    $responseBarcode->assertStatus(200);
    $responseBarcode->assertJson([
        'type' => 'book',
        'title' => 'Fisika Kuantum Praktis',
        'is_available' => true,
    ]);

    // Lookup by NFC UID
    $responseNfc = $this->actingAs($this->librarian)->post(route('library.circulation.scan'), [
        'code' => 'NFC-99-00-11',
    ]);
    $responseNfc->assertStatus(200);
    $responseNfc->assertJson([
        'type' => 'book',
        'title' => 'Fisika Kuantum Praktis',
    ]);
});

test('librarian can process instant barcode borrow via ajax', function () {
    $response = $this->actingAs($this->librarian)->post(route('library.circulation.barcode-borrow'), [
        'student_code' => '2026999888',
        'book_code' => 'BK-999000111',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'student_name' => 'Ahmad Dahlan',
        'book_title' => 'Fisika Kuantum Praktis',
    ]);

    $this->assertDatabaseHas('library_loans', [
        'student_id' => $this->student->id,
        'library_book_id' => $this->book->id,
    ]);

    // Book should now not be available for second borrow
    $this->book->refresh();
    expect($this->book->isAvailableForBorrow())->toBeFalse();
});

test('librarian can process instant barcode return via ajax', function () {
    // First create a loan
    $loan = LibraryLoan::create([
        'student_id' => $this->student->id,
        'library_book_id' => $this->book->id,
        'borrowed_at' => now()->subDays(3),
        'due_at' => now()->addDays(4),
        'created_by' => $this->librarian->id,
    ]);

    $response = $this->actingAs($this->librarian)->post(route('library.circulation.barcode-return'), [
        'book_code' => 'BK-999000111',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'book_title' => 'Fisika Kuantum Praktis',
        'fine' => 0,
    ]);

    $loan->refresh();
    expect($loan->returned_at)->not->toBeNull();

    // Book should be available again
    $this->book->refresh();
    expect($this->book->isAvailableForBorrow())->toBeTrue();
});

test('librarian can access barcode printable page', function () {
    $response = $this->actingAs($this->librarian)->get(route('library.books.print-barcodes'));
    $response->assertStatus(200);
    $response->assertSee('Fisika Kuantum Praktis');
    $response->assertSee('BK-999000111');
});
