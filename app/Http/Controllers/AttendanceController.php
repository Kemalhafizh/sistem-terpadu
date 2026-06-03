<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /** @var AttendanceService */
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * @param  int|string|null  $classroomId
     * @return Collection
     */
    private function getStudentsByClassroom($classroomId)
    {
        if (! $classroomId) {
            return collect([]);
        }

        return Student::with('user')
            ->where('classroom_id', $classroomId)
            ->get()
            ->sortBy(fn ($s) => $s->user?->name);
    }

    public function index(Request $request)
    {
        $classrooms = Classroom::all();
        $selectedClassroomId = $request->input('classroom_id');
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $attendances = collect([]);
        $summary = null;
        $students = collect([]);

        if ($selectedClassroomId) {
            $students = $this->getStudentsByClassroom($selectedClassroomId);

            $attendances = Attendance::with('student')
                ->where('classroom_id', $selectedClassroomId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereNull('schedule_id')
                ->get()
                ->groupBy('student_id');

            // Summary counts
            $baseQuery = Attendance::where('classroom_id', $selectedClassroomId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereNull('schedule_id');

            $summaryData = $baseQuery->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $summary = (object) [
                'hadir' => $summaryData['H'] ?? 0,
                'sakit' => $summaryData['S'] ?? 0,
                'izin' => $summaryData['I'] ?? 0,
                'alpa' => $summaryData['A'] ?? 0,
                'total' => $summaryData->sum(),
            ];
        }

        return view('attendances.index', compact(
            'classrooms', 'selectedClassroomId', 'month', 'year',
            'attendances', 'summary', 'students'
        ));
    }

    public function create(Request $request)
    {
        $classrooms = Classroom::all();
        $selectedClassroomId = $request->input('classroom_id');
        $date = $request->input('date', date('Y-m-d'));

        $students = collect([]);
        $existingAttendances = collect([]);

        if ($selectedClassroomId) {
            $students = $this->getStudentsByClassroom($selectedClassroomId);
            $existingAttendances = Attendance::where('classroom_id', $selectedClassroomId)
                ->where('date', $date)
                ->whereNull('schedule_id') // Presensi harian
                ->get()
                ->keyBy('student_id');
        }

        return view('attendances.create', compact('classrooms', 'selectedClassroomId', 'date', 'students', 'existingAttendances'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date|before_or_equal:today',
            'attendances' => 'required|array',
        ]);

        try {
            $this->attendanceService->recordBulkAttendance(
                (int) $request->classroom_id,
                $request->date,
                $request->attendances,
                Auth::id()
            );

            return redirect()->route('attendances.create', [
                'classroom_id' => $request->classroom_id,
                'date' => $request->date,
            ])->with('success', 'Data presensi massal berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
