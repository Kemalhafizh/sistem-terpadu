<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;

class EcoChallengeController extends Controller
{
    public function index()
    {
        return view('fase59.eco-challenge.challenges');
    }

    public function leaderboard()
    {
        return view('fase59.eco-challenge.leaderboard');
    }
}
