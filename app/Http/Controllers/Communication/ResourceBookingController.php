<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RoleMiddleware;
use App\Models\BookableResource;
use App\Models\ResourceBooking;
use App\Services\Communication\ResourceBookingService;
use Illuminate\Http\Request;

class ResourceBookingController extends Controller
{
    /**
     * Display Resource Booking Console.
     */
    public function index()
    {
        $resources = BookableResource::with(['bookings' => function ($q) {
            $q->orderBy('start_datetime', 'desc')->take(10);
        }])->get();

        $recentBookings = ResourceBooking::with(['resource', 'user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('communication.resource_bookings.index', compact('resources', 'recentBookings'));
    }

    /**
     * Store new resource booking reservation.
     */
    public function createBooking(Request $request, ResourceBookingService $bookingService)
    {
        $request->validate([
            'bookable_resource_id' => 'required|exists:bookable_resources,id',
            'purpose' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        $bookedBy = auth()->id();

        try {
            $booking = $bookingService->createBooking($request->all(), $bookedBy);

            return back()->with('success', "Reservasi aset [{$booking->purpose}] berhasil dikirim dan menunggu persetujuan Admin!");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve booking reservation (Admin/Superadmin/Waka Sarpras/Staf TU).
     */
    public function approveBooking(int $id, ResourceBookingService $bookingService)
    {
        $user = auth()->user();
        if (! $user || ! RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras', 'staf_tu'])) {
            abort(403, 'Anda tidak memiliki wewenang untuk menyetujui reservasi aset.');
        }

        $approvedBy = $user->id;

        try {
            $booking = $bookingService->approveBooking($id, $approvedBy);

            return back()->with('success', "Reservasi [{$booking->purpose}] berhasil DISETUJUI.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject booking reservation (Admin/Superadmin/Waka Sarpras/Staf TU).
     */
    public function rejectBooking(int $id, ResourceBookingService $bookingService)
    {
        $user = auth()->user();
        if (! $user || ! RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras', 'staf_tu'])) {
            abort(403, 'Anda tidak memiliki wewenang untuk menolak reservasi aset.');
        }

        $approvedBy = $user->id;

        try {
            $booking = $bookingService->rejectBooking($id, $approvedBy);

            return back()->with('success', "Reservasi [{$booking->purpose}] DITOLAK.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check resource availability API.
     */
    public function checkAvailability(Request $request, ResourceBookingService $bookingService)
    {
        $validated = $request->validate([
            'resource_id' => ['required', 'integer'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $isAvailable = $bookingService->checkAvailability((int) $validated['resource_id'], $validated['start'], $validated['end']);

        return response()->json([
            'is_available' => $isAvailable,
            'message' => $isAvailable ? 'Jadwal TERSEDIA.' : 'BENTROK: Aset sudah dibooking pada jam tersebut.',
        ]);
    }
}
