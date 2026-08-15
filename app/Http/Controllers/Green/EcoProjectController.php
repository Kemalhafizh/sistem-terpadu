<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EcoProjectController extends Controller
{
    public function index()
    {
        return view('fase59.green-curriculum.project-list');
    }

    public function create()
    {
        // Implementation
    }

    public function store(Request $request)
    {
        // Implementation
    }

    public function show($id)
    {
        // Implementation
    }

    public function update(Request $request, $id)
    {
        // Implementation
    }

    public function destroy($id)
    {
        // Implementation
    }

    public function sdgMapping()
    {
        return view('fase59.green-curriculum.sdg-mapping');
    }
}
