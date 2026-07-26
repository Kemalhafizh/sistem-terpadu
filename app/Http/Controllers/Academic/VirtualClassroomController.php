<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\VirtualClassroom;
use App\Services\Academic\VirtualClassroomService;
use Illuminate\Http\Request;

class VirtualClassroomController extends Controller
{
    public function index()
    {
        $meetings = VirtualClassroom::with('host')->latest()->get();
        return view('virtual-classroom.index', compact('meetings'));
    }

    public function storeMeeting(Request $request, VirtualClassroomService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'provider' => 'required|in:zoom,google_meet,jitsi',
            'start_time' => 'required|date',
            'duration_minutes' => 'required|integer|min:15',
        ]);

        $meeting = $service->createMeeting($validated, auth()->id());

        return redirect()->route('academic.virtual.index')
            ->with('success', "Rapat Kelas Virtual [{$meeting->title}] berhasil dijadwalkan!");
    }

    public function joinLobby(int $id, VirtualClassroomService $service)
    {
        $meeting = VirtualClassroom::findOrFail($id);
        $service->recordAttendance($meeting, auth()->id());

        return view('virtual-classroom.join', compact('meeting'));
    }
}
