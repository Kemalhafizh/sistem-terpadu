<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Services\Communication\CalendarService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display Unified Enterprise Calendar Console.
     */
    public function index(Request $request, CalendarService $calendarService)
    {
        $userId = auth()->id();
        $startDate = $request->query('start', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end', now()->endOfMonth()->toDateString());

        $events = $calendarService->getEventsForRange($startDate, $endDate, $userId);

        return view('communication.calendar.index', compact('events', 'startDate', 'endDate'));
    }

    /**
     * Return JSON events endpoint for Calendar Widget / FullCalendar.js.
     */
    public function eventsJson(Request $request, CalendarService $calendarService)
    {
        $userId = auth()->id();
        $startDate = $request->query('start', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end', now()->endOfMonth()->toDateString());

        $events = $calendarService->getEventsForRange($startDate, $endDate, $userId);

        $jsonEvents = $events->map(function ($e) {
            return [
                'id' => $e->id,
                'title' => $e->title,
                'start' => $e->start_datetime->toIso8601String(),
                'end' => $e->end_datetime->toIso8601String(),
                'allDay' => $e->is_all_day,
                'color' => $e->color_hex,
                'location' => $e->location,
                'organizer' => optional($e->organizer)->name,
            ];
        });

        return response()->json($jsonEvents);
    }

    /**
     * Store new calendar event.
     */
    public function store(Request $request, CalendarService $calendarService)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'event_type' => 'required|in:academic,meeting,ceremony,holiday,exam,extracurricular',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'location' => 'nullable|string|max:150',
            'visibility' => 'required|in:public,staff,private',
        ]);

        $organizerId = auth()->id();
        $event = $calendarService->createEvent($request->all(), $organizerId);

        return back()->with('success', "Jadwal Acara [{$event->title}] berhasil ditambahkan ke Kalender!");
    }

    /**
     * Update calendar event.
     */
    public function update(Request $request, int $id, CalendarService $calendarService)
    {
        $userId = auth()->id();
        try {
            $event = $calendarService->updateEvent($id, $request->all(), $userId);

            return back()->with('success', "Jadwal Acara [{$event->title}] berhasil diperbarui!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui acara: '.$e->getMessage());
        }
    }

    /**
     * Delete calendar event.
     */
    public function destroy(int $id, CalendarService $calendarService)
    {
        $userId = auth()->id();
        try {
            $calendarService->deleteEvent($id, $userId);

            return back()->with('success', 'Jadwal Acara berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus acara: '.$e->getMessage());
        }
    }

    /**
     * RSVP to an event.
     */
    public function rsvp(Request $request, int $id, CalendarService $calendarService)
    {
        $request->validate(['status' => 'required|in:accepted,declined,pending']);
        $userId = auth()->id();

        $calendarService->rsvp($id, $userId, $request->status);

        return back()->with('success', 'Respon RSVP Anda berhasil dicatat.');
    }
}
