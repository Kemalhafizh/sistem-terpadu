<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;

class MentorshipController extends Controller
{
    public function directory()
    {
        $mentors = MentorProfile::with('alumni')->where('is_available', true)->latest()->get();

        return view('mentorship.directory', compact('mentors'));
    }

    public function workspace()
    {
        return view('mentorship.workspace');
    }
}
