<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Classroom;
use App\Models\EventAttendanceEvent;
use App\Models\Student;
use App\Models\User;
use App\Services\EventAttendanceService;
use App\Support\Access\StudentDataAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EventAttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(EventAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display listing of events.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Redirect students and parents to personal rekap
        if ($user->hasRole('student') || $user->hasRole('parent')) {
            return redirect()->route('event-attendance.rekap');
        }

        $query = EventAttendanceEvent::query();

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('month')) {
            $monthParts = explode('-', $request->month);
            if (count($monthParts) === 2) {
                $query->whereYear('event_date', $monthParts[0])
                    ->whereMonth('event_date', $monthParts[1]);
            }
        }

        $events = $query->withCount([
            'records as total_attendees',
            'records as present_count' => function ($q) {
                $q->whereIn('status', ['hadir', 'terlambat']);
            },
        ])->latest('event_date')->paginate(10);

        return view('event-attendance.index', compact('events'));
    }

    /**
     * Show form to create event.
     */
    public function create()
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES])) {
            abort(403, 'Akses ditolak.');
        }

        $classrooms = Classroom::all();

        // Load teachers list for target specific group if needed
        $teachers = User::whereIn('role', StudentDataAccess::TEACHER_ROLES)->get();
        $students = Student::with('user')->get();

        return view('event-attendance.create', compact('classrooms', 'teachers', 'students'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES])) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:upacara,sholat_dhuha,sholat_dzuhur,kultum,phbi,rapat_guru,osis,class_meeting,lainnya',
            'event_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'target' => 'required|in:all_students,all_teachers,specific_class,specific_group',
            'target_ids' => 'nullable|array',
        ]);

        $validated['created_by'] = $user->id;

        $event = $this->attendanceService->createEvent($validated);

        return redirect()->route('event-attendance.index')
            ->with('success', "Kegiatan '{$event->name}' berhasil dibuat dan draf kehadiran peserta telah diinisialisasi.");
    }

    /**
     * Display details of a specific event.
     */
    public function show(EventAttendanceEvent $event)
    {
        $user = auth()->user();
        if ($user->hasRole('student') || $user->hasRole('parent')) {
            // Check ownership/target constraints for security
            $hasAccess = false;
            if ($user->hasRole('student')) {
                $hasAccess = $event->records()->where('user_id', $user->id)->exists();
            } else {
                $childrenIds = Student::where('parent_id', $user->id)->pluck('user_id')->toArray();
                $hasAccess = $event->records()->whereIn('user_id', $childrenIds)->exists();
            }
            if (! $hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke data kegiatan ini.');
            }
        }

        $records = $event->records()->with('user')->get();

        // Calculate statistics for charts
        $stats = [
            'hadir' => $records->where('status', 'hadir')->count(),
            'terlambat' => $records->where('status', 'terlambat')->count(),
            'izin' => $records->where('status', 'izin')->count(),
            'sakit' => $records->where('status', 'sakit')->count(),
            'alpa' => $records->where('status', 'alpa')->count(),
        ];

        return view('event-attendance.show', compact('event', 'records', 'stats'));
    }

    /**
     * Show form to input/edit attendance.
     */
    public function inputAttendance(EventAttendanceEvent $event)
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES]) && $event->created_by !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $records = $event->records()->with('user.student.classroom')->get()->sortBy(function ($r) {
            return $r->user?->name;
        });

        return view('event-attendance.input', compact('event', 'records'));
    }

    /**
     * Save/update attendance records.
     */
    public function storeAttendance(Request $request, EventAttendanceEvent $event)
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES]) && $event->created_by !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'records' => 'required|array',
            'records.*.user_id' => 'required|exists:users,id',
            'records.*.status' => 'required|in:hadir,izin,sakit,alpa,terlambat',
            'records.*.check_in_time' => 'nullable',
            'records.*.notes' => 'nullable|string',
        ]);

        $this->attendanceService->batchStoreAttendance($event, $request->records);

        return redirect()->route('event-attendance.show', $event->id)
            ->with('success', 'Data presensi kegiatan berhasil disimpan.');
    }

    /**
     * Display rekapitulasi data.
     */
    public function rekap(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            $summary = $this->attendanceService->getStudentEventAttendanceSummary($student);

            return view('event-attendance.rekap', compact('student', 'summary'));
        }

        if ($user->hasRole('parent')) {
            $students = Student::where('parent_id', $user->id)->get();
            if ($students->isEmpty()) {
                return view('event-attendance.rekap', ['student' => null, 'summary' => null, 'students' => $students]);
            }
            $selectedStudentId = $request->get('student_id', $students->first()->id);
            $student = $students->firstWhere('id', $selectedStudentId) ?? $students->first();
            $summary = $this->attendanceService->getStudentEventAttendanceSummary($student);

            return view('event-attendance.rekap', compact('student', 'summary', 'students'));
        }

        // Guru/Admin: Can view rekap of any selected student
        $classrooms = Classroom::all();
        $classroomId = $request->get('classroom_id', Classroom::first()?->id);
        $classroom = Classroom::find($classroomId);

        $students = $classroom ? Student::where('classroom_id', $classroom->id)->with('user')->get() : collect();
        $selectedStudentId = $request->get('student_id', $students->first()?->id);
        $student = $selectedStudentId ? Student::find($selectedStudentId) : $students->first();

        $summary = $student ? $this->attendanceService->getStudentEventAttendanceSummary($student) : null;

        return view('event-attendance.rekap', compact('classrooms', 'classroom', 'students', 'student', 'summary'));
    }

    /**
     * Export event attendance report to PDF.
     */
    public function exportPdf(EventAttendanceEvent $event)
    {
        $user = auth()->user();
        if ($user->hasRole('student') || $user->hasRole('parent')) {
            abort(403, 'Akses ditolak.');
        }

        $records = $event->records()->with('user.student.classroom')->get()->sortBy(function ($r) {
            return $r->user?->name;
        });

        $stats = [
            'hadir' => $records->where('status', 'hadir')->count(),
            'terlambat' => $records->where('status', 'terlambat')->count(),
            'izin' => $records->where('status', 'izin')->count(),
            'sakit' => $records->where('status', 'sakit')->count(),
            'alpa' => $records->where('status', 'alpa')->count(),
        ];

        $pdf = Pdf::loadView('event-attendance.pdf_export', compact('event', 'records', 'stats'));

        $fileName = str_replace(' ', '_', strtolower($event->name));

        return $pdf->download("rekap_presensi_{$fileName}.pdf");
    }
}
