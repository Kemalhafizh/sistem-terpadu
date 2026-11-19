<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Imports\StudentsImport;
use App\Models\Classroom;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentLedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index(Request $request)
    {
        $query = Student::with(['user', 'classroom']);

        // Search by name, email, nis, or nisn
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Filter by Classroom
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $students = $query->latest()->paginate(15)->withQueryString();
        $classrooms = Classroom::all();

        return view('students.index', compact('students', 'classrooms'));
    }

    public function create()
    {
        // Get users that are "student" but don't have a student profile yet
        $users = User::where('role', 'student')->doesntHave('student')->get();
        $classrooms = Classroom::all();

        return view('students.create', compact('users', 'classrooms'));
    }

    public function store(StoreStudentRequest $request)
    {
        Student::create($request->validated());

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $student = Student::findOrFail($id);
        $users = User::where('role', 'student')->get();
        $classrooms = Classroom::all();

        return view('students.edit', compact('student', 'users', 'classrooms'));
    }

    public function update(UpdateStudentRequest $request, string $id)
    {
        $student = Student::findOrFail($id);
        $student->update($request->validated());

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        Student::findOrFail($id)->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new StudentsImport, $request->file('file'));

        return back()->with('success', 'Data siswa berhasil diimport secara masal.');
    }

    public function exportPdf(string $id)
    {
        $student = Student::with(['user', 'classroom'])->findOrFail($id);
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView('students.pdf-biodata', compact('student', 'settings'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Biodata_'.$student->nis.'_'.str_replace(' ', '_', $student->user->name).'.pdf');
    }

    public function bukuInduk(Student $student, StudentLedgerService $ledgerService)
    {
        abort_unless($student->isVisibleTo(auth()->user()), 403, 'Anda tidak berwenang melihat Buku Induk siswa ini.');

        $ledger = $ledgerService->build($student);

        return view('students.buku-induk', $ledger);
    }

    public function bukuIndukPdf(Student $student, StudentLedgerService $ledgerService)
    {
        abort_unless($student->isVisibleTo(auth()->user()), 403, 'Anda tidak berwenang mencetak Buku Induk siswa ini.');

        $ledger = $ledgerService->build($student);
        $pdf = Pdf::loadView('students.buku-induk-pdf', $ledger);
        $pdf->setPaper('A4', 'portrait');

        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($student->user?->name ?? 'siswa'));

        return $pdf->stream('Buku_Induk_'.($student->nis ?: $student->nisn).'_'.$safeName.'.pdf');
    }
}
