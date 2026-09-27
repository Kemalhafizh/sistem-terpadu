<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Services\LibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Library Mobile API v1
 *
 * REST API Endpoints untuk integrasi modul E-Pustaka Pintar pada aplikasi Android Sulaone.
 */
class LibraryMobileApiController extends Controller
{
    protected LibraryService $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    /**
     * GET /api/v1/library/books
     * Katalog buku digital & fisik dengan filter pencarian dan kategori.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LibraryBook::with('category');

        // Filter text query (Judul, Penulis, ISBN, Barcode, Sinopsis)
        if ($search = $request->input('query') ?: $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter Kategori (misal: "Sains & Teknologi", "Keislaman & Tarbiyah", dll)
        $category = $request->input('category');
        if ($category && $category !== 'Semua') {
            $query->where(function ($q) use ($category) {
                $q->whereHas('category', function ($cq) use ($category) {
                    $cq->where('name', $category);
                })->orWhere('category_id', $category);
            });
        }

        // Default sorting: ketersediaan fisik dahulu, lalu terbaru
        $query->orderBy('is_physical_available', 'desc')->latest();

        // Support paginasi jika diminta secara eksplisit
        if ($request->boolean('paginated') || $request->has('page')) {
            $perPage = $request->integer('per_page', 15);
            $paginated = $query->paginate($perPage);

            $paginated->getCollection()->transform(function ($book) {
                return $book->toMobileItemArray();
            });

            return response()->json($paginated);
        }

        // Default output: JSON array list langsung untuk deserializer Retrofit Android
        $books = $query->get()->map(function ($book) {
            return $book->toMobileItemArray();
        });

        return response()->json($books);
    }

    /**
     * GET /api/v1/library/books/{id}
     * Detail buku dan status ketersediaan salinan fisik.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $book = LibraryBook::with('category')
            ->where('id', $id)
            ->orWhere('uuid', $id)
            ->first();

        if (! $book) {
            return response()->json([
                'error' => 'Buku tidak ditemukan.',
                'message' => 'Data buku dengan identifier tersebut tidak terdaftar.',
            ], 404);
        }

        return response()->json($book->toMobileItemArray());
    }

    /**
     * POST /api/v1/library/self-checkout
     * Scan mandiri QR/Barcode/ISBN buku fisik oleh siswa via kamera mobile.
     */
    public function selfCheckout(Request $request): JsonResponse
    {
        $request->validate([
            'book_qr_code' => 'required|string',
            'student_id' => 'nullable',
        ]);

        $user = $request->user();

        // Tentukan data siswa
        $student = null;
        $isStaff = in_array($user->role, ['admin', 'superadmin', 'guru', 'teacher', 'pustakawan', 'petugas_perpustakaan'], true);

        if ($request->filled('student_id')) {
            if (! $isStaff && $user->student && (string) $user->student->id !== (string) $request->student_id && (string) $user->student->nis !== (string) $request->student_id) {
                return response()->json([
                    'error' => 'Akses ditolak.',
                    'message' => 'Siswa dilarang melakukan peminjaman atas nama identitas siswa lain.',
                ], 403);
            }

            if ($isStaff) {
                $student = Student::where('id', $request->student_id)
                    ->orWhere('nis', $request->student_id)
                    ->first();
            }
        }

        if (! $student && $user->student) {
            $student = $user->student;
        }

        if (! $student) {
            // Jika guru/admin meminjam atas nama siswa tetapi student_id tidak disertakan
            return response()->json([
                'error' => 'Data siswa tidak valid.',
                'message' => 'Identitas siswa peminjam tidak ditemukan pada sesi atau parameter request.',
            ], 422);
        }

        try {
            $loan = $this->libraryService->selfCheckout($student, $request->book_qr_code, $user);

            $loan->load(['book', 'student.user']);

            return response()->json($loan->toMobileItemArray(), 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal melakukan peminjaman.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v1/library/my-loans
     * Daftar peminjaman aktif & riwayat peminjaman siswa.
     */
    public function myLoans(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (! $student) {
            return response()->json([]);
        }

        $loans = LibraryLoan::where('student_id', $student->id)
            ->with(['book.category'])
            ->orderByRaw('CASE WHEN returned_at IS NULL THEN 0 ELSE 1 END')
            ->latest()
            ->get();

        $data = $loans->map(function ($loan) {
            return $loan->toMobileItemArray();
        });

        return response()->json($data);
    }
}
