<?php

namespace App\Http\Controllers;

use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = LibraryBook::with(['category', 'subject']);

        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->subject) {
            $query->where('subject_id', $request->subject);
        }
        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $books = $query->latest()->paginate(12);
        $categories = LibraryCategory::all();
        $subjects = Subject::all();

        return view('library.index', compact('books', 'categories', 'subjects'));
    }

    public function manage()
    {
        $books = LibraryBook::with(['category', 'subject', 'creator'])->latest()->paginate(20);

        return view('library.manage', compact('books'));
    }

    public function create()
    {
        $categories = LibraryCategory::all();
        $subjects = Subject::all();

        return view('library.create', compact('categories', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'category_id' => 'required|exists:library_categories,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'type' => 'required|in:digital,physical',
            'cover' => 'nullable|image|max:2048',
            'file' => 'nullable|mimes:pdf,epub|max:10240',
            'video_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('library/covers', 'public');
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('library/files', 'public');
        }

        LibraryBook::create(array_merge($validated, [
            'cover_path' => $coverPath,
            'file_path' => $filePath,
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('library.manage')->with('success', 'Materi berhasil ditambahkan ke perpustakaan.');
    }

    public function download(LibraryBook $libraryBook)
    {
        if ($libraryBook->type !== 'digital' || ! $libraryBook->file_path) {
            return back()->with('error', 'Materi digital tidak tersedia.');
        }

        $libraryBook->increment('download_count');

        return Storage::disk('public')->download($libraryBook->file_path, Str::slug($libraryBook->title).'.pdf');
    }

    public function edit(LibraryBook $libraryBook)
    {
        $categories = LibraryCategory::all();
        $subjects = Subject::all();

        return view('library.edit', compact('libraryBook', 'categories', 'subjects'));
    }

    public function update(Request $request, LibraryBook $libraryBook)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'category_id' => 'required|exists:library_categories,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'type' => 'required|in:digital,physical',
            'cover' => 'nullable|image|max:2048',
            'file' => 'nullable|mimes:pdf,epub|max:10240',
            'video_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('cover')) {
            if ($libraryBook->cover_path) {
                Storage::disk('public')->delete($libraryBook->cover_path);
            }
            $validated['cover_path'] = $request->file('cover')->store('library/covers', 'public');
        }

        if ($request->hasFile('file')) {
            if ($libraryBook->file_path) {
                Storage::disk('public')->delete($libraryBook->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('library/files', 'public');
        }

        $libraryBook->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'category_id' => $validated['category_id'],
            'subject_id' => $validated['subject_id'],
            'type' => $validated['type'],
            'cover_path' => $validated['cover_path'] ?? $libraryBook->cover_path,
            'file_path' => $validated['file_path'] ?? $libraryBook->file_path,
            'video_url' => $validated['video_url'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('library.manage')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(LibraryBook $libraryBook)
    {
        if ($libraryBook->cover_path) {
            Storage::disk('public')->delete($libraryBook->cover_path);
        }
        if ($libraryBook->file_path) {
            Storage::disk('public')->delete($libraryBook->file_path);
        }
        $libraryBook->delete();

        return back()->with('success', 'Buku/Materi berhasil dihapus.');
    }

    /**
     * Membuka penampil E-Book PDF terproteksi secara online.
     */
    public function readBook(LibraryBook $libraryBook)
    {
        if ($libraryBook->type !== 'digital' || ! $libraryBook->file_path) {
            return back()->with('error', 'Materi digital tidak tersedia atau bukan berupa e-book.');
        }

        // Catat statistik akses (opsional, bisa increment download/view count)
        $libraryBook->increment('download_count');

        return view('library.read', compact('libraryBook'));
    }

    /**
     * Menampilkan Kartu Anggota Perpustakaan Siswa untuk dicetak.
     */
    public function studentCard(Student $student)
    {
        // Validasi otoritas: Guru, Staf TU, Admin, atau siswa itu sendiri
        $user = auth()->user();
        if ($user->hasRole('student') && $user->student?->id !== $student->id) {
            abort(403, 'Anda tidak diizinkan melihat kartu perpustakaan siswa lain.');
        }

        return view('library.student_card', compact('student'));
    }
}
