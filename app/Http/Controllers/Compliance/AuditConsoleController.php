<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\ForensicEvent;

class AuditConsoleController extends Controller
{
    public function console()
    {
        $events = ForensicEvent::with('user')->latest()->take(50)->get();

        return view('compliance.audit.console', compact('events'));
    }
}
