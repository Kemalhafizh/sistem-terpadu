<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\InternshipService;

class InternshipController extends Controller
{
    protected $service;

    public function __construct(InternshipService $service)
    {
        $this->service = $service;
    }

    public function portal()
    {
        return view('internships.portal');
    }

    public function myApplications()
    {
        return view('internships.portal');
    }

    public function supervisorMonitoring()
    {
        return view('internships.monitoring');
    }
}
