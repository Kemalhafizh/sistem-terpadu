<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;

class EndowmentController extends Controller
{
    public function dashboard()
    {
        return view('fase61.endowment.dashboard');
    }

    public function pledgeManager()
    {
        return view('fase61.endowment.pledge-manager');
    }

    public function recognition()
    {
        return view('fase61.endowment.recognition');
    }
}
