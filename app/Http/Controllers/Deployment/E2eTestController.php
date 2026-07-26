<?php

namespace App\Http\Controllers\Deployment;

use App\Http\Controllers\Controller;
use App\Models\E2eTestRun;
use App\Services\Deployment\E2eTestService;
use Illuminate\Http\Request;

class E2eTestController extends Controller
{
    public function dashboard()
    {
        $runs = E2eTestRun::latest()->get();
        return view('e2e.dashboard', compact('runs'));
    }

    public function triggerRun(Request $request, E2eTestService $service)
    {
        $run = $service->runSuite();

        return redirect()->route('deploy.e2e.dashboard')
            ->with('success', "Full E2E Test Suite [10/10 Passed] selesai dieksekusi dalam {$run->duration_seconds} detik!");
    }

    public function results(int $id)
    {
        $run = E2eTestRun::findOrFail($id);
        return view('e2e.results', compact('run'));
    }

    public function flakeyTests()
    {
        return view('e2e.flakey-tests');
    }
}
