<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JobApplicant;
use App\Models\JobVacancy;

class RecruitmentController extends Controller
{
    public function vacancies()
    {
        $vacancies = JobVacancy::all();

        return view('recruitment.vacancies', compact('vacancies'));
    }

    public function kanban()
    {
        $applicants = JobApplicant::all();

        return view('recruitment.kanban', compact('applicants'));
    }
}
