<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;

class AiRecommendationController extends Controller
{
    public function recommendations()
    {
        $recommendations = AiRecommendation::with('user')->latest()->get();

        return view('developer.ai.recommendations', compact('recommendations'));
    }
}
