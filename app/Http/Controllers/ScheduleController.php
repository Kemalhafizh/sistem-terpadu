<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\ScheduleTime;
use App\Models\Subject;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedule presets.
     */
    public function index()
    {
        $presets = SchedulePreset::withCount('times')->latest()->get();

        return view('schedules.presets.index', compact('presets'));
    }

    /**
     * Show the grid for a specific classroom.
     */
    public function showGrid(Request $request)
    {
        $classrooms = Classroom::all();
        $rooms = Room::all();
        $selectedClassroom = null;
        $schedules = [];
        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();

        if ($request->has('classroom_id')) {
            $selectedClassroom = Classroom::find($request->classroom_id);
            if ($selectedClassroom) {
                $schedules = Schedule::where('classroom_id', $selectedClassroom->id)
                    ->where('academic_year_id', AcademicYear::current()?->id)
                    ->with(['subject', 'teacher', 'room'])
                    ->get();
            }
        }

        $subjects = Subject::all();
        $teachers = User::where('role', 'guru')->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        return view('schedules.grid', compact(
            'classrooms', 'rooms', 'selectedClassroom', 'schedules',
            'activePreset', 'subjects', 'teachers', 'days'
        ));
    }

    /**
     * Store a new schedule slot with three-way conflict checking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'session_start' => 'required|integer|min:1|max:11',
            'session_end' => 'required|integer|min:1|max:11|gte:session_start',
        ]);

        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        // Helper to check time overlaps
        $overlapCheck = function ($query) use ($validated) {
            $query->where('day', $validated['day'])
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('session_start', [$validated['session_start'], $validated['session_end']])
                        ->orWhereBetween('session_end', [$validated['session_start'], $validated['session_end']])
                        ->orWhere(function ($sub) use ($validated) {
                            $sub->where('session_start', '<=', $validated['session_start'])
                                ->where('session_end', '>=', $validated['session_end']);
                        });
                });
        };

        // 1. Teacher Conflict Check
        $teacherConflict = Schedule::where('academic_year_id', $activeYear->id)
            ->where('teacher_id', $validated['teacher_id'])
            ->where($overlapCheck)
            ->first();

        if ($teacherConflict) {
            return back()->with('error', "Konflik Guru! Guru tersebut sudah mengajar di kelas {$teacherConflict->classroom->name} pada jam tersebut.");
        }

        // 2. Classroom Conflict Check
        $classConflict = Schedule::where('academic_year_id', $activeYear->id)
            ->where('classroom_id', $validated['classroom_id'])
            ->where($overlapCheck)
            ->first();

        if ($classConflict) {
            return back()->with('error', "Konflik Kelas! Kelas {$classConflict->classroom->name} sudah memiliki jadwal untuk mata pelajaran {$classConflict->subject->name} pada jam tersebut.");
        }

        // 3. Room Conflict Check (if room is assigned)
        if (! empty($validated['room_id'])) {
            $roomConflict = Schedule::where('academic_year_id', $activeYear->id)
                ->where('room_id', $validated['room_id'])
                ->where($overlapCheck)
                ->first();

            if ($roomConflict) {
                return back()->with('error', "Konflik Ruangan! Ruangan {$roomConflict->room->name} sudah digunakan oleh kelas {$roomConflict->classroom->name} pada jam tersebut.");
            }
        }

        Schedule::create(array_merge($validated, ['academic_year_id' => $activeYear->id]));

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Delete a schedule slot.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Manage Preset Times.
     */
    public function storePreset(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        SchedulePreset::create(['name' => $request->name]);

        return back()->with('success', 'Preset berhasil dibuat.');
    }

    public function activatePreset(SchedulePreset $preset)
    {
        SchedulePreset::query()->update(['is_active' => false]);
        $preset->update(['is_active' => true]);

        return back()->with('success', 'Preset aktif telah diubah.');
    }

    public function storeTime(Request $request, SchedulePreset $preset)
    {
        $validated = $request->validate([
            'session_number' => 'required|integer|min:1|max:11',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        ScheduleTime::updateOrCreate(
            ['schedule_preset_id' => $preset->id, 'session_number' => $validated['session_number']],
            $validated
        );

        return back()->with('success', 'Waktu sesi berhasil disimpan.');
    }

    /**
     * Show schedules from a teacher's perspective.
     */
    public function teacherSchedule(Request $request)
    {
        $teachers = User::where('role', 'guru')->get();
        $selectedTeacher = null;
        $schedules = [];
        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();

        // If user is a teacher, default to themselves if no specific selection
        $currentUser = auth()->user();
        if ($currentUser->role === 'guru' && ! $request->has('teacher_id')) {
            $selectedTeacher = $currentUser;
        } elseif ($request->filled('teacher_id')) {
            $selectedTeacher = User::findOrFail($request->teacher_id);
        }

        if ($selectedTeacher) {
            $schedules = Schedule::where('teacher_id', $selectedTeacher->id)
                ->where('academic_year_id', AcademicYear::current()?->id)
                ->with(['subject', 'classroom', 'room'])
                ->get();
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        return view('schedules.teacher-view', compact('teachers', 'selectedTeacher', 'schedules', 'activePreset', 'days'));
    }

    /**
     * Show schedules from a room's perspective.
     */
    public function roomSchedule(Request $request)
    {
        $rooms = Room::all();
        $selectedRoom = null;
        $schedules = [];
        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();

        if ($request->filled('room_id')) {
            $selectedRoom = Room::findOrFail($request->room_id);
            $schedules = Schedule::where('room_id', $selectedRoom->id)
                ->where('academic_year_id', AcademicYear::current()?->id)
                ->with(['subject', 'classroom', 'teacher'])
                ->get();
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        return view('schedules.room-view', compact('rooms', 'selectedRoom', 'schedules', 'activePreset', 'days'));
    }

    /**
     * Audit and detect all current schedule conflicts in the database.
     */
    public function detectConflicts()
    {
        $activeYear = AcademicYear::current();
        $schedules = $activeYear ? Schedule::where('academic_year_id', $activeYear->id)
            ->with(['classroom', 'subject', 'teacher', 'room'])
            ->get() : collect();

        $conflicts = [];

        foreach ($schedules as $s1) {
            foreach ($schedules as $s2) {
                if ($s1->id >= $s2->id) {
                    continue;
                }
                if ($s1->day !== $s2->day) {
                    continue;
                }

                // Check overlap
                $overlap = max($s1->session_start, $s2->session_start) <= min($s1->session_end, $s2->session_end);
                if (! $overlap) {
                    continue;
                }

                // 1. Teacher conflict
                if ($s1->teacher_id === $s2->teacher_id) {
                    $conflicts[] = [
                        'type' => 'Guru',
                        'description' => "Guru {$s1->teacher->name} terdaftar mengajar di Kelas {$s1->classroom->name} ({$s1->subject->name}) dan Kelas {$s2->classroom->name} ({$s2->subject->name}) pada jam sesi yang bertabrakan.",
                        'schedule_1' => $s1,
                        'schedule_2' => $s2,
                    ];
                }

                // 2. Classroom conflict
                if ($s1->classroom_id === $s2->classroom_id) {
                    $conflicts[] = [
                        'type' => 'Kelas',
                        'description' => "Kelas {$s1->classroom->name} memiliki jam pelajaran ganda antara {$s1->subject->name} (Guru {$s1->teacher->name}) dan {$s2->subject->name} (Guru {$s2->teacher->name}) pada jam sesi yang bertabrakan.",
                        'schedule_1' => $s1,
                        'schedule_2' => $s2,
                    ];
                }

                // 3. Room conflict
                if ($s1->room_id && $s2->room_id && $s1->room_id === $s2->room_id) {
                    $conflicts[] = [
                        'type' => 'Ruangan',
                        'description' => "Ruangan {$s1->room->name} digunakan secara bersamaan oleh Kelas {$s1->classroom->name} ({$s1->subject->name}) dan Kelas {$s2->classroom->name} ({$s2->subject->name}) pada jam sesi yang bertabrakan.",
                        'schedule_1' => $s1,
                        'schedule_2' => $s2,
                    ];
                }
            }
        }

        return view('schedules.conflicts', compact('conflicts'));
    }

    /**
     * Print schedule for a specific classroom to PDF.
     */
    public function printClassSchedule(Classroom $classroom)
    {
        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();
        $schedules = Schedule::where('classroom_id', $classroom->id)
            ->where('academic_year_id', AcademicYear::current()?->id)
            ->with(['subject', 'teacher', 'room'])
            ->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $pdf = Pdf::loadView('schedules.print-class', compact('classroom', 'schedules', 'activePreset', 'days'))->setPaper('a4', 'landscape');
        $fileName = str_replace(' ', '_', strtolower($classroom->name));

        return $pdf->download("jadwal_kelas_{$fileName}.pdf");
    }

    /**
     * Print schedule for a specific teacher to PDF.
     */
    public function printTeacherSchedule(User $teacher)
    {
        if ($teacher->role !== 'guru') {
            abort(404, 'User bukan berstatus guru.');
        }

        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('academic_year_id', AcademicYear::current()?->id)
            ->with(['subject', 'classroom', 'room'])
            ->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $pdf = Pdf::loadView('schedules.print-teacher', compact('teacher', 'schedules', 'activePreset', 'days'))->setPaper('a4', 'landscape');
        $fileName = str_replace(' ', '_', strtolower($teacher->name));

        return $pdf->download("jadwal_guru_{$fileName}.pdf");
    }

    /**
     * Print master schedule for the entire school.
     */
    public function printMasterSchedule()
    {
        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();
        $classrooms = Classroom::with('teacher')->get();
        $schedules = Schedule::where('academic_year_id', AcademicYear::current()?->id)
            ->with(['subject', 'teacher', 'room', 'classroom'])
            ->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $pdf = Pdf::loadView('schedules.print-master', compact('classrooms', 'schedules', 'activePreset', 'days'))->setPaper('a4', 'landscape');

        return $pdf->download('jadwal_master_sekolah.pdf');
    }
}
