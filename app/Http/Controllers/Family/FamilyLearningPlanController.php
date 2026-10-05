<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function updateProgress(Request $request, $id)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function termReview()
    {
        return view('fase62.family-plan.term-review');
    }
}
