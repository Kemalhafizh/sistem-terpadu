<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FamilyLearningPlan;

class FamilyLearningPlanController extends Controller
{
    public function index()
    {
        return redirect()->route('family_plan.builder');
    }

    public function planBuilder()
    {
        return view('fase62.family-plan.plan-builder');
    }

    public function storePlan(Request $request)
    {
        return redirect()->route('family_plan.builder');
    }

    public function updateProgress(Request $request, $id)
    {
        return redirect()->back();
    }

    public function termReview()
    {
        return view('fase62.family-plan.term-review');
    }
}
