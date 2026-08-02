<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\VolunteerOpportunity;
use App\Services\Community\VolunteerService;
use Illuminate\Http\Request;

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
