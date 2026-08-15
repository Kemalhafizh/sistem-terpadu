<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EcoChallengeController extends Controller
{
    public function index()
    {
        return view('fase59.eco-challenge.challenges');
    }

    public function submitEvidence(Request $request)
    {
        // Implementation
    }

    public function verifyParticipation($id)
    {
        // Implementation
    }

    public function leaderboard()
    {
        return view('fase59.eco-challenge.leaderboard');
    }
}
