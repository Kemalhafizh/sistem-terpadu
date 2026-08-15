<?php

namespace App\Services\Campus;

use App\Models\TicketedEvent;
use App\Models\EventTicket;
use Illuminate\Support\Str;

class EventTicketingService
{
    public function createEvent(array $data)
    {
        return TicketedEvent::create($data);
    }

    public function issueTicket($eventId, array $attendeeData)
    {
        $event = TicketedEvent::findOrFail($eventId);
        
        if ($event->tickets()->count() >= $event->max_capacity) {
            throw new \Exception('Event is at full capacity.');
        }

        $attendeeData['ticketed_event_id'] = $event->id;
        $attendeeData['ticket_code'] = 'TKT-' . strtoupper(Str::random(10));
        
        return EventTicket::create($attendeeData);
    }

    public function scanTicket($ticketCode)
    {
        $ticket = EventTicket::where('ticket_code', $ticketCode)->firstOrFail();
        
        if ($ticket->is_scanned) {
            throw new \Exception('Ticket has already been scanned.');
        }
        
        $ticket->update(['is_scanned' => true]);
        return $ticket;
    }
}
