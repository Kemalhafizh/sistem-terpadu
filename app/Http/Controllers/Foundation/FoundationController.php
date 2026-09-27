<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Services\Foundation\FoundationGovernanceService;

class FoundationController extends Controller
{
    public function dashboard(FoundationGovernanceService $service)
    {
        return view('foundation.dashboard', [
            'financials' => $service->getConsolidatedFinancials(),
            'students' => $service->getConsolidatedStudentStats(),
        ]);
    }
}
