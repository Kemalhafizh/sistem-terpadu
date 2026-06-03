<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LibraryService
{
    /**
     * Memproses peminjaman buku fisik oleh siswa.
     */
    public function borrowBook(Student $student, LibraryBook $book, Carbon $dueAt, User $librarian): LibraryLoan
    {
        return DB::transaction(function () use ($student, $book, $dueAt, $librarian) {
            // 1. Validasi tipe buku harus fisik
            if ($book->type !== 'physical') {
                throw new \Exception('Hanya buku fisik yang dapat dipinjam secara manual.');
            }

            // 2. Validasi ketersediaan buku
            if (! $book->isAvailableForBorrow()) {
                throw new \Exception('Buku sedang tidak tersedia untuk dipinjam.');
            }

            // 3. Validasi batas kuota peminjaman siswa (maksimal dari pengaturan, default 3)
            $maxBorrow = (int) Setting::get('library_max_borrow_books', 3);
            if ($student->activeLoansCount() >= $maxBorrow) {
                throw new \Exception("Siswa ini telah mencapai batas maksimal peminjaman ({$maxBorrow} buku).");
            }

            // 4. Ubah status ketersediaan buku menjadi false
            $book->update(['is_physical_available' => false]);

            // 5. Buat data peminjaman
            return LibraryLoan::create([
                'uuid' => (string) Str::uuid(),
                'student_id' => $student->id,
                'library_book_id' => $book->id,
                'borrowed_at' => now(),
                'due_at' => $dueAt,
                'status' => 'borrowed',
                'fine_amount' => 0,
                'created_by' => $librarian->id,
            ]);
        });
    }

    /**
     * Memproses pengembalian buku fisik yang sedang dipinjam.
     */
    public function returnBook(LibraryLoan $loan, User $librarian): LibraryLoan
    {
        return DB::transaction(function () use ($loan) {
            if ($loan->returned_at !== null) {
                throw new \Exception('Buku ini sudah dikembalikan sebelumnya.');
            }

            $now = now();
            $loan->returned_at = $now;

            // 1. Hitung denda keterlambatan jika ada
            $fine = $loan->calculateFine();
            if ($fine > 0) {
                $loan->fine_amount = $fine;
                $loan->status = 'overdue'; // Ditandai overdue jika terlambat dikembalikan
            } else {
                $loan->status = 'returned';
            }

            // 2. Kembalikan status ketersediaan buku fisik menjadi true
            $loan->book->update(['is_physical_available' => true]);

            // 3. Simpan perubahan peminjaman
            $loan->save();

            return $loan;
        });
    }

    /**
     * Memproses pelunasan denda keterlambatan buku dan melakukan penjurnalan otomatis.
     */
    public function payLoanFine(LibraryLoan $loan): LibraryLoan
    {
        return DB::transaction(function () use ($loan) {
            if ($loan->fine_amount <= 0) {
                throw new \Exception('Peminjaman ini tidak memiliki denda.');
            }

            if ($loan->fine_paid_at !== null) {
                throw new \Exception('Denda untuk peminjaman ini sudah dilunasi.');
            }

            // 1. Catat pelunasan denda
            $loan->fine_paid_at = now();
            $loan->status = 'returned'; // Pastikan status kembali normal menjadi 'returned' setelah denda lunas
            $loan->save();

            // 2. Lakukan penjurnalan otomatis (Akuntansi Double-Entry)
            // Debit: Kas & Setara Kas (1010)
            // Kredit: Pendapatan Lain-lain (4040)
            $kasAccount = Account::where('code', '1010')->first();
            $pendapatanAccount = Account::where('code', '4040')->first();

            if ($kasAccount && $pendapatanAccount) {
                $entry = JournalEntry::create([
                    'entry_date' => now()->toDateString(),
                    'description' => 'Penerimaan Kas Denda Perpustakaan - Buku: '.$loan->book->title.' (Siswa: '.($loan->student->user->name ?? $loan->student->nis).')',
                    'reference' => 'LBF-'.$loan->id,
                ]);

                // Baris Debit (Kas bertambah)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $kasAccount->id,
                    'debit' => $loan->fine_amount,
                    'credit' => 0,
                ]);

                // Baris Kredit (Pendapatan Lain-lain bertambah)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $pendapatanAccount->id,
                    'debit' => 0,
                    'credit' => $loan->fine_amount,
                ]);
            }

            return $loan;
        });
    }

    /**
     * Helper untuk memindai dan memperbarui status peminjaman aktif yang telah melewati due_at menjadi overdue.
     */
    public function updateOverdueLoans(): int
    {
        return DB::transaction(function () {
            $overdueLoans = LibraryLoan::active()
                ->where('due_at', '<', now())
                ->where('status', '!=', 'overdue')
                ->get();

            foreach ($overdueLoans as $loan) {
                $loan->update([
                    'status' => 'overdue',
                    'fine_amount' => $loan->calculateFine(),
                ]);
            }

            return $overdueLoans->count();
        });
    }
}
