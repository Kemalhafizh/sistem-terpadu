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
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function stats()
    {
        return view('spaced-repetition.stats');
    }
}
