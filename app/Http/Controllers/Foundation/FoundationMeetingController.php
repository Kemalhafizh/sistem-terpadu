<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\FoundationBoardMeeting;
use Illuminate\Http\Request;

class FoundationMeetingController extends Controller
{
    public function index()
    {
        return view('foundation.meetings.index', ['meetings' => FoundationBoardMeeting::all()]);
    }

    public function create()
    {
        return view('foundation.meetings.create');
    }

    public function store(Request $request)
    {
        FoundationBoardMeeting::create($request->all());

        return redirect()->route('foundation.meetings.index');
    }

    public function show($id)
    {
        return view('foundation.meetings.show', ['meeting' => FoundationBoardMeeting::findOrFail($id)]);
    }
}
