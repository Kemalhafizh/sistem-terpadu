<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobVacancy;
use App\Models\JobApplicant;

class RecruitmentController extends Controller
{
    public function vacancies()
    {
        $vacancies = JobVacancy::all();
        return view('recruitment.vacancies', compact('vacancies'));
    }

    public function createVacancy()
    {
        return view('recruitment.vacancy-form');
    }

    public function storeVacancy(Request $request)
    {
        // Validation and storage logic
        return redirect()->route('recruitment.vacancies');
    }

    public function applicants()
    {
        //
    }

    public function kanban()
    {
        $applicants = JobApplicant::all();
        return view('recruitment.kanban', compact('applicants'));
    }

    public function updateStage(Request $request, $id)
    {
        //
    }
}