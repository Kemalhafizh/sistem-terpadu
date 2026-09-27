<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\CircuitBreakerService;
use Illuminate\Http\Request;

class ExternalIntegrationApiController extends Controller
{
    public function sendWhatsapp(Request $request, CircuitBreakerService $cb)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $cb->call(
            'fonnte_whatsapp',
            function () use ($request) {
                // Primary action: simulate successful gateway call
                return [
                    'status' => 'SENT',
                    'gateway' => 'Fonnte WA Primary',
                    'phone' => $request->phone,
                    'message' => $request->message,
                ];
            },
            function ($reason) {
                // Fallback action: return fallback mode
                return [
                    'status' => 'FALLBACK_QUEUED',
                    'gateway' => 'Local SMS / Internal Notification Queue',
                    'reason' => $reason,
                ];
            }
        );

        return response()->json([
            'version' => 'v1',
            'gateway_result' => $result,
        ]);
    }
}
