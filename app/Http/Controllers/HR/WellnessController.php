<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WellnessController extends Controller
{
    public function index()
    {
        return view('wellness.catalog');
    }

    public function programs()
    {
        //
    }

    public function createProgram()
    {
        //
    }

    public function storeProgram(Request $request)
    {
        //
    }

    public function myWellness()
    {
        return view('wellness.employee-dashboard');
    }

    public function logParticipation(Request $request)
    {
        //
    }

    public function hrAnalytics()
    {
        return view('wellness.hr-analytics');
    }
}
