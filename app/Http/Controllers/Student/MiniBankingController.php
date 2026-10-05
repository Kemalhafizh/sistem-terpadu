<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\MiniBankingService;

class MiniBankingController extends Controller
{
    protected $service;

    public function __construct(MiniBankingService $service)
    {
        $this->service = $service;
    }

    public function walletView()
    {
        return view('mini-bank.student-wallet');
    }

    public function depositForm()
    {
        return view('mini-bank.student-wallet');
    }

    public function transferForm()
    {
        return view('mini-bank.student-wallet');
    }

    public function tellerDashboard()
    {
        return view('mini-bank.teller');
    }

    public function literacyDashboard()
    {
        return view('mini-bank.literacy-dashboard');
    }
}
