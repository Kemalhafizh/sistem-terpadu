<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonPlan;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CurriculumController extends Controller
{
    // =========================================================
    //  DASHBOARD & REPORT
    // =========================================================

    public function index()
    {
        $this->authorize('viewDashboard', LessonPlan::class);
        $user = auth()->user();
        $activeYear = AcademicYear::current();

        if (!$activeYear) {
            return redirect()->route('dashboard')->with('error', 'Tahun akademik aktif tidak ditemukan.');
        }

        $rppQuery = LessonPlan::where('academic_year_id', $activeYear->id);
        if ($user->role === 'guru') {
            $rppQuery->where('user_id', $user->id);
        }

        $rppStats = [
            'total' => (clone $rppQuery)->count(),
            'approved' => (clone $rppQuery)->where('status', 'approved')->count(),
            'submitted' => (clone $rppQuery)->where('status', 'submitted')->count(),
            'draft' => (clone $rppQuery)->where('status', 'draft')->count(),
            'revision' => (clone $rppQuery)->where('status', 'revision_needed')->count(),
        ];

        $syllabusQuery = Syllabus::where('academic_year_id', $activeYear->id);
        $syllabiStats = [
            'total' => (clone $syllabusQuery)->count(),
            'approved' => (clone $syllabusQuery)->where('status', 'approved')->count(),
            'draft' => (clone $syllabusQuery)->where('status', 'draft')->count(),
            'revision' => (clone $syllabusQuery)->where('status', 'revision_needed')->count(),
        ];

        $teachers = User::where('role', 'guru')->get();
        $teachersProgress = [];
        foreach ($teachers as $t) {
            $totalRpp = LessonPlan::where('user_id', $t->id)->where('academic_year_id', $activeYear->id)->count();
            $approvedRpp = LessonPlan::where('user_id', $t->id)->where('academic_year_id', $activeYear->id)->where('status', 'approved')->count();
            $rate = $totalRpp > 0 ? round(($approvedRpp / $totalRpp) * 100) : 0;
            $teachersProgress[] = [
                'name' => $t->name,
                'total' => $totalRpp,
                'approved' => $approvedRpp,
                'rate' => $rate
            ];
        }

        usort($teachersProgress, fn($a, $b) => $a['rate'] <=> $b['rate']);

        return view('curriculum.index', compact('rppStats', 'syllabiStats', 'teachersProgress', 'activeYear'));
    }

    public function completionReport()
    {
        $this->authorize('downloadReport', LessonPlan::class);
        $activeYear = AcademicYear::current();
        
        $subjects = Subject::all();
        $syllabiData = [];
        foreach ($subjects as $sub) {
            $syllabus = Syllabus::where('subject_id', $sub->id)
                ->where('academic_year_id', $activeYear->id)
                ->first();
            $syllabiData[] = [
                'code' => $sub->code,
                'name' => $sub->name,
                'status' => $syllabus ? $syllabus->status : 'belum_unggah',
                'uploader' => $syllabus ? $syllabus->uploader->name : '-',
                'notes' => $syllabus ? $syllabus->notes : ''
            ];
        }

        $teachers = User::where('role', 'guru')->get();
        $rppData = [];
        foreach ($teachers as $t) {
            $totalRpp = LessonPlan::where('user_id', $t->id)->where('academic_year_id', $activeYear->id)->count();
            $approvedRpp = LessonPlan::where('user_id', $t->id)->where('academic_year_id', $activeYear->id)->where('status', 'approved')->count();
            $rppData[] = [
                'name' => $t->name,
                'total' => $totalRpp,
                'approved' => $approvedRpp,
                'rate' => $totalRpp > 0 ? round(($approvedRpp / $totalRpp) * 100) : 0
            ];
        }

        $pdf = Pdf::loadView('curriculum.pdf_report', compact('syllabiData', 'rppData', 'activeYear'));
        $safeYear = str_replace(['/', '\\'], '-', $activeYear->year);
        return $pdf->setPaper('a4', 'portrait')->download('Laporan_Kelengkapan_Kurikulum_' . $safeYear . '.pdf');
    }

    // =========================================================
    //  BANK SILABUS
    // =========================================================

    public function syllabiIndex()
    {
        $this->authorize('manageSyllabus', Syllabus::class);
        $activeYear = AcademicYear::current();

        $subjects = Subject::all();
        $syllabi = Syllabus::with(['subject', 'uploader', 'approver'])
            ->where('academic_year_id', $activeYear->id)
            ->get();

        return view('curriculum.syllabi.index', compact('syllabi', 'subjects', 'activeYear'));
    }

    public function syllabusStore(Request $request)
    {
        $this->authorize('manageSyllabus', Syllabus::class);
        $activeYear = AcademicYear::current();

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'required|in:X,XI,XII',
            'semester' => 'required|in:ganjil,genap',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $exists = Syllabus::where('subject_id', $request->subject_id)
            ->where('academic_year_id', $activeYear->id)
            ->where('grade_level', $request->grade_level)
            ->where('semester', $request->semester)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Silabus untuk Mata Pelajaran, Tingkat Kelas, dan Semester ini sudah terdaftar.');
        }

        $path = $request->file('file')->store('uploads/curriculum/syllabi', 'public');

        Syllabus::create([
            'subject_id' => $request->subject_id,
            'academic_year_id' => $activeYear->id,
            'grade_level' => $request->grade_level,
            'semester' => $request->semester,
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'status' => 'draft',
        ]);

        return back()->with('success', 'Silabus berhasil diunggah sebagai Draft.');
    }

    public function syllabusReview(Request $request, Syllabus $syllabus)
    {
        $this->authorize('reviewSyllabus', Syllabus::class);

        $request->validate([
            'status' => 'required|in:approved,revision_needed',
            'notes' => 'nullable|string',
        ]);

        $syllabus->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'approved_by' => auth()->id(),
            'approved_at' => $request->status === 'approved' ? now() : null,
        ]);

        $message = $request->status === 'approved' ? 'Silabus berhasil disetujui.' : 'Silabus dikembalikan untuk direvisi.';
        return back()->with('success', $message);
    }

    public function syllabusDestroy(Syllabus $syllabus)
    {
        $this->authorize('manageSyllabus', Syllabus::class);
        
        if (auth()->user()->role === 'guru' && $syllabus->uploaded_by !== auth()->id()) {
            abort(403);
        }
        if ($syllabus->status === 'approved' && auth()->user()->role === 'guru') {
            return back()->with('error', 'Dokumen silabus yang telah disetujui tidak dapat dihapus.');
        }

        Storage::disk('public')->delete($syllabus->file_path);
        $syllabus->delete();

        return back()->with('success', 'Silabus berhasil dihapus.');
    }

    // =========================================================
    //  RENCANA PELAKSANAAN PEMBELAJARAN (RPP)
    // =========================================================

    public function lessonPlansIndex(Request $request)
    {
        $this->authorize('viewLessonPlanList', LessonPlan::class);
        $user = auth()->user();
        $activeYear = AcademicYear::current();

        $query = LessonPlan::with(['subject', 'classroom', 'user'])
            ->where('academic_year_id', $activeYear->id);

        if ($user->role === 'guru') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lessonPlans = $query->orderBy('week_number', 'asc')->paginate(15);
        $subjects = Subject::all();

        return view('curriculum.lesson-plans.index', compact('lessonPlans', 'subjects'));
    }

    public function lessonPlanCreate()
    {
        $this->authorize('createLessonPlan', LessonPlan::class);
        $subjects = Subject::all();
        $classrooms = Classroom::all();

        return view('curriculum.lesson-plans.create', compact('subjects', 'classrooms'));
    }

    public function lessonPlanStore(Request $request)
    {
        $this->authorize('createLessonPlan', LessonPlan::class);
        $activeYear = AcademicYear::current();

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'topic' => 'required|string|max:255',
            'week_number' => 'required|integer|min:1|max:52',
            'competency_standards' => 'nullable|string',
            'learning_objectives' => 'required|string',
            'learning_activities' => 'required|string',
            'assessment_method' => 'nullable|string',
            'resources' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('uploads/curriculum/rpp', 'public');
        }

        LessonPlan::create([
            'user_id' => auth()->id(),
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'academic_year_id' => $activeYear->id,
            'topic' => $request->topic,
            'week_number' => $request->week_number,
            'competency_standards' => $request->competency_standards,
            'learning_objectives' => $request->learning_objectives,
            'learning_activities' => $request->learning_activities,
            'assessment_method' => $request->assessment_method,
            'resources' => $request->resources,
            'file_path' => $filePath,
            'status' => 'draft',
        ]);

        return redirect()->route('curriculum.lesson-plans.index')->with('success', 'Draf RPP berhasil disimpan.');
    }

    public function lessonPlanShow(LessonPlan $lessonPlan)
    {
        $this->authorize('viewLessonPlan', $lessonPlan);
        $lessonPlan->load(['subject', 'classroom', 'user', 'academicYear', 'reviewer']);
        return view('curriculum.lesson-plans.show', compact('lessonPlan'));
    }

    public function lessonPlanEdit(LessonPlan $lessonPlan)
    {
        $this->authorize('updateLessonPlan', $lessonPlan);
        $subjects = Subject::all();
        $classrooms = Classroom::all();

        return view('curriculum.lesson-plans.edit', compact('lessonPlan', 'subjects', 'classrooms'));
    }

    public function lessonPlanUpdate(Request $request, LessonPlan $lessonPlan)
    {
        $this->authorize('updateLessonPlan', $lessonPlan);

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'topic' => 'required|string|max:255',
            'week_number' => 'required|integer|min:1|max:52',
            'competency_standards' => 'nullable|string',
            'learning_objectives' => 'required|string',
            'learning_activities' => 'required|string',
            'assessment_method' => 'nullable|string',
            'resources' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data = [
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'topic' => $request->topic,
            'week_number' => $request->week_number,
            'competency_standards' => $request->competency_standards,
            'learning_objectives' => $request->learning_objectives,
            'learning_activities' => $request->learning_activities,
            'assessment_method' => $request->assessment_method,
            'resources' => $request->resources,
        ];

        if ($request->hasFile('file')) {
            if ($lessonPlan->file_path) {
                Storage::disk('public')->delete($lessonPlan->file_path);
            }
            $data['file_path'] = $request->file('file')->store('uploads/curriculum/rpp', 'public');
        }

        if ($lessonPlan->status === 'revision_needed') {
            $data['status'] = 'draft';
        }

        $lessonPlan->update($data);

        return redirect()->route('curriculum.lesson-plans.index')->with('success', 'RPP berhasil diperbarui.');
    }

    public function lessonPlanSubmit(LessonPlan $lessonPlan)
    {
        if (auth()->id() !== $lessonPlan->user_id) {
            abort(403);
        }
        if (!in_array($lessonPlan->status, ['draft', 'revision_needed'])) {
            return back()->with('error', 'RPP sudah diajukan atau disetujui.');
        }

        $lessonPlan->update(['status' => 'submitted']);
        return redirect()->route('curriculum.lesson-plans.index')->with('success', 'RPP berhasil diajukan untuk ditinjau.');
    }

    public function lessonPlanReview(Request $request, LessonPlan $lessonPlan)
    {
        $this->authorize('reviewLessonPlan', LessonPlan::class);

        $request->validate([
            'status' => 'required|in:approved,revision_needed',
            'reviewer_notes' => 'nullable|string',
        ]);

        $lessonPlan->update([
            'status' => $request->status,
            'reviewer_notes' => $request->reviewer_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $message = $request->status === 'approved' ? 'RPP berhasil disetujui.' : 'RPP dikembalikan untuk revisi.';
        return redirect()->route('curriculum.lesson-plans.show', $lessonPlan->uuid)->with('success', $message);
    }

    public function lessonPlanDestroy(LessonPlan $lessonPlan)
    {
        $this->authorize('deleteLessonPlan', $lessonPlan);

        if ($lessonPlan->file_path) {
            Storage::disk('public')->delete($lessonPlan->file_path);
        }
        $lessonPlan->delete();

        return redirect()->route('curriculum.lesson-plans.index')->with('success', 'Draf RPP berhasil dihapus.');
    }

    public function lessonPlanPdf(LessonPlan $lessonPlan)
    {
        $this->authorize('viewLessonPlan', $lessonPlan);
        $lessonPlan->load(['subject', 'classroom', 'user', 'academicYear', 'reviewer']);
        $pdf = Pdf::loadView('curriculum.lesson-plans.pdf_single', compact('lessonPlan'));
        $safeTopic = str_replace(['/', '\\', ' '], '_', $lessonPlan->topic);
        return $pdf->setPaper('a4', 'portrait')->download('RPP_' . $safeTopic . '.pdf');
    }
}
