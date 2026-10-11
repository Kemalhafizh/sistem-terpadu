<?php

namespace App\Http\Controllers\Growth;

use App\Http\Controllers\Controller;
use App\Models\PtcSession;
use App\Models\PtcSlot;
use App\Models\Student;
use App\Services\Growth\PtcSchedulerService;
use Illuminate\Http\Request;

/**
 * Parent–teacher conferences: staff open a session (slots are generated for
 * every teacher), parents book a slot for one of their own children, and
 * teachers see their bookings. The routes carry those roles (PtcSession).
 */
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
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration_minutes' => 'nullable|integer|min:5|max:60',
            'location' => 'nullable|string|max:255',
        ]);

        $session = $service->createSession($validated, auth()->id());

        return redirect()->route('growth.ptc.sessions')
            ->with('success', __('Sesi konsultasi orang tua ":title" dibuat dengan :count slot.', ['title' => $session->title, 'count' => $session->slots()->count()]));
    }

    public function availableSlots(int $sessionId)
    {
        $session = PtcSession::findOrFail($sessionId);
        $slots = PtcSlot::with(['teacher', 'parent', 'student'])
            ->where('ptc_session_id', $sessionId)
            ->orderBy('start_time')
            ->get();

        // A parent picks among their own children only (this used to list every student account).
        $children = Student::ofFamily(auth()->user())->with('user')->get();

        return view('ptc.available-slots', compact('session', 'slots', 'children'));
    }

    public function bookSlot(int $slotId, Request $request, PtcSchedulerService $service)
    {
        $slot = PtcSlot::findOrFail($slotId);

        $validated = $request->validate([
            'student_id' => 'required|integer',
            'topic' => 'nullable|string|max:255',
        ]);

        $child = Student::ofFamily(auth()->user())->where('user_id', $validated['student_id'])->first();
        abort_unless($child, 403, __('Anda hanya dapat memesan untuk anak Anda sendiri.'));

        if (! $service->bookSlot($slot, auth()->id(), $child->user_id, $validated['topic'] ?? null)) {
            return back()->with('error', __('Slot ini baru saja dipesan orang tua lain. Silakan pilih slot lain.'));
        }

        return back()->with('success', __('Slot konsultasi dipesan.'));
    }

    public function cancelBooking(int $slotId, PtcSchedulerService $service)
    {
        $slot = PtcSlot::findOrFail($slotId);
        abort_unless((int) $slot->parent_id === auth()->id() && $slot->status === 'booked', 403);

        $service->cancelBooking($slot);

        return back()->with('success', __('Pesanan slot dibatalkan.'));
    }

    public function teacherSchedule()
    {
        $slots = PtcSlot::with(['session', 'parent', 'student'])
            ->where('teacher_id', auth()->id())
            ->where('status', 'booked')
            ->get();

        return view('ptc.teacher-schedule', compact('slots'));
    }
}
