<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\CollegeCounselingService;

class CollegeCounselingController extends Controller
{
    protected $service;

    public function __construct(CollegeCounselingService $service)
    {
        $this->service = $service;
    }

    public function database()
    {
        return view('college-counseling.match-engine');
    }

    public function matchEngine()
    {
        return view('college-counseling.match-engine');
    }

    public function myApplications()
    {
        return view('college-counseling.tracker');
    }

    public function counselorTracker()
    {
        return view('college-counseling.tracker');
    }
}
