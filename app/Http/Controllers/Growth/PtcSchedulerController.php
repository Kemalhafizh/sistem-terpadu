<?php

namespace App\Http\Controllers\Growth;

use App\Http\Controllers\Controller;
use App\Models\PtcSession;
use App\Models\PtcSlot;
use App\Models\User;
use App\Services\Growth\PtcSchedulerService;
use Illuminate\Http\Request;

class PtcSchedulerController extends Controller
{
    public function sessions()
    {
        $sessions = PtcSession::withCount('slots')->latest()->get();

        return view('ptc.sessions', compact('sessions'));
    }

    public function storeSession(Request $request, PtcSchedulerService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'academic_year' => 'required|string|max:9',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'slot_duration_minutes' => 'nullable|integer|min:5|max:60',
            'location' => 'nullable|string',
        ]);

        $session = $service->createSession($validated, auth()->id());

        return redirect()->route('growth.ptc.sessions')
            ->with('success', "Sesi Parent-Teacher Conference [{$session->title}] berhasil dibuat & slot tergenerasi!");
    }

    public function availableSlots(int $sessionId)
    {
        $session = PtcSession::findOrFail($sessionId);
        $slots = PtcSlot::with(['teacher', 'parent', 'student'])
            ->where('ptc_session_id', $sessionId)
            ->get();

        $students = User::where('role', 'student')->get();

        return view('ptc.available-slots', compact('session', 'slots', 'students'));
    }

    public function bookSlot(int $slotId, Request $request, PtcSchedulerService $service)
    {
        $slot = PtcSlot::findOrFail($slotId);

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'topic' => 'nullable|string|max:255',
        ]);

        $parentId = auth()->id();
        $service->bookSlot($slot, $parentId, $validated['student_id'], $validated['topic'] ?? null);

        return back()->with('success', 'Slot konsultasi PTC berhasil dibooking!');
    }

    public function teacherSchedule()
    {
        $teacherId = auth()->id();
        $slots = PtcSlot::with(['session', 'parent', 'student'])
            ->where('teacher_id', $teacherId)
            ->where('status', 'booked')
            ->get();

        return view('ptc.teacher-schedule', compact('slots'));
    }
}
