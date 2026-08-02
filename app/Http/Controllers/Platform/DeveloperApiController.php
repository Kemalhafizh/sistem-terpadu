<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\DeveloperApiKey;
use App\Models\DevWebhookEndpoint;
use Illuminate\Http\Request;

class DeveloperApiController extends Controller
{
    public function dashboard()
    {
        $keys = DeveloperApiKey::latest()->get();
        $webhooks = DevWebhookEndpoint::latest()->get();
        return view('developer.api.dashboard', compact('keys', 'webhooks'));
    }
}
