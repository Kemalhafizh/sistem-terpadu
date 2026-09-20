<?php

use App\Jobs\SendLibraryDueReminderJob;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // 1. Setup Academic Year & Class
    $this->academicYear = AcademicYear::firstOrCreate(
        ['year' => '2026/2027', 'semester' => 'Ganjil'],
        ['is_active' => true]
    );

    $this->classroom = Classroom::factory()->create(['grade_level' => 'XII']);

    // 2. Setup Roles & Users
    $this->adminUser = User::factory()->create(['role' => 'admin']);
    $this->guruUser = User::factory()->create(['role' => 'guru']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '108' . rand(1000, 9999),
    ]);

    // 3. Setup Categories
    $this->categorySains = LibraryCategory::firstOrCreate(
        ['name' => 'Sains & Teknologi'],
        ['icon' => 'atom']
    );

    $this->categoryAgama = LibraryCategory::firstOrCreate(
        ['name' => 'Keislaman & Tarbiyah'],
        ['icon' => 'mosque']
    );

    // 4. Setup Books
    $this->bookFisika = LibraryBook::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Fisika Dasar untuk Universitas & Persiapan UTBK',
        'author' => 'Halliday, Resnick, Walker',
        'isbn' => '978-602-1234-56-1',
        'barcode' => '9786021234561',
        'shelf_location' => 'Rak A-02 (Lantai 1)',
        'category_id' => $this->categorySains->id,
        'type' => 'physical',
        'description' => 'Referensi lengkap teori mekanika dan gelombang.',
        'is_physical_available' => true,
        'total_copies' => 5,
        'available_copies' => 5,
        'rating' => 4.90,
        'created_by' => $this->adminUser->id,
    ]);

    $this->bookTafsir = LibraryBook::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Tafsir Al-Azhar: Surat Al-Baqarah',
        'author' => 'Buya Hamka',
        'isbn' => '978-979-418-091-2',
        'barcode' => '9789794180912',
        'shelf_location' => 'Rak B-01 (Koleksi Khusus Islami)',
        'category_id' => $this->categoryAgama->id,
        'type' => 'physical',
        'description' => 'Karya tafsir Al-Quran Buya Hamka.',
        'is_physical_available' => true,
        'total_copies' => 3,
        'available_copies' => 3,
        'rating' => 5.00,
        'created_by' => $this->adminUser->id,
    ]);
});

// ─────────────────────────────────────────────────────────────────────────────
// 1. TEST ENDPOINT AUTH & ROLE ACCESS CONTROL
// ─────────────────────────────────────────────────────────────────────────────

test('tamu tanpa autentikasi ditolak dengan kode 401 saat mengakses API perpustakaan', function () {
    $response = $this->getJson('/api/v1/library/books');
    $response->assertStatus(401);
});

test('peran parent ditolak dengan kode 403 saat mengakses API perpustakaan mobile', function () {
    Sanctum::actingAs($this->parentUser);

    $response = $this->getJson('/api/v1/library/books');
    $response->assertStatus(403);
});

test('peran siswa dan guru diizinkan mengakses katalog buku mobile', function () {
    // Siswa
    Sanctum::actingAs($this->studentUser);
    $resStudent = $this->getJson('/api/v1/library/books');
    $resStudent->assertStatus(200);

    // Guru
    Sanctum::actingAs($this->guruUser);
    $resGuru = $this->getJson('/api/v1/library/books');
    $resGuru->assertStatus(200);
});

// ─────────────────────────────────────────────────────────────────────────────
// 2. TEST SELF-CHECKOUT (STOK TERSEDIA, HABIS, KUOTA MAKSIMAL, QUEUE H-1)
// ─────────────────────────────────────────────────────────────────────────────

test('siswa dapat melakukan self-checkout buku fisik via scan barcode QR', function () {
    Queue::fake();
    Sanctum::actingAs($this->studentUser);

    $initialCopies = $this->bookFisika->available_copies;

    $response = $this->postJson('/api/v1/library/self-checkout', [
        'book_qr_code' => $this->bookFisika->barcode,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'book_id',
            'book_title',
            'borrow_date',
            'due_date',
            'days_remaining',
            'status',
            'fine_amount',
        ])
        ->assertJson([
            'book_id' => $this->bookFisika->id,
            'book_title' => $this->bookFisika->title,
            'status' => 'ACTIVE',
            'fine_amount' => 0,
        ]);

    // Verifikasi durasi 14 hari
    $expectedDue = now()->addDays(14)->format('Y-m-d');
    expect($response->json('due_date'))->toBe($expectedDue);

    // Verifikasi stok buku berkurang
    $this->bookFisika->refresh();
    expect($this->bookFisika->available_copies)->toBe($initialCopies - 1);

    // Verifikasi reminder queue job H-1 jatuh tempo ter-dispatch
    Queue::assertPushed(SendLibraryDueReminderJob::class, function ($job) use ($response) {
        return $job->loanId === $response->json('id');
    });
});

test('self-checkout gagal jika stok buku habis (stok 0)', function () {
    Sanctum::actingAs($this->studentUser);

    // Set buku habis
    $this->bookFisika->update([
        'available_copies' => 0,
        'is_physical_available' => false,
    ]);

    $response = $this->postJson('/api/v1/library/self-checkout', [
        'book_qr_code' => $this->bookFisika->isbn,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'Gagal melakukan peminjaman.',
        ]);
});

test('self-checkout gagal jika siswa telah mencapai batas kuota peminjaman 3 buku aktif', function () {
    Sanctum::actingAs($this->studentUser);

    // Buat 3 peminjaman aktif sebelumnya
    for ($i = 1; $i <= 3; $i++) {
        $dummyBook = LibraryBook::create([
            'uuid' => (string) Str::uuid(),
            'title' => "Buku Tes {$i}",
            'category_id' => $this->categorySains->id,
            'type' => 'physical',
            'is_physical_available' => false,
            'total_copies' => 1,
            'available_copies' => 0,
            'created_by' => $this->adminUser->id,
        ]);

        LibraryLoan::create([
            'uuid' => (string) Str::uuid(),
            'student_id' => $this->student->id,
            'library_book_id' => $dummyBook->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(14),
            'status' => 'borrowed',
            'fine_amount' => 0,
            'created_by' => $this->studentUser->id,
        ]);
    }

    // Mencoba pinjam buku ke-4
    $response = $this->postJson('/api/v1/library/self-checkout', [
        'book_qr_code' => $this->bookFisika->barcode,
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment([
            'error' => 'Gagal melakukan peminjaman.',
        ]);
});

test('self-checkout gagal jika siswa sedang meminjam buku yang sama', function () {
    Sanctum::actingAs($this->studentUser);

    // Pinjam buku pertama kali
    $res1 = $this->postJson('/api/v1/library/self-checkout', [
        'book_qr_code' => $this->bookFisika->barcode,
    ]);
    $res1->assertStatus(201);

    // Pinjam buku yang sama kedua kali
    $res2 = $this->postJson('/api/v1/library/self-checkout', [
        'book_qr_code' => $this->bookFisika->barcode,
    ]);
    $res2->assertStatus(422);
});

// ─────────────────────────────────────────────────────────────────────────────
// 3. TEST KATALOG BUKU, FILTER PENCARIAN, FILTER KATEGORI, PAGINASI, DETAIL
// ─────────────────────────────────────────────────────────────────────────────

test('katalog buku mengembalikan JSON array langsung yang kompatibel dengan Retrofit Android', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/library/books');

    $response->assertStatus(200);
    $books = $response->json();

    // Harus array root langsung
    expect(is_array($books))->toBeTrue();
    expect(count($books))->toBeGreaterThanOrEqual(2);

    $firstBook = $books[0];
    expect($firstBook)->toHaveKeys([
        'id',
        'title',
        'author',
        'isbn',
        'category',
        'shelf_location',
        'synopsis',
        'available_copies',
        'total_copies',
        'rating',
        'is_available',
    ]);
});

test('pencarian buku dengan parameter query bekerja secara akurat', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/library/books?query=Hamka');
    $response->assertStatus(200);

    $books = $response->json();
    expect(count($books))->toBe(1);
    expect($books[0]['title'])->toContain('Tafsir Al-Azhar');
});

test('filter buku berdasarkan nama kategori bekerja dengan benar', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/library/books?category=Sains%20%26%20Teknologi');
    $response->assertStatus(200);

    $books = $response->json();
    expect(count($books))->toBe(1);
    expect($books[0]['category'])->toBe('Sains & Teknologi');
});

test('katalog mendukung mode paginasi saat parameter paginated dikirim', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/library/books?paginated=true&per_page=1');
    $response->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data',
            'total',
            'per_page',
        ]);

    expect(count($response->json('data')))->toBe(1);
});

test('detail buku mengembalikan detail BookItem lengkap dan 404 jika tidak ditemukan', function () {
    Sanctum::actingAs($this->studentUser);

    // Buku ada
    $resFound = $this->getJson("/api/v1/library/books/{$this->bookFisika->id}");
    $resFound->assertStatus(200)
        ->assertJson([
            'id' => $this->bookFisika->id,
            'title' => $this->bookFisika->title,
            'isbn' => $this->bookFisika->isbn,
        ]);

    // Buku tidak ada
    $resNotFound = $this->getJson('/api/v1/library/books/999999');
    $resNotFound->assertStatus(404);
});

test('endpoint my-loans mengembalikan daftar peminjaman aktif siswa', function () {
    Sanctum::actingAs($this->studentUser);

    // Lakukan peminjaman
    $this->postJson('/api/v1/library/self-checkout', [
        'book_qr_code' => $this->bookFisika->barcode,
    ])->assertStatus(201);

    $response = $this->getJson('/api/v1/library/my-loans');
    $response->assertStatus(200);

    $loans = $response->json();
    expect(count($loans))->toBe(1);
    expect($loans[0]['book_id'])->toBe($this->bookFisika->id);
    expect($loans[0]['status'])->toBe('ACTIVE');
    expect($loans[0]['days_remaining'])->toBe(14);
});
