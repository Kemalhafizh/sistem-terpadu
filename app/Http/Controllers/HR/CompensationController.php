<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BenefitClaim;
use App\Models\CompensationStructure;
use Illuminate\Http\Request;

class CompensationController extends Controller
{
    public function dashboard()
    {
        return view('compensation.dashboard');
    }

    public function structures()
    {
        $structures = CompensationStructure::all();

        return view('compensation.employee-structures', compact('structures'));
    }

    public function storeStructure(Request $request)
    {
        //
    }

    public function claims()
    {
        $claims = BenefitClaim::all();

        return view('compensation.claims-approval', compact('claims'));
    }

    public function createClaim()
    {
        return view('compensation.claim-form');
    }

    public function storeClaim(Request $request)
    {
        //
    }

    public function approveClaim(Request $request, $id)
    {
        //
    }
}
