<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;

class EcoProjectController extends Controller
{
    public function index()
    {
        return view('fase59.green-curriculum.project-list');
    }

    public function sdgMapping()
    {
        return view('fase59.green-curriculum.sdg-mapping');
    }
}
