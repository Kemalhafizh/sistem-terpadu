<?php

namespace App\Http\Controllers\UX;

use App\Http\Controllers\Controller;
use App\Models\AccessibilityAuditLog;
use App\Services\UX\AccessibilityService;
use Illuminate\Http\Request;

class AccessibilityController extends Controller
{
    public function dashboard(AccessibilityService $service)
    {
        $compliance = $service->getComplianceScore();
        $logs = AccessibilityAuditLog::latest()->get();

        return view('accessibility.dashboard', compact('compliance', 'logs'));
    }

    public function auditReport(AccessibilityService $service)
    {
        $compliance = $service->getComplianceScore();
        return view('accessibility.audit-report', compact('compliance'));
    }
}
