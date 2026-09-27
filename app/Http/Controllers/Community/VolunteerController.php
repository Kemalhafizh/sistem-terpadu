<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\VolunteerOpportunity;

class VolunteerController extends Controller
{
    public function opportunities()
    {
        $opportunities = VolunteerOpportunity::with('organizer')->latest()->get();

        return view('volunteers.opportunities', compact('opportunities'));
    }

    public function dashboard()
    {
        return view('volunteers.dashboard');
    }
}
