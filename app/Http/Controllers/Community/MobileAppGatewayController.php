<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Services\Community\MobileApiService;
use Illuminate\Http\Request;

class MobileAppGatewayController extends Controller
{
    public function registerToken(Request $request, MobileApiService $service)
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
            'platform' => 'required|in:android,ios,web',
        ]);

        $device = $service->registerDevice(auth()->id(), $validated['device_token'], $validated['platform']);

        return response()->json(['success' => true, 'message' => 'Device registered successfully', 'device' => $device]);
    }
}
