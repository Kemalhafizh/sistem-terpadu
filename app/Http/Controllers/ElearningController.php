<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\ElearningMaterial;
use App\Models\ElearningSubmission;
use App\Models\ElearningAnnouncement;
use App\Models\ElearningMaterialComment;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ElearningService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class ElearningController extends Controller
{
    protected $elearningService;

    public function __construct(ElearningService $elearningService)
    {
        $this->elearningService = $elearningService;
    }

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
    public function index(Request $request)
    {
        $user = auth()->user();
        $classes = collect();
        $archivedClasses = collect();
        $classrooms = collect();
        $subjects = collect();

        $showArchived = $request->boolean('show_archived', false);

        if ($user->role === 'student' && $user->student) {
            // Student: load classes for their classroom (students do not see archived classes unless specified)
            $classesQuery = ElearningClass::where('classroom_id', $user->student->classroom_id)
                ->with(['subject', 'teacher']);
            
            if ($showArchived) {
                $classes = $classesQuery->where('is_archived', true)->get();
            } else {
                $classes = $classesQuery->where('is_archived', false)->get();
            }
        } elseif (in_array($user->role, ['guru', 'staf_tu', 'admin', 'superadmin', 'kepala_sekolah'])) {
            // Teacher / Admin: load classes they teach or all classes
            if (in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'])) {
                $classesQuery = ElearningClass::with(['subject', 'classroom', 'teacher']);
            } else {
                $classesQuery = ElearningClass::where('teacher_id', $user->id)
                    ->with(['subject', 'classroom', 'teacher']);
            }

            if ($showArchived) {
                $classes = (clone $classesQuery)->where('is_archived', true)->get();
            } else {
                $classes = (clone $classesQuery)->where('is_archived', false)->get();
                $archivedClasses = (clone $classesQuery)->where('is_archived', true)->get();
            }

            $classrooms = Classroom::all();
            $subjects = Subject::all();
        }

        return view('elearning.index', compact('classes', 'archivedClasses', 'classrooms', 'subjects', 'showArchived'));
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
            'is_archived' => false,
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
        $announcements = $elearningClass->announcements()->with('user')->latest()->get();

        // Calculate progress if student
        $studentProgress = null;
        if ($user->role === 'student' && $user->student) {
            $studentProgress = $this->elearningService->getStudentProgress($elearningClass->id, $user->student->id);
        }

        return view('elearning.show_class', compact('elearningClass', 'materials', 'assignments', 'announcements', 'studentProgress'));
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

    // =========================================================
    // NEW METHODS FOR MODUL 12.3 ENHANCEMENTS
    // =========================================================

    /**
     * Store a new class announcement (Guru/Admin only).
     */
    public function storeAnnouncement(Request $request, ElearningClass $elearningClass)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        ElearningAnnouncement::create([
            'uuid' => (string) Str::uuid(),
            'elearning_class_id' => $elearningClass->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Pengumuman kelas berhasil dipublikasikan.');
    }

    /**
     * Delete a class announcement (Guru/Admin only).
     */
    public function deleteAnnouncement(ElearningClass $elearningClass, ElearningAnnouncement $announcement)
    {
        // Security check
        if ($announcement->elearning_class_id !== $elearningClass->id) {
            abort(400);
        }

        if (auth()->id() !== $announcement->user_id && !in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $announcement->delete();

        return back()->with('success', 'Pengumuman kelas berhasil dihapus.');
    }

    /**
     * Show a learning material detail and log the view (with comments/discussion).
     */
    public function showMaterial(ElearningClass $elearningClass, ElearningMaterial $material)
    {
        $user = auth()->user();

        // Security check
        if ($material->elearning_class_id !== $elearningClass->id) {
            abort(404);
        }
        if ($user->role === 'student' && $user->student->classroom_id !== $elearningClass->classroom_id) {
            abort(403);
        }

        // Log view if student
        if ($user->role === 'student' && $user->student) {
            $this->elearningService->logMaterialView($user->student->id, $material->id);
        }

        $comments = $material->comments()->with('user')->oldest()->get();

        return view('elearning.show_material', compact('elearningClass', 'material', 'comments'));
    }

    /**
     * Store comment/reply in material discussion.
     */
    public function storeComment(Request $request, ElearningMaterial $material)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        ElearningMaterialComment::create([
            'uuid' => (string) Str::uuid(),
            'elearning_material_id' => $material->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    /**
     * Delete a comment in material discussion.
     */
    public function deleteComment(ElearningMaterial $material, ElearningMaterialComment $comment)
    {
        if ($comment->elearning_material_id !== $material->id) {
            abort(400);
        }

        $isTeacher = in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin']) && auth()->id() === $material->elearningClass->teacher_id;
        $isAdmin = in_array(auth()->user()->role, ['admin', 'superadmin']);

        if (auth()->id() !== $comment->user_id && !$isTeacher && !$isAdmin) {
            abort(403, 'Akses ditolak.');
        }

        $comment->delete();

        return back()->with('success', 'Komentar diskusi berhasil dihapus.');
    }

    /**
     * View virtual classroom overall student progress dashboard (Guru/Admin only).
     */
    public function classProgress(ElearningClass $elearningClass)
    {
        $user = auth()->user();

        // Security check
        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
            abort(403);
        }

        $progressData = $this->elearningService->getClassProgressSummary($elearningClass->id);

        return view('elearning.class_progress', compact('elearningClass', 'progressData'));
    }

    /**
     * View detailed learning progress checklist for a specific student (Guru/Admin only).
     */
    public function studentProgress(ElearningClass $elearningClass, Student $student)
    {
        $user = auth()->user();

        // Security check
        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
            abort(403);
        }
        if ($student->classroom_id !== $elearningClass->classroom_id) {
            abort(400);
        }

        $progressData = $this->elearningService->getStudentProgress($elearningClass->id, $student->id);

        return view('elearning.student_progress', compact('elearningClass', 'student', 'progressData'));
    }

    /**
     * Duplicate a virtual classroom.
     */
    public function duplicateClass(ElearningClass $elearningClass)
    {
        $user = auth()->user();

        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
            abort(403);
        }

        $duplicated = $this->elearningService->duplicateClass($elearningClass->id, auth()->id());

        return redirect()->route('elearning.class.show', $duplicated->uuid)
            ->with('success', 'Kelas virtual berhasil diduplikasi ke kelas baru.');
    }

    /**
     * Archive a virtual classroom.
     */
    public function archiveClass(ElearningClass $elearningClass)
    {
        $user = auth()->user();

        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
            abort(403);
        }

        $this->elearningService->toggleArchive($elearningClass->id);

        return redirect()->route('elearning.index')
            ->with('success', 'Kelas virtual berhasil diarsipkan.');
    }

    /**
     * Unarchive/Restore a virtual classroom.
     */
    public function unarchiveClass(ElearningClass $elearningClass)
    {
        $user = auth()->user();

        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
            abort(403);
        }

        $this->elearningService->toggleArchive($elearningClass->id);

        return redirect()->route('elearning.class.show', $elearningClass->uuid)
            ->with('success', 'Kelas virtual berhasil dipulihkan dari arsip.');
    }
}
