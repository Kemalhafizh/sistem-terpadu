<?php

namespace App\Http\Controllers\Islamic;

use App\Http\Controllers\Controller;

class SpiritualPortfolioController extends Controller
{
    public function index()
    {
        return view('fase57.spiritual.dashboard');
    }

    public function showPortfolio($id)
    {
        return view('fase57.spiritual.student-portfolio');
    }

    public function studentPortfolio()
    {
        return view('fase57.spiritual.student-portfolio');
    }

    public function dashboard()
    {
        return view('fase57.spiritual.dashboard');
    }
}
