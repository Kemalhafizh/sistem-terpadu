<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\TicketedEvent;
use App\Services\Campus\EventTicketingService;
use Illuminate\Http\Request;

class EventTicketingController extends Controller
{
    protected $eventService;

    public function __construct(EventTicketingService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index()
    {
        $events = TicketedEvent::latest()->get();

        return view('events.manager', compact('events'));
    }

    public function create()
    {
        return view('events.manager');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'event_name' => 'required|string',
            'description' => 'required|string',
            'event_datetime' => 'required|date',
            'venue' => 'required|string',
            'max_capacity' => 'required|integer',
        ]);

        $data['organizer_id'] = $request->user()->id;

        $this->eventService->createEvent($data);

        return back()->with('success', 'Event created successfully');
    }

    public function salesDashboard()
    {
        $events = TicketedEvent::withCount('tickets')->get();

        return view('events.sales-dashboard', compact('events'));
    }

    public function scannerApp()
    {
        return view('events.scanner-app');
    }

    public function scanTicket(Request $request)
    {
        $request->validate(['ticket_code' => 'required|string']);

        try {
            $this->eventService->scanTicket($request->ticket_code);

            return back()->with('success', 'Ticket scanned successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
