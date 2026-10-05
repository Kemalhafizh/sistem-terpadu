<?php

namespace App\Http\Controllers\Microlearning;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\Access\StudentDataAccess;

class DigitalTranscriptController extends Controller
{
    public function index()
    {
        return view('transcripts.student-view');
    }

    public function show($id)
    {
        // One student's transcript: staff in scope, the student, or their parent.
        StudentDataAccess::authorizeStudentRecord(request()->user(), Student::find($id));

        return view('transcripts.student-view');
    }

    public function verify($hash)
    {
        return view('transcripts.public-verification');
    }

    public function badgeGallery()
    {
        return view('transcripts.student-view');
    }

    public function adminManager()
    {
        return view('transcripts.admin-manager');
    }
}
