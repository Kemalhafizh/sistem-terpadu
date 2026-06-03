<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\LibraryLoan;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

beforeEach(function () {
    // Buat data pengguna penguji
    $this->librarian = User::factory()->create(['role' => 'staf_tu']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->studentUser = User::factory()->create(['role' => 'student']);

    // Hubungkan studentUser dengan Student model
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'nis' => 'NIS-9999',
    ]);

    // Buat kategori buku
    $this->category = LibraryCategory::create([
        'name' => 'Sains & Teknologi',
        'icon' => 'academic-cap',
    ]);

    // Buat tahun ajaran aktif untuk pengujian Buku Paket Pelajaran
    $this->academicYear = AcademicYear::create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-01',
        'end_date' => '2027-06-30',
    ]);

    // Seting default denda dan maksimal buku pinjam
    Setting::set('library_fine_per_day', 1000);
    Setting::set('library_max_borrow_books', 3);

    // Inisialisasi Chart of Accounts (COA) untuk double-entry
    $this->kasAccount = Account::create([
        'code' => '1010',
        'name' => 'Kas & Setara Kas',
        'type' => 'asset',
        'normal_balance' => 'debit',
    ]);

    $this->pendapatanAccount = Account::create([
        'code' => '4040',
        'name' => 'Pendapatan Lain-lain',
        'type' => 'revenue',
        'normal_balance' => 'credit',
    ]);
});

test('siswa dibatasi meminjam maksimal 3 buku fisik secara bersamaan', function () {
    // 1. Buat 4 buku fisik yang tersedia
    $books = [];
    for ($i = 1; $i <= 4; $i++) {
        $books[] = LibraryBook::create([
            'uuid' => (string) Str::uuid(),
            'title' => "Buku Fisik Pengujian Ke-$i",
            'author' => 'Penulis Uji',
            'category_id' => $this->category->id,
            'type' => 'physical',
            'is_physical_available' => true,
            'created_by' => $this->librarian->id,
        ]);
    }

    // 2. Pinjam buku 1, 2, dan 3 - Harus Sukses (HTTP 200)
    for ($i = 0; $i < 3; $i++) {
        $response = $this->actingAs($this->librarian)->post(route('library.circulation.borrow'), [
            'student_id' => $this->student->id,
            'library_book_id' => $books[$i]->id,
            'due_date_type' => 'regular',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    // Pastikan database mencatat 3 peminjaman aktif
    expect($this->student->activeLoansCount())->toBe(3);

    // 3. Pinjam buku ke-4 - Harus Gagal (HTTP 400) karena mencapai limit
    $response = $this->actingAs($this->librarian)->post(route('library.circulation.borrow'), [
        'student_id' => $this->student->id,
        'library_book_id' => $books[3]->id,
        'due_date_type' => 'regular',
    ]);

    $response->assertStatus(400);
    $response->assertJsonPath('success', false);
    $response->assertJsonFragment([
        'message' => 'Siswa ini telah mencapai batas maksimal peminjaman (3 buku).',
    ]);

    // Kuota peminjaman aktif harus tetap 3
    expect($this->student->activeLoansCount())->toBe(3);
});

test('peminjaman dan pengembalian mengubah status ketersediaan buku fisik secara presisi', function () {
    // 1. Buat buku fisik baru
    $book = LibraryBook::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Buku Fisik Ketersediaan',
        'author' => 'Penulis Ketersediaan',
        'category_id' => $this->category->id,
        'type' => 'physical',
        'is_physical_available' => true,
        'created_by' => $this->librarian->id,
    ]);

    expect($book->isAvailableForBorrow())->toBeTrue();

    // 2. Lakukan peminjaman
    $response = $this->actingAs($this->librarian)->post(route('library.circulation.borrow'), [
        'student_id' => $this->student->id,
        'library_book_id' => $book->id,
        'due_date_type' => 'regular',
    ]);

    $response->assertStatus(200);

    // Status ketersediaan di database harus menjadi false
    $book->refresh();
    expect($book->is_physical_available)->toBeFalse();
    expect($book->isAvailableForBorrow())->toBeFalse();

    // Dapatkan data peminjaman
    $loan = LibraryLoan::where('student_id', $this->student->id)
        ->where('library_book_id', $book->id)
        ->whereNull('returned_at')
        ->first();

    expect($loan)->not->toBeNull();

    // 3. Lakukan pengembalian (Gunakan instans model $loan langsung agar Route Model Binding mencocokkan UUID)
    $returnResponse = $this->actingAs($this->librarian)->post(route('library.circulation.return', $loan));
    $returnResponse->assertStatus(200);

    // Status ketersediaan di database harus kembali menjadi true
    $book->refresh();
    expect($book->is_physical_available)->toBeTrue();
    expect($book->isAvailableForBorrow())->toBeTrue();
});

test('sistem mendukung fleksibilitas jatuh tempo reguler, buku paket pelajaran, dan kustom', function () {
    // Buat 3 buku fisik terpisah
    $bookReg = LibraryBook::create([
        'uuid' => (string) Str::uuid(), 'title' => 'Buku Reguler', 'category_id' => $this->category->id, 'type' => 'physical', 'created_by' => $this->librarian->id,
    ]);
    $bookPack = LibraryBook::create([
        'uuid' => (string) Str::uuid(), 'title' => 'Buku Paket', 'category_id' => $this->category->id, 'type' => 'physical', 'created_by' => $this->librarian->id,
    ]);
    $bookCust = LibraryBook::create([
        'uuid' => (string) Str::uuid(), 'title' => 'Buku Kustom', 'category_id' => $this->category->id, 'type' => 'physical', 'created_by' => $this->librarian->id,
    ]);

    // 1. Uji Jatuh Tempo Reguler (7 hari)
    $this->actingAs($this->librarian)->post(route('library.circulation.borrow'), [
        'student_id' => $this->student->id,
        'library_book_id' => $bookReg->id,
        'due_date_type' => 'regular',
    ]);
    $loanReg = LibraryLoan::where('library_book_id', $bookReg->id)->first();
    expect($loanReg->due_at->toDateString())->toBe(now()->addDays(7)->toDateString());

    // 2. Uji Jatuh Tempo Buku Paket Pelajaran (sampai akhir tahun ajaran)
    $this->actingAs($this->librarian)->post(route('library.circulation.borrow'), [
        'student_id' => $this->student->id,
        'library_book_id' => $bookPack->id,
        'due_date_type' => 'package',
    ]);
    $loanPack = LibraryLoan::where('library_book_id', $bookPack->id)->first();
    expect($loanPack->due_at->toDateString())->toBe('2027-06-30');

    // 3. Uji Jatuh Tempo Kustom (misal: tanggal pilihan petugas)
    $customDate = now()->addDays(15)->format('Y-m-d');
    $this->actingAs($this->librarian)->post(route('library.circulation.borrow'), [
        'student_id' => $this->student->id,
        'library_book_id' => $bookCust->id,
        'due_date_type' => 'custom',
        'custom_due_date' => $customDate,
    ]);
    $loanCust = LibraryLoan::where('library_book_id', $bookCust->id)->first();
    expect($loanCust->due_at->toDateString())->toBe($customDate);
});

test('perhitungan denda keterlambatan secara otomatis saat pengembalian terlambat', function () {
    // Bekukan waktu pengujian secara presisi untuk menghindari ketidakpastian milidetik
    $now = Carbon::parse('2026-05-20 12:00:00');
    Carbon::setTestNow($now);

    // 1. Buat buku fisik
    $book = LibraryBook::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Buku Terlambat',
        'category_id' => $this->category->id,
        'type' => 'physical',
        'is_physical_available' => false, // Set false karena ceritanya sedang dipinjam
        'created_by' => $this->librarian->id,
    ]);

    // 2. Buat data peminjaman di masa lalu secara langsung di database (due_at 5 hari yang lalu)
    $dueAt = $now->copy()->subDays(5);
    $borrowedAt = $now->copy()->subDays(12);

    $loan = LibraryLoan::create([
        'uuid' => (string) Str::uuid(),
        'student_id' => $this->student->id,
        'library_book_id' => $book->id,
        'borrowed_at' => $borrowedAt,
        'due_at' => $dueAt,
        'status' => 'borrowed',
        'fine_amount' => 0,
        'created_by' => $this->librarian->id,
    ]);

    // Verifikasi denda yang dihitung secara dinamis sebelum pengembalian
    // 5 hari terlambat * Rp 1.000 (fine per day) = Rp 5.000
    expect((int) $loan->calculateFine())->toBe(5000);

    // 3. Kembalikan buku lewat API pengembalian
    $response = $this->actingAs($this->librarian)->post(route('library.circulation.return', $loan));
    $response->assertStatus(200);

    $loan->refresh();

    // Status peminjaman setelah dikembalikan terlambat harus 'overdue' (karena memiliki denda belum lunas)
    expect($loan->status)->toBe('overdue');
    expect((int) $loan->fine_amount)->toBe(5000);
    expect($loan->returned_at)->not->toBeNull();
    expect($loan->fine_paid_at)->toBeNull();

    // Kembalikan waktu normal setelah pengujian
    Carbon::setTestNow();
});

test('pelunasan denda mencatat kas masuk otomatis pada sistem akuntansi double-entry', function () {
    $book = LibraryBook::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Buku Dengan Denda Lunas',
        'category_id' => $this->category->id,
        'type' => 'physical',
        'created_by' => $this->librarian->id,
    ]);

    // Buat pinjaman yang sudah dikembalikan tetapi memiliki denda belum dibayar
    $loan = LibraryLoan::create([
        'uuid' => (string) Str::uuid(),
        'student_id' => $this->student->id,
        'library_book_id' => $book->id,
        'borrowed_at' => now()->subDays(10),
        'due_at' => now()->subDays(5),
        'returned_at' => now(),
        'status' => 'overdue',
        'fine_amount' => 5000.00,
        'fine_paid_at' => null,
        'created_by' => $this->librarian->id,
    ]);

    // Pelunasan denda via API (Gunakan model $loan langsung)
    $response = $this->actingAs($this->librarian)->post(route('library.circulation.pay-fine', $loan));
    $response->assertStatus(200);

    $loan->refresh();

    // Status setelah denda lunas harus 'returned' dan fine_paid_at terisi
    expect($loan->status)->toBe('returned');
    expect($loan->fine_paid_at)->not->toBeNull();

    // Verifikasi Penjurnalan Akuntansi Double-Entry otomatis
    // Harus ada baris jurnal dengan referensi LBF-{loan_id}
    $journalEntry = JournalEntry::where('reference', 'LBF-'.$loan->id)->first();
    expect($journalEntry)->not->toBeNull();
    expect($journalEntry->description)->toContain($book->title);

    // Periksa detail baris jurnal (Debit Kas, Kredit Pendapatan)
    $lines = JournalEntryLine::where('journal_entry_id', $journalEntry->id)->get();
    expect($lines->count())->toBe(2);

    // Baris Debit Kas
    $debitLine = $lines->where('account_id', $this->kasAccount->id)->first();
    expect($debitLine)->not->toBeNull();
    expect((int) $debitLine->debit)->toBe(5000);
    expect((int) $debitLine->credit)->toBe(0);

    // Baris Kredit Pendapatan
    $creditLine = $lines->where('account_id', $this->pendapatanAccount->id)->first();
    expect($creditLine)->not->toBeNull();
    expect((int) $creditLine->debit)->toBe(0);
    expect((int) $creditLine->credit)->toBe(5000);
});

test('proteksi keamanan rute pembaca e-book terenkripsi dan pembatasan role sirkulasi perpustakaan', function () {
    // Buat materi e-book digital
    $ebook = LibraryBook::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'E-Book Sains Terapan',
        'author' => 'Dr. Budi',
        'category_id' => $this->category->id,
        'type' => 'digital',
        'file_path' => 'library/files/sains_terapan.pdf',
        'is_physical_available' => false,
        'created_by' => $this->librarian->id,
    ]);

    // 1. TANPA LOGIN (Guest) - Harus diarahkan ke login page (HTTP 302)
    $responseGuestRead = $this->get(route('library.read', $ebook));
    $responseGuestRead->assertStatus(302);

    $responseGuestCirc = $this->get(route('library.circulation.index'));
    $responseGuestCirc->assertStatus(302);

    // 2. SISWA (Authenticated Student) - Boleh membaca e-book tetapi tidak boleh mengakses sirkulasi
    $responseStudentRead = $this->actingAs($this->studentUser)->get(route('library.read', $ebook));
    $responseStudentRead->assertStatus(200);
    $responseStudentRead->assertSee($ebook->title);

    $responseStudentCirc = $this->actingAs($this->studentUser)->get(route('library.circulation.index'));
    // Harus Forbidden (HTTP 403) karena sirkulasi khusus karyawan perpustakaan
    $responseStudentCirc->assertStatus(403);

    // 3. KARYAWAN (Authenticated Librarian) - Boleh mengakses sirkulasi perpustakaan
    $responseLibrarianCirc = $this->actingAs($this->librarian)->get(route('library.circulation.index'));
    $responseLibrarianCirc->assertStatus(200);
    $responseLibrarianCirc->assertSee('Sirkulasi Perpustakaan');
});
