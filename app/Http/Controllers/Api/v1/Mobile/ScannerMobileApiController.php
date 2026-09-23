<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Campus\EventTicketingService;
use App\Services\Campus\VisitorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backs the mobile multi-mode QR/barcode scanner's EVENT_TICKET and
 * VISITOR_PASS modes with the real EventTicketingService/VisitorService
 * that already power the web scanner-app and visitor kiosk — no
 * fabricated "LUNAS & VALID" results.
 */
class ScannerMobileApiController extends Controller
{
    public function __construct(
        protected EventTicketingService $eventService,
        protected VisitorService $visitorService
    ) {}

    public function scanEventTicket(Request $request): JsonResponse
    {
        $request->validate(['ticket_code' => 'required|string']);

        try {
            $ticket = $this->eventService->scanTicket((string) $request->input('ticket_code'));
            $ticket->load('event');

            return response()->json([
                'valid' => true,
                'attendeeName' => $ticket->attendee_name,
                'ticketType' => $ticket->ticket_type,
                'eventName' => $ticket->event->event_name ?? '',
                'venue' => $ticket->event->venue ?? '',
                'eventDatetime' => optional($ticket->event?->event_datetime)->toIso8601String(),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['valid' => false, 'message' => 'Tiket dengan kode tersebut tidak ditemukan.'], 422);
        } catch (\Throwable $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function scanVisitorPass(Request $request): JsonResponse
    {
        $request->validate(['badge_number' => 'required|string']);

        try {
            $visitor = $this->visitorService->scanBadge((string) $request->input('badge_number'));

            return response()->json([
                'valid' => true,
                'visitorName' => $visitor->visitor_name,
                'purpose' => $visitor->purpose,
                'status' => $visitor->status,
                'checkInTime' => optional($visitor->check_in_time)->toIso8601String(),
                'checkOutTime' => optional($visitor->check_out_time)->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
