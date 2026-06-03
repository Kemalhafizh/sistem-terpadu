<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\ElearningMaterial;
use App\Models\ElearningSubmission;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ElearningController extends Controller
{
    /**
     * PWA Install landing page with App Store / Google Play visuals.
     */
    public function install()
    {
        return view('elearning.install');
    }

    /**
     * LMS main dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $classes = collect();
        $classrooms = collect();
        $subjects = collect();

        if ($user->role === 'student' && $user->student) {
            // Student: load classes for their classroom
            $classes = ElearningClass::where('classroom_id', $user->student->classroom_id)
                ->with(['subject', 'teacher'])
                ->get();
        } elseif (in_array($user->role, ['guru', 'staf_tu', 'admin', 'superadmin', 'kepala_sekolah'])) {
            // Teacher / Admin: load classes they teach or all classes
            if (in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'])) {
                $classes = ElearningClass::with(['subject', 'classroom', 'teacher'])->get();
            } else {
                $classes = ElearningClass::where('teacher_id', $user->id)
                    ->with(['subject', 'classroom', 'teacher'])
                    ->get();
            }
            $classrooms = Classroom::all();
            $subjects = Subject::all();
        }

        return view('elearning.index', compact('classes', 'classrooms', 'subjects'));
    }

    /**
     * Store a new classroom (Guru/Admin only).
     */
    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'description' => 'nullable|string',
        ]);

        ElearningClass::create([
            'uuid' => (string) Str::uuid(),
            'subject_id' => $validated['subject_id'],
            'classroom_id' => $validated['classroom_id'],
            'teacher_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Kelas virtual berhasil dibuat.');
    }

    /**
     * Class detail stream.
     */
    public function showClass(ElearningClass $elearningClass)
    {
        $user = auth()->user();

        // Security check
        if ($user->role === 'student' && $user->student->classroom_id !== $elearningClass->classroom_id) {
            abort(403, 'Anda tidak terdaftar di kelas virtual ini.');
        }

        $materials = $elearningClass->materials()->latest()->get();
        $assignments = $elearningClass->assignments()->latest()->get();

        return view('elearning.show_class', compact('elearningClass', 'materials', 'assignments'));
    }

    /**
     * Store virtual class material (Guru/Admin only).
     */
    public function storeMaterial(Request $request, ElearningClass $elearningClass)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,zip,doc,docx,ppt,pptx|max:10240', // Max 10MB
            'video_url' => 'nullable|url',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time().'_'.Str::slug($validated['title']).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/materials'), $fileName);
            $filePath = 'uploads/materials/'.$fileName;
        }

        ElearningMaterial::create([
            'uuid' => (string) Str::uuid(),
            'elearning_class_id' => $elearningClass->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'file_path' => $filePath,
            'video_url' => $validated['video_url'],
        ]);

        return back()->with('success', 'Materi belajar berhasil dibagikan.');
    }

    /**
     * Store new assignment (Guru/Admin only).
     */
    public function storeAssignment(Request $request, ElearningClass $elearningClass)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_at' => 'required|date|after:now',
            'allow_late' => 'boolean',
            'is_secure_mode' => 'boolean',
            'max_violations' => 'required_if:is_secure_mode,1|integer|min:1|max:10',
        ]);

        ElearningAssignment::create([
            'uuid' => (string) Str::uuid(),
            'elearning_class_id' => $elearningClass->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'due_at' => $validated['due_at'],
            'allow_late' => $request->has('allow_late'),
            'is_secure_mode' => $request->has('is_secure_mode'),
            'max_violations' => $request->get('max_violations', 3),
        ]);

        return back()->with('success', 'Tugas/Ujian berhasil diterbitkan.');
    }

    /**
     * Show assignment detail.
     */
    public function showAssignment(ElearningAssignment $elearningAssignment)
    {
        $user = auth()->user();
        $student = $user->student;

        if (! $student) {
            abort(403, 'Hanya siswa yang dapat mengakses pengerjaan tugas.');
        }

        // Security check
        if ($student->classroom_id !== $elearningAssignment->elearningClass->classroom_id) {
            abort(403, 'Akses ditolak.');
        }

        $submission = $elearningAssignment->studentSubmission($student);

        return view('elearning.show_assignment', compact('elearningAssignment', 'submission'));
    }

    /**
     * Submit assignment (Student only).
     */
    public function submitAssignment(Request $request, ElearningAssignment $elearningAssignment)
    {
        $user = auth()->user();
        $student = $user->student;

        if (! $student) {
            abort(403, 'Hanya siswa yang dapat mengumpulkan tugas.');
        }

        // Security check
        if ($student->classroom_id !== $elearningAssignment->elearningClass->classroom_id) {
            abort(403, 'Akses ditolak.');
        }

        // Check if already submitted
        $existing = $elearningAssignment->studentSubmission($student);
        if ($existing && $existing->status === 'locked_cheated') {
            return back()->with('error', 'Pengerjaan Anda terkunci karena pelanggaran integritas.');
        }

        // Check deadline
        if ($elearningAssignment->isOverdue() && ! $elearningAssignment->allow_late) {
            return back()->with('error', 'Batas waktu pengerjaan telah berakhir dan pengumpulan terlambat dinonaktifkan.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time().'_'.$student->nis.'_'.$elearningAssignment->id.'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/submissions'), $fileName);
            $filePath = 'uploads/submissions/'.$fileName;
        }

        ElearningSubmission::updateOrCreate(
            [
                'elearning_assignment_id' => $elearningAssignment->id,
                'student_id' => $student->id,
            ],
            [
                'submitted_at' => now(),
                'file_path' => $filePath,
                'status' => 'submitted',
            ]
        );

        return back()->with('success', 'Tugas berhasil dikumpulkan secara aman.');
    }

    /**
     * View list of student submissions (Guru/Admin only).
     */
    public function viewSubmissions(ElearningAssignment $elearningAssignment)
    {
        $submissions = $elearningAssignment->submissions()->with('student.user')->get();

        return view('elearning.submissions', compact('elearningAssignment', 'submissions'));
    }

    /**
     * Input grade and feedback (Guru/Admin only).
     */
    public function gradeSubmission(Request $request, ElearningSubmission $elearningSubmission)
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $elearningSubmission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
            'status' => 'graded',
        ]);

        return back()->with('success', 'Nilai dan masukan siswa berhasil disimpan.');
    }
}
