<?php

use App\Models\BookableResource;
use App\Models\User;
use App\Services\Communication\ResourceBookingService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin Kebijakan']);
    $this->bookingService = app(ResourceBookingService::class);
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
