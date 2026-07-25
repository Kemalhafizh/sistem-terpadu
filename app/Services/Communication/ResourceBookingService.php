<?php

namespace App\Services\Communication;

use App\Models\BookableResource;
use App\Models\ResourceBooking;
use Illuminate\Support\Collection;

class ResourceBookingService
{
    /**
     * Check if a resource is available during start and end datetimes without overlap conflicts.
     */
    public function checkAvailability(int $resourceId, string $startDatetime, string $endDatetime, ?int $excludeBookingId = null): bool
    {
        $resource = BookableResource::find($resourceId);
        if (! $resource || ! $resource->is_available) {
            return false;
        }

        $query = ResourceBooking::where('bookable_resource_id', $resourceId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startDatetime, $endDatetime) {
                // Overlap condition: start < new_end AND end > new_start
                $q->where('start_datetime', '<', $endDatetime)
                    ->where('end_datetime', '>', $startDatetime);
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return ! $query->exists();
    }

    /**
     * Create a resource reservation request.
     */
    public function createBooking(array $data, int $bookedBy): ResourceBooking
    {
        $isAvailable = $this->checkAvailability(
            (int) $data['bookable_resource_id'],
            $data['start_datetime'],
            $data['end_datetime']
        );

        if (! $isAvailable) {
            throw new \Exception('BENTROK JADWAL: Aset / Ruangan ini sudah dibooking oleh pengajuan lain pada jam tersebut.');
        }

        return ResourceBooking::create([
            'bookable_resource_id' => $data['bookable_resource_id'],
            'booked_by' => $bookedBy,
            'purpose' => $data['purpose'],
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'status' => 'pending',
        ]);
    }

    /**
     * Approve resource booking.
     */
    public function approveBooking(int $bookingId, int $approvedBy): ResourceBooking
    {
        $booking = ResourceBooking::findOrFail($bookingId);

        // Verify conflict again before approval
        $isAvailable = $this->checkAvailability(
            $booking->bookable_resource_id,
            $booking->start_datetime->toDateTimeString(),
            $booking->end_datetime->toDateTimeString(),
            $booking->id
        );

        if (! $isAvailable) {
            $booking->status = 'rejected';
            $booking->save();
            throw new \Exception('Gagal menyetujui: Terjadi bentrok jadwal dengan reservasi lain yang telah disetujui.');
        }

        $booking->status = 'approved';
        $booking->approved_by = $approvedBy;
        $booking->save();

        return $booking;
    }

    /**
     * Reject resource booking.
     */
    public function rejectBooking(int $bookingId, int $approvedBy): ResourceBooking
    {
        $booking = ResourceBooking::findOrFail($bookingId);
        $booking->status = 'rejected';
        $booking->approved_by = $approvedBy;
        $booking->save();

        return $booking;
    }

    /**
     * Get booking schedule list for a resource.
     */
    public function getBookingSchedule(int $resourceId): Collection
    {
        return ResourceBooking::with(['user', 'approver', 'resource'])
            ->where('bookable_resource_id', $resourceId)
            ->orderBy('start_datetime', 'desc')
            ->get();
    }
}
