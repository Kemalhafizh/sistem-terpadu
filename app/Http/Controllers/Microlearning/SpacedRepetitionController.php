<?php

namespace App\Http\Controllers\Microlearning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SpacedRepetitionController extends Controller
{
    public function deck()
    {
        return view('spaced-repetition.deck');
    }

    public function review()
    {
        return view('spaced-repetition.review');
    }

    public function submitReview(Request $request)
    {
        return redirect()->route('spaced-repetition.deck');
    }

    public function stats()
    {
        return view('spaced-repetition.stats');
    }
}
