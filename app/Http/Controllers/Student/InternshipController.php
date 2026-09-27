<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\InternshipService;
use Illuminate\Http\Request;

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

    public function apply(Request $request)
    {
        return back()->with('success', 'Berhasil mendaftar magang');
    }

    public function myApplications()
    {
        return view('internships.portal');
    }

    public function logJournal(Request $request)
    {
        return back()->with('success', 'Jurnal berhasil disimpan');
    }

    public function supervisorMonitoring()
    {
        return view('internships.monitoring');
    }
}
