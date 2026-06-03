<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\ScheduleTime;
use App\Models\Subject;
use App\Models\User;
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
        $selectedClassroom = null;
        $schedules = [];
        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();

        if ($request->has('classroom_id')) {
            $selectedClassroom = Classroom::findOrFail($request->classroom_id);
            $schedules = Schedule::where('classroom_id', $selectedClassroom->id)
                ->where('academic_year_id', AcademicYear::current()?->id)
                ->with(['subject', 'teacher'])
                ->get();
        }

        $subjects = Subject::all();
        $teachers = User::where('role', 'guru')->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        return view('schedules.grid', compact(
            'classrooms', 'selectedClassroom', 'schedules',
            'activePreset', 'subjects', 'teachers', 'days'
        ));
    }

    /**
     * Store a new schedule slot with conflict check.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'session_start' => 'required|integer|min:1|max:11',
            'session_end' => 'required|integer|min:1|max:11|gte:session_start',
        ]);

        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        // Conflict Check: Is Teacher busy at this time?
        $conflict = Schedule::where('academic_year_id', $activeYear->id)
            ->where('day', $validated['day'])
            ->where('teacher_id', $validated['teacher_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('session_start', [$validated['session_start'], $validated['session_end']])
                    ->orWhereBetween('session_end', [$validated['session_start'], $validated['session_end']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('session_start', '<=', $validated['session_start'])
                            ->where('session_end', '>=', $validated['session_end']);
                    });
            })
            ->first();

        if ($conflict) {
            return back()->with('error', "Konflik Jadwal! Guru tersebut sudah mengajar di kelas {$conflict->classroom->name} pada jam tersebut.");
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
}
