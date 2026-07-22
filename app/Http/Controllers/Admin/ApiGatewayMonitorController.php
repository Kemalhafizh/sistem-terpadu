<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CircuitBreakerService;
use Illuminate\Http\Request;
use Exception;

class ApiGatewayMonitorController extends Controller
{
    /**
     * Display API Gateway & Circuit Breaker Monitoring Dashboard.
     */
    public function index(CircuitBreakerService $cb)
    {
        $circuits = $cb->getAllServicesStatus();

        return view('admin.observability.gateway', compact('circuits'));
    }

    /**
     * Reset Circuit Breaker to CLOSED state.
     */
    public function resetCircuit(Request $request, CircuitBreakerService $cb)
    {
        $request->validate([
            'service' => 'required|string',
        ]);

        try {
            $service = $request->service;
            $cb->resetCircuit($service);

            return back()->with('success', "Circuit Breaker untuk layanan [{$service}] berhasil di-reset ke status NORMAL (CLOSED)!");
        } catch (Exception $e) {
            return back()->with('error', "Gagal me-reset circuit breaker: {$e->getMessage()}");
        }
    }

    /**
     * Simulate 5 consecutive failures to trip Circuit Breaker to OPEN state for testing.
     */
    public function simulateFailure(Request $request, CircuitBreakerService $cb)
    {
        $request->validate([
            'service' => 'required|string',
        ]);

        try {
            $service = $request->service;
            for ($i = 0; $i < 5; $i++) {
                $cb->recordFailure($service);
            }

            return back()->with('success', "Simulasi 5x eror berhasil! Circuit Breaker [{$service}] kini beralih ke status DARURAT (OPEN / Fallback Mode).");
        } catch (Exception $e) {
            return back()->with('error', "Gagal mensimulasikan eror: {$e->getMessage()}");
        }
    }
}
