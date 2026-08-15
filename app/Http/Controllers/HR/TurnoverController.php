<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExitInterview;

class TurnoverController extends Controller
{
    public function dashboard()
    {
        return view('turnover.dashboard');
    }

    public function exitInterviews()
    {
        $interviews = ExitInterview::all();
        return view('turnover.exit-interviews', compact('interviews'));
    }

    public function scheduleInterview(Request $request)
    {
        //
    }

    public function storeInterview(Request $request)
    {
        //
    }

    public function analytics()
    {
        //
    }
}