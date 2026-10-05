<?php

namespace App\Http\Controllers\Crisis;

use App\Http\Controllers\Controller;
use App\Models\EmergencyBroadcast;
use App\Services\Crisis\EmergencyBroadcastService;
use Illuminate\Http\Request;

class EmergencyBroadcastController extends Controller
{
    protected $service;

    public function __construct(EmergencyBroadcastService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $broadcasts = EmergencyBroadcast::latest()->get();

        return view('crisis.broadcast.index', compact('broadcasts'));
    }

    public function create()
    {
        return view('crisis.broadcast.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'priority' => 'required|string',
            'target_groups' => 'required|array',
            'channels' => 'required|array',
        ]);
        $validated['sent_by'] = $request->user()->id;

        $this->service->sendBroadcast($validated);

        return redirect()->route('crisis.broadcast.index')->with('success', 'Broadcast sent.');
    }

    public function show($id)
    {
        $broadcast = EmergencyBroadcast::findOrFail($id);
        $stats = $this->service->getDeliveryStats($broadcast);

        return view('crisis.broadcast.show', compact('broadcast', 'stats'));
    }
}
