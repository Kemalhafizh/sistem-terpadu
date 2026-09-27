<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SharedResourceController extends Controller
{
    public function index()
    {
        return view('knowledge.resources.index');
    }

    public function create()
    {
        return view('knowledge.resources.upload');
    }

    public function store(Request $request)
    {
        return redirect()->route('knowledge.resources.index');
    }

    public function show($id)
    {
        return view('knowledge.resources.show');
    }

    public function rate(Request $request, $id)
    {
        return back();
    }
}
