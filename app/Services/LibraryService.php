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
use App\Models\LibraryCategory;
use App\Services\WhatsappService;


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

    /**
     * Get visual and numeric library statistics summary.
     */
    public function getLibraryStatistics(): array
    {
        $totalPhysical = LibraryBook::where('type', 'physical')->count();
        $totalDigital = LibraryBook::where('type', 'digital')->count();

        $activeLoans = LibraryLoan::whereNull('returned_at')->count();
        $returnedLoans = LibraryLoan::whereNotNull('returned_at')->count();
        $overdueLoans = LibraryLoan::whereNull('returned_at')->where('due_at', '<', now())->count();

        // Top 5 popular books based on total borrows/loans
        $topBooks = LibraryBook::select('library_books.*', DB::raw('count(library_loans.id) as loans_count'))
            ->leftJoin('library_loans', 'library_books.id', '=', 'library_loans.library_book_id')
            ->groupBy('library_books.id')
            ->orderByDesc('loans_count')
            ->limit(5)
            ->with('category')
            ->get();

        // 6 months borrowing trend
        $trendLabels = [];
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = LibraryLoan::whereMonth('borrowed_at', $month->month)
                ->whereYear('borrowed_at', $month->year)
                ->count();
            $trendLabels[] = $month->translatedFormat('F Y');
            $trendData[] = $count;
        }

        // Category distribution
        $categoryDistribution = LibraryCategory::select('library_categories.name', DB::raw('count(library_books.id) as books_count'))
            ->leftJoin('library_books', 'library_categories.id', '=', 'library_books.category_id')
            ->groupBy('library_categories.id', 'library_categories.name')
            ->get();

        return [
            'total_physical' => $totalPhysical,
            'total_digital' => $totalDigital,
            'active_loans' => $activeLoans,
            'returned_loans' => $returnedLoans,
            'overdue_loans' => $overdueLoans,
            'top_books' => $topBooks,
            'trend' => [
                'labels' => $trendLabels,
                'data' => $trendData,
            ],
            'categories' => $categoryDistribution,
        ];
    }

    /**
     * Get book recommendations based on student's favorite category or global popularity.
     */
    public function getBookRecommendations(Student $student, int $limit = 6): \Illuminate\Support\Collection
    {
        $borrowedBookIds = LibraryLoan::where('student_id', $student->id)->pluck('library_book_id')->toArray();

        // Find favorite category based on student's borrow history
        $favoriteCategory = LibraryLoan::join('library_books', 'library_loans.library_book_id', '=', 'library_books.id')
            ->where('library_loans.student_id', $student->id)
            ->select('library_books.category_id', DB::raw('count(library_loans.id) as count'))
            ->groupBy('library_books.category_id')
            ->orderByDesc('count')
            ->first();

        if ($favoriteCategory) {
            // Suggest books from favorite category that haven't been borrowed yet
            $books = LibraryBook::where('category_id', $favoriteCategory->category_id)
                ->whereNotIn('id', $borrowedBookIds)
                ->with('category')
                ->limit($limit)
                ->get();

            // If not enough books, fill the rest with globally popular books
            if ($books->count() < $limit) {
                $backfillLimit = $limit - $books->count();
                $popularBooks = LibraryBook::select('library_books.*', DB::raw('count(library_loans.id) as loans_count'))
                    ->leftJoin('library_loans', 'library_books.id', '=', 'library_loans.library_book_id')
                    ->whereNotIn('library_books.id', array_merge($borrowedBookIds, $books->pluck('id')->toArray()))
                    ->groupBy('library_books.id')
                    ->orderByDesc('loans_count')
                    ->limit($backfillLimit)
                    ->with('category')
                    ->get();
                $books = $books->merge($popularBooks);
            }
        } else {
            // Suggest globally popular books
            $books = LibraryBook::select('library_books.*', DB::raw('count(library_loans.id) as loans_count'))
                ->leftJoin('library_loans', 'library_books.id', '=', 'library_loans.library_book_id')
                ->whereNotIn('library_books.id', $borrowedBookIds)
                ->groupBy('library_books.id')
                ->orderByDesc('loans_count')
                ->limit($limit)
                ->with('category')
                ->get();
        }

        return $books;
    }

    /**
     * Get student reading history and statistic summary.
     */
    public function getStudentReadingStats(Student $student): array
    {
        $totalBorrowed = LibraryLoan::where('student_id', $student->id)->count();

        $physicalCount = LibraryLoan::where('student_id', $student->id)
            ->whereHas('book', fn($q) => $q->where('type', 'physical'))
            ->count();

        $digitalCount = LibraryLoan::where('student_id', $student->id)
            ->whereHas('book', fn($q) => $q->where('type', 'digital'))
            ->count();

        // Favorite category
        $fav = LibraryLoan::join('library_books', 'library_loans.library_book_id', '=', 'library_books.id')
            ->join('library_categories', 'library_books.category_id', '=', 'library_categories.id')
            ->where('library_loans.student_id', $student->id)
            ->select('library_categories.name', DB::raw('count(library_loans.id) as count'))
            ->groupBy('library_categories.id', 'library_categories.name')
            ->orderByDesc('count')
            ->first();

        $favoriteCategory = $fav ? $fav->name : 'Belum Ada';

        $totalFinePaid = (float) LibraryLoan::where('student_id', $student->id)
            ->whereNotNull('fine_paid_at')
            ->sum('fine_amount');

        $activeLoans = LibraryLoan::with('book.category')
            ->where('student_id', $student->id)
            ->whereNull('returned_at')
            ->latest()
            ->get();

        $historyLoans = LibraryLoan::with('book.category')
            ->where('student_id', $student->id)
            ->whereNotNull('returned_at')
            ->latest()
            ->get();

        return [
            'total_borrowed' => $totalBorrowed,
            'physical_count' => $physicalCount,
            'digital_count' => $digitalCount,
            'favorite_category' => $favoriteCategory,
            'total_fine_paid' => $totalFinePaid,
            'active_loans' => $activeLoans,
            'history_loans' => $historyLoans,
        ];
    }

    /**
     * Get all currently overdue loans.
     */
    public function getOverdueLoansSummary(): \Illuminate\Support\Collection
    {
        return LibraryLoan::with(['student.user', 'student.classroom', 'book'])
            ->overdue()
            ->latest()
            ->get();
    }

    /**
     * Send WhatsApp alert notification to student's parent/student for overdue book.
     */
    public function sendOverdueWhatsAppReminder(int $loanId): \App\Models\WhatsappLog
    {
        $loan = LibraryLoan::with(['student.user', 'student.parent', 'book'])->findOrFail($loanId);

        // Prioritize parent number, fallback to student number
        $recipient = $loan->student->parent->phone_number ?? $loan->student->user->phone_number;

        if (!$recipient) {
            throw new \Exception('Nomor telepon siswa maupun orang tua tidak terdaftar di sistem.');
        }

        $studentName = $loan->student->user->name;
        $bookTitle = $loan->book->title;
        $dueDate = $loan->due_at->format('d-m-Y');
        $daysLate = $loan->daysOverdue();
        $fine = number_format($loan->calculateFine(), 0, ',', '.');

        $message = "Yth. Orang Tua/Wali Murid dari {$studentName},\n\n";
        $message .= "Kami menginformasikan bahwa peminjaman buku perpustakaan berikut telah melewati batas waktu pengembalian:\n";
        $message .= "- Judul Buku: {$bookTitle}\n";
        $message .= "- Batas Waktu: {$dueDate}\n";
        $message .= "- Terlambat: {$daysLate} hari\n";
        $message .= "- Akumulasi Denda: Rp {$fine}\n\n";
        $message .= "Mohon untuk segera mengingatkan anak Anda agar mengembalikan buku tersebut ke perpustakaan sekolah.\n\n";
        $message .= "Salam,\nPerpustakaan SMA Islam Sultan Agung 1 Semarang";

        return app(WhatsappService::class)->sendMessage($recipient, $message, 'library_overdue_reminder');
    }
}
