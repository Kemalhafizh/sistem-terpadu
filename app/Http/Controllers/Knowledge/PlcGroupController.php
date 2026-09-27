<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlcGroupController extends Controller
{
    public function index()
    {
        return view('knowledge.plc.index');
    }

    public function show($id)
    {
        return view('knowledge.plc.show');
    }

    public function create()
    {
        return view('knowledge.plc.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('knowledge.plc.index');
    }
}
