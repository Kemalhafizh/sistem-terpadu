<?php

namespace App\Services\Communication;

use App\Models\CalendarEvent;
use App\Models\CalendarEventAttendee;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * Create new calendar event with optional attendee list.
     */
    public function createEvent(array $data, int $organizerId): CalendarEvent
    {
        $event = CalendarEvent::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'event_type' => $data['event_type'] ?? 'academic',
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'is_all_day' => ! empty($data['is_all_day']),
            'location' => $data['location'] ?? null,
            'organizer_id' => $organizerId,
            'visibility' => $data['visibility'] ?? 'public',
            'recurrence_rule' => $data['recurrence_rule'] ?? null,
            'color_hex' => $data['color_hex'] ?? $this->getDefaultColor($data['event_type'] ?? 'academic'),
        ]);

        if (! empty($data['attendee_ids']) && is_array($data['attendee_ids'])) {
            foreach ($data['attendee_ids'] as $uId) {
                CalendarEventAttendee::create([
                    'calendar_event_id' => $event->id,
                    'user_id' => (int) $uId,
                    'rsvp_status' => 'pending',
                ]);
            }
        }

        return $event;
    }

    /**
     * Update existing calendar event.
     */
    public function updateEvent(int $eventId, array $data, int $userId): CalendarEvent
    {
        $event = CalendarEvent::findOrFail($eventId);

        if ($event->organizer_id !== $userId && ! auth()->user()->hasRole(['superadmin', 'admin'])) {
            throw new \Exception('Akses ditolak: Hanya pembuat acara atau Admin yang dapat merubah jadwal.');
        }

        $event->update([
            'title' => $data['title'] ?? $event->title,
            'description' => $data['description'] ?? $event->description,
            'event_type' => $data['event_type'] ?? $event->event_type,
            'start_datetime' => $data['start_datetime'] ?? $event->start_datetime,
            'end_datetime' => $data['end_datetime'] ?? $event->end_datetime,
            'is_all_day' => isset($data['is_all_day']) ? ! empty($data['is_all_day']) : $event->is_all_day,
            'location' => $data['location'] ?? $event->location,
            'visibility' => $data['visibility'] ?? $event->visibility,
            'color_hex' => $data['color_hex'] ?? $event->color_hex,
        ]);

        return $event;
    }

    /**
     * Delete calendar event.
     */
    public function deleteEvent(int $eventId, int $userId): bool
    {
        $event = CalendarEvent::findOrFail($eventId);

        if ($event->organizer_id !== $userId && ! auth()->user()->hasRole(['superadmin', 'admin'])) {
            throw new \Exception('Akses ditolak: Hanya pembuat acara yang dapat menghapus.');
        }

        return $event->delete();
    }

    /**
     * Get calendar events within a date range for a specific user view.
     */
    public function getEventsForRange(string $startDate, string $endDate, int $userId): Collection
    {
        return CalendarEvent::with(['organizer', 'attendees.user'])
            ->where(function ($q) use ($userId) {
                $q->where('visibility', 'public')
                    ->orWhere('organizer_id', $userId)
                    ->orWhereHas('attendees', function ($aq) use ($userId) {
                        $aq->where('user_id', $userId);
                    });
            })
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_datetime', [$startDate, $endDate])
                    ->orWhereBetween('end_datetime', [$startDate, $endDate]);
            })
            ->orderBy('start_datetime', 'asc')
            ->get();
    }

    /**
     * Respond RSVP to an event.
     */
    public function rsvp(int $eventId, int $userId, string $status): bool
    {
        if (! in_array($status, ['accepted', 'declined', 'pending'])) {
            return false;
        }

        CalendarEventAttendee::updateOrCreate(
            [
                'calendar_event_id' => $eventId,
                'user_id' => $userId,
            ],
            [
                'rsvp_status' => $status,
            ]
        );

        return true;
    }

    private function getDefaultColor(string $type): string
    {
        return match ($type) {
            'academic' => '#3B82F6', // Blue
            'exam' => '#EF4444',     // Red
            'holiday' => '#10B981',  // Green
            'ceremony' => '#F59E0B', // Amber
            'meeting' => '#8B5CF6',  // Purple
            default => '#6B7280',    // Gray
        };
    }
}
