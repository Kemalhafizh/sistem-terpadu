<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicEventController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function index()
    {
        $classrooms = Classroom::all();
        $activeYear = AcademicYear::current();

        return view('calendar.index', compact('classrooms', 'activeYear'));
    }

    public function apiEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'type' => 'nullable|string',
            'classroom_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $query = AcademicEvent::active()
            ->inDateRange($request->start, $request->end);

        // Filter per kelas dan per peran pengguna non-admin
        if (! $user->isAdmin()) {
            $query->forRole($user->role);
            if ($user->role === 'student' && $user->student) {
                $query->forClassroom($user->student->classroom_id);
            } elseif ($user->role === 'parent') {
                // Ambil kelas anak pertamanya
                $child = $user->children()->first();
                if ($child) {
                    $query->forClassroom($child->classroom_id);
                }
            }
        } else {
            // Admin/Guru bisa memfilter per kelas di dropdown dashboard
            if ($request->filled('classroom_id')) {
                $query->forClassroom($request->classroom_id);
            }
        }

        if ($request->filled('type')) {
            $query->where('event_type', $request->type);
        }

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->toIso8601String(),
                'end' => $event->end_date ? $event->end_date->toIso8601String() : null,
                'allDay' => $event->is_all_day,
                'color' => $event->color,
                'extendedProps' => [
                    'uuid' => $event->uuid,
                    'description' => $event->description,
                    'event_type' => $event->event_type,
                    'target_role' => $event->target_role,
                    'classroom' => $event->classroom ? $event->classroom->name : 'Semua Kelas',
                    'creator' => $event->creator ? $event->creator->name : 'Sistem',
                ],
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:exam,holiday,meeting,ceremony,tryout,osis,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_all_day' => 'boolean',
            'color' => 'required|string',
            'target_role' => 'nullable|string',
            'target_classroom_id' => 'nullable|exists:classrooms,id',
            'send_notification' => 'boolean',
        ]);

        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return response()->json(['message' => 'Tahun Akademik aktif tidak ditemukan.'], 422);
        }

        // VALIDASI TABRAKAN JADWAL UJIAN (Exam Overlap Protection)
        if ($request->event_type === 'exam' && $request->filled('target_classroom_id')) {
            $start = $request->start_date;
            $end = $request->end_date ?: $start;

            $overlap = AcademicEvent::where('event_type', 'exam')
                ->where('target_classroom_id', $request->target_classroom_id)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($sub) use ($start, $end) {
                            $sub->where('start_date', '<=', $start)
                                ->where('end_date', '>=', $end);
                        });
                })->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'Jadwal ujian bentrok! Sudah ada ujian lain di kelas dan waktu yang sama.',
                ], 422);
            }
        }

        $event = AcademicEvent::create(array_merge($request->all(), [
            'created_by' => Auth::id(),
            'academic_year_id' => $activeYear->id,
            'is_all_day' => $request->boolean('is_all_day'),
            'send_notification' => $request->boolean('send_notification'),
        ]));

        return response()->json(['message' => 'Agenda berhasil dibuat!', 'event' => $event]);
    }

    public function update(Request $request, AcademicEvent $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:exam,holiday,meeting,ceremony,tryout,osis,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_all_day' => 'boolean',
            'color' => 'required|string',
            'target_role' => 'nullable|string',
            'target_classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $event->update(array_merge($request->all(), [
            'is_all_day' => $request->boolean('is_all_day'),
        ]));

        return response()->json(['message' => 'Agenda berhasil diperbarui!']);
    }

    public function destroy(AcademicEvent $event)
    {
        $event->delete();

        return response()->json(['message' => 'Agenda berhasil dihapus!']);
    }
}
