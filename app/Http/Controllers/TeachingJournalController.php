<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeachingJournalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', TeachingJournal::class);

        $user = Auth::user();
        $query = TeachingJournal::with(['teacher', 'subject', 'classroom']);

        // Filter Hak Akses
        if ($user->role === 'guru') {
            $query->forTeacher($user->id);
        } else {
            // Admin/Wakakur/Kepsek bisa filter per guru
            if ($request->filled('teacher_id')) {
                $query->where('user_id', $request->teacher_id);
            }
        }

        // Filter Umum
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month') && $request->filled('year')) {
            $query->byMonth($request->month, $request->year);
        }

        $journals = $query->orderBy('teaching_date', 'desc')->paginate(15);
        $classrooms = Classroom::all();
        $teachers = User::where('role', 'guru')->get();

        return view('teaching-journals.index', compact('journals', 'classrooms', 'teachers'));
    }

    public function create()
    {
        $this->authorize('create', TeachingJournal::class);

        $user = Auth::user();
        $classrooms = Classroom::all();
        $subjects = $user->role === 'guru' ? $user->subjects : Subject::all();

        // LOGIKA AUTO-FILL JADWAL GURU
        $suggestedSchedule = null;
        if ($user->role === 'guru') {
            $daysMap = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $dayOfWeekIndo = $daysMap[now()->format('l')] ?? 'Senin';

            $suggestedSchedule = Schedule::where('teacher_id', $user->id)
                ->where('day', $dayOfWeekIndo)
                ->with(['classroom', 'subject'])
                ->get();
        }

        return view('teaching-journals.create', compact('classrooms', 'subjects', 'suggestedSchedule'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', TeachingJournal::class);

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teaching_date' => 'required|date|before_or_equal:today',
            'jam_ke' => 'nullable|integer|min:1|max:12',
            'topic' => 'required|string|max:255',
            'learning_activity' => 'nullable|string',
            'learning_method' => 'nullable|string|max:100',
            'obstacles' => 'nullable|string',
            'notes' => 'nullable|string',
            'students_present' => 'nullable|integer|min:0',
            'students_absent' => 'nullable|integer|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'action' => 'required|in:draft,submit',
        ]);

        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return back()->with('error', 'Tahun Akademik aktif tidak ditemukan. Harap hubungi administrator.');
        }

        if (! Schedule::teaches($request->user(), (int) $request->classroom_id, (int) $request->subject_id, $activeYear->id)) {
            abort(403, 'Anda tidak mengajar mata pelajaran ini di kelas tersebut pada tahun ajaran aktif.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('teaching-journals', 'public');
        }

        TeachingJournal::create([
            'user_id' => Auth::id(),
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'academic_year_id' => $activeYear->id,
            'teaching_date' => $request->teaching_date,
            'jam_ke' => $request->jam_ke,
            'topic' => $request->topic,
            'learning_activity' => $request->learning_activity,
            'learning_method' => $request->learning_method,
            'obstacles' => $request->obstacles,
            'notes' => $request->notes,
            'students_present' => $request->students_present,
            'students_absent' => $request->students_absent,
            'attachment_path' => $attachmentPath,
            'status' => $request->action === 'submit' ? 'submitted' : 'draft',
        ]);

        return redirect()->route('teaching-journals.index')->with('success', 'Jurnal mengajar berhasil disimpan.');
    }

    public function show(TeachingJournal $teachingJournal)
    {
        $this->authorize('view', $teachingJournal);

        return view('teaching-journals.show', compact('teachingJournal'));
    }

    public function edit(TeachingJournal $teachingJournal)
    {
        $this->authorize('update', $teachingJournal);

        $classrooms = Classroom::all();
        $subjects = Auth::user()->role === 'guru' ? Auth::user()->subjects : Subject::all();

        return view('teaching-journals.edit', compact('teachingJournal', 'classrooms', 'subjects'));
    }

    public function update(Request $request, TeachingJournal $teachingJournal)
    {
        $this->authorize('update', $teachingJournal);

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teaching_date' => 'required|date|before_or_equal:today',
            'jam_ke' => 'nullable|integer|min:1|max:12',
            'topic' => 'required|string|max:255',
            'learning_activity' => 'nullable|string',
            'learning_method' => 'nullable|string|max:100',
            'obstacles' => 'nullable|string',
            'notes' => 'nullable|string',
            'students_present' => 'nullable|integer|min:0',
            'students_absent' => 'nullable|integer|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'action' => 'required|in:draft,submit',
        ]);

        // Moving a journal to another class or subject needs the same right as filing it there.
        if (! Schedule::teaches($request->user(), (int) $request->classroom_id, (int) $request->subject_id, $teachingJournal->academic_year_id)) {
            abort(403, 'Anda tidak mengajar mata pelajaran ini di kelas tersebut pada tahun ajaran aktif.');
        }

        if ($request->hasFile('attachment')) {
            if ($teachingJournal->attachment_path) {
                Storage::disk('public')->delete($teachingJournal->attachment_path);
            }
            $teachingJournal->attachment_path = $request->file('attachment')->store('teaching-journals', 'public');
        }

        $teachingJournal->update([
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'teaching_date' => $request->teaching_date,
            'jam_ke' => $request->jam_ke,
            'topic' => $request->topic,
            'learning_activity' => $request->learning_activity,
            'learning_method' => $request->learning_method,
            'obstacles' => $request->obstacles,
            'notes' => $request->notes,
            'students_present' => $request->students_present,
            'students_absent' => $request->students_absent,
            'status' => $request->action === 'submit' ? 'submitted' : 'draft',
        ]);

        return redirect()->route('teaching-journals.index')->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingJournal $teachingJournal)
    {
        $this->authorize('delete', $teachingJournal);

        if ($teachingJournal->attachment_path) {
            Storage::disk('public')->delete($teachingJournal->attachment_path);
        }

        $teachingJournal->delete();

        return redirect()->route('teaching-journals.index')->with('success', 'Jurnal mengajar berhasil dihapus.');
    }

    // REVIEW WORKFLOW BY CO-ORDINATOR
    public function review(Request $request, TeachingJournal $teachingJournal)
    {
        $this->authorize('review', $teachingJournal);

        $request->validate([
            'reviewer_notes' => 'nullable|string',
            'status' => 'required|in:reviewed,draft', // 'draft' berarti dikembalikan ke guru untuk direvisi
        ]);

        $teachingJournal->update([
            'status' => $request->status,
            'reviewer_notes' => $request->reviewer_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $statusText = $request->status === 'reviewed' ? 'disetujui' : 'dikembalikan untuk direvisi';

        return redirect()->route('teaching-journals.show', $teachingJournal->uuid)->with('success', "Jurnal mengajar berhasil ditinjau dan {$statusText}.");
    }

    // EKSPOR PDF REKAPITULASI BULANAN GURU
    public function exportPdf(Request $request)
    {
        $this->authorize('export', TeachingJournal::class);

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $query = TeachingJournal::with(['teacher', 'subject', 'classroom'])
            ->byMonth($request->month, $request->year)
            ->where('status', 'reviewed');

        if ($request->filled('teacher_id')) {
            $query->where('user_id', $request->teacher_id);
        }

        $journals = $query->orderBy('teaching_date', 'asc')->get();

        if ($journals->isEmpty()) {
            return back()->with('error', 'Tidak ada data jurnal mengajar yang disetujui pada bulan tersebut.');
        }

        $pdf = Pdf::loadView('teaching-journals.pdf', compact('journals', 'request'));

        return $pdf->download("rekap_jurnal_mengajar_{$request->month}_{$request->year}.pdf");
    }
}
