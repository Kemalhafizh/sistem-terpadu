<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlcDiscussionController extends Controller
{
    public function show($groupId, $discussionId)
    {
        return view('knowledge.plc.discussion');
    }

    public function store(Request $request, $groupId)
    {
        return back();
    }

    public function reply(Request $request, $discussionId)
    {
        return back();
    }
}
