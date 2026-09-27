<?php

use App\Models\BookableResource;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Services\Communication\CalendarService;
use App\Services\Communication\ResourceBookingService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin Kebijakan']);
    $this->calendarService = app(CalendarService::class);
    $this->bookingService = app(ResourceBookingService::class);
});

test('calendar service creates, updates, and fetches range events', function () {
    $start = now()->addDay()->setHour(10)->setMinute(0);
    $end = now()->addDay()->setHour(12)->setMinute(0);

    $event = $this->calendarService->createEvent([
        'title' => 'Rapat Uji Coba Calendar',
        'event_type' => 'meeting',
        'start_datetime' => $start->toDateTimeString(),
        'end_datetime' => $end->toDateTimeString(),
        'location' => 'Ruang Meeting 1',
        'visibility' => 'public',
    ], $this->user->id);

    expect($event->id)->not()->toBeNull();

    $events = $this->calendarService->getEventsForRange(
        now()->toDateString(),
        now()->addDays(2)->toDateString(),
        $this->user->id
    );

    expect($events->pluck('id'))->toContain($event->id);
});

test('resource booking service detects schedule conflicts', function () {
    $resource = BookableResource::create([
        'name' => 'Aula Utama Test',
        'type' => 'room',
        'capacity' => 200,
        'is_available' => true,
    ]);

    $start = now()->addDays(3)->setHour(8)->setMinute(0)->toDateTimeString();
    $end = now()->addDays(3)->setHour(11)->setMinute(0)->toDateTimeString();

    $booking1 = $this->bookingService->createBooking([
        'bookable_resource_id' => $resource->id,
        'purpose' => 'Seminar 1',
        'start_datetime' => $start,
        'end_datetime' => $end,
    ], $this->user->id);

    expect($booking1->status)->toBe('pending');

    // Attempt overlapping booking during same window
    expect(function () use ($resource, $start, $end) {
        $this->bookingService->createBooking([
            'bookable_resource_id' => $resource->id,
            'purpose' => 'Seminar Bentrok',
            'start_datetime' => $start,
            'end_datetime' => $end,
        ], $this->user->id);
    })->toThrow(Exception::class);
});

test('admin can view calendar console and JSON API endpoint', function () {
    $response = $this->actingAs($this->user)->get(route('calendar.index'));
    $response->assertStatus(200);

    $jsonResponse = $this->actingAs($this->user)->get(route('calendar.events_json'));
    $jsonResponse->assertStatus(200);
});

test('user can create calendar event and rsvp via controller', function () {
    $start = now()->addDays(5)->setHour(9)->toDateTimeString();
    $end = now()->addDays(5)->setHour(11)->toDateTimeString();

    $storeResponse = $this->actingAs($this->user)->post(route('calendar.store'), [
        'title' => 'Ujian Susulan PAS',
        'event_type' => 'exam',
        'start_datetime' => $start,
        'end_datetime' => $end,
        'visibility' => 'public',
    ]);

    $storeResponse->assertRedirect();
    $event = CalendarEvent::where('title', 'Ujian Susulan PAS')->first();
    expect($event)->not()->toBeNull();

    $rsvpResponse = $this->actingAs($this->user)->post(route('calendar.rsvp', $event->id), [
        'status' => 'accepted',
    ]);

    $rsvpResponse->assertRedirect();
});

test('admin can approve and reject resource booking reservations', function () {
    $resource = BookableResource::create([
        'name' => 'Lab Komputer Test',
        'type' => 'facility',
        'is_available' => true,
    ]);

    $start = now()->addDays(6)->setHour(13)->toDateTimeString();
    $end = now()->addDays(6)->setHour(15)->toDateTimeString();

    $booking = $this->bookingService->createBooking([
        'bookable_resource_id' => $resource->id,
        'purpose' => 'Pelatihan Coding CBT',
        'start_datetime' => $start,
        'end_datetime' => $end,
    ], $this->user->id);

    $approveResponse = $this->actingAs($this->user)->post(route('resource_bookings.approve', $booking->id));
    $approveResponse->assertRedirect();

    $booking->refresh();
    expect($booking->status)->toBe('approved');
});
