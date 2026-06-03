<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Setting;
use App\Models\Student;
use App\Services\LibraryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LibraryCirculationController extends Controller
{
    protected $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    /**
     * Menampilkan dashboard sirkulasi perpustakaan.
     */
    public function index()
    {
        // Karyawan perpustakaan bisa mengakses
        $activeLoans = LibraryLoan::with(['student.user', 'book', 'creator'])
            ->whereNull('returned_at')
            ->latest()
            ->paginate(15, ['*'], 'active_page');

        $allLoans = LibraryLoan::with(['student.user', 'book', 'creator'])
            ->latest()
            ->paginate(15, ['*'], 'all_page');

        $currentAcademicYear = AcademicYear::current();

        // Default denda per hari
        $finePerDay = Setting::get('library_fine_per_day', 1000);

        return view('library.circulation', compact('activeLoans', 'allLoans', 'currentAcademicYear', 'finePerDay'));
    }

    /**
     * Scan API: Mengidentifikasi kode (bisa berupa NIS siswa atau UUID/ID buku).
     */
    public function scan(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = trim($request->code);

        // 1. Cari di data Siswa (berdasarkan NIS)
        $student = Student::with(['user', 'classroom', 'libraryLoans' => function ($q) {
            $q->whereNull('returned_at')->with('book');
        }])->where('nis', $code)->first();

        if ($student) {
            return response()->json([
                'type' => 'student',
                'id' => $student->id,
                'name' => $student->user->name,
                'nis' => $student->nis,
                'class_name' => $student->classroom->name ?? 'Tanpa Kelas',
                'active_loans_count' => $student->activeLoansCount(),
                'active_loans' => $student->libraryLoans->map(function ($loan) {
                    return [
                        'id' => $loan->id,
                        'title' => $loan->book->title,
                        'borrowed_at' => $loan->borrowed_at->format('d-m-Y'),
                        'due_at' => $loan->due_at->format('d-m-Y'),
                        'fine' => $loan->calculateFine(),
                        'is_overdue' => $loan->due_at->isPast(),
                    ];
                }),
            ]);
        }

        // 2. Cari di data Buku (berdasarkan UUID atau ID)
        $book = LibraryBook::where('uuid', $code)
            ->orWhere('id', $code)
            ->first();

        if ($book) {
            return response()->json([
                'type' => 'book',
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author ?? 'Sekolah',
                'book_type' => $book->type,
                'is_available' => $book->isAvailableForBorrow(),
            ]);
        }

        return response()->json([
            'error' => 'Kode tidak dikenali sebagai NIS Siswa atau Kode Buku.',
        ], 404);
    }

    /**
     * Memproses peminjaman buku via AJAX.
     */
    public function borrow(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'library_book_id' => 'required|exists:library_books,id',
            'due_date_type' => 'required|in:regular,package,custom',
            'custom_due_date' => 'required_if:due_date_type,custom|nullable|date|after_or_equal:today',
        ]);

        try {
            $student = Student::findOrFail($request->student_id);
            $book = LibraryBook::findOrFail($request->library_book_id);

            // Tentukan tanggal jatuh tempo berdasarkan pilihan petugas
            $dueAt = now();
            if ($request->due_date_type === 'regular') {
                $dueAt = now()->addDays(7);
            } elseif ($request->due_date_type === 'package') {
                $academicYear = AcademicYear::current();
                if ($academicYear && $academicYear->end_date) {
                    $dueAt = Carbon::parse($academicYear->end_date);
                } else {
                    // Fallback jika tidak ada tahun ajaran aktif, durasi 6 bulan (1 semester)
                    $dueAt = now()->addMonths(6);
                }
            } elseif ($request->due_date_type === 'custom') {
                $dueAt = Carbon::parse($request->custom_due_date);
            }

            $loan = $this->libraryService->borrowBook($student, $book, $dueAt, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Buku "'.$book->title.'" berhasil dipinjam oleh '.$student->user->name.'.',
                'loan' => $loan,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Memproses pengembalian buku fisik.
     */
    public function returnBook(LibraryLoan $loan)
    {
        try {
            $returnedLoan = $this->libraryService->returnBook($loan, auth()->user());
            $fine = $returnedLoan->fine_amount;

            $message = 'Buku "'.$returnedLoan->book->title.'" berhasil dikembalikan.';
            if ($fine > 0) {
                $message .= ' Siswa terlambat mengembalikan dan dikenai denda sebesar Rp '.number_format($fine, 0, ',', '.').'.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'fine_amount' => $fine,
                'loan' => $returnedLoan,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Memproses pelunasan denda buku.
     */
    public function payFine(LibraryLoan $loan)
    {
        try {
            $paidLoan = $this->libraryService->payLoanFine($loan);

            return response()->json([
                'success' => true,
                'message' => 'Denda keterlambatan sebesar Rp '.number_format($paidLoan->fine_amount, 0, ',', '.').' berhasil dilunasi dan dicatatkan ke kas keuangan.',
                'loan' => $paidLoan,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
