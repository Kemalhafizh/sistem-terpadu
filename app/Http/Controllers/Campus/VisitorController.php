<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use App\Services\Campus\VisitorService;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    protected $visitorService;

    public function __construct(VisitorService $visitorService)
    {
        $this->visitorService = $visitorService;
    }

    public function index()
    {
        $activeVisitors = $this->visitorService->getActiveVisitors();

        return view('visitors.dashboard', compact('activeVisitors'));
    }

    public function kioskView()
    {
        return view('visitors.kiosk');
    }

    public function preRegisterForm()
    {
        return view('visitors.pre-register');
    }

    public function storePreRegister(Request $request)
    {
        $data = $request->validate([
            'visitor_name' => 'required|string',
            'identity_number' => 'nullable|string',
            'organization_name' => 'nullable|string',
            'purpose' => 'required|in:meeting,maintenance,delivery,parent_visit,other',
        ]);

        $this->visitorService->preRegister($data);

        return redirect()->route('visitors.dashboard')->with('success', 'Pre-registration successful');
    }

    public function checkIn(Request $request, VisitorLog $visitor)
    {
        $this->visitorService->processCheckIn($visitor);

        return back()->with('success', 'Visitor checked in successfully');
    }

    public function checkOut(Request $request, VisitorLog $visitor)
    {
        $this->visitorService->processCheckOut($visitor);

        return back()->with('success', 'Visitor checked out successfully');
    }

    public function history()
    {
        $visitors = VisitorLog::latest()->get();

        return view('visitors.dashboard', ['activeVisitors' => $visitors]); // Reusing dashboard view for simplicity or ideally separate view
    }
}
