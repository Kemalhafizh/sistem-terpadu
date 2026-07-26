<?php

namespace App\Http\Controllers\Deployment;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use App\Services\Deployment\EnvironmentConfigService;
use Illuminate\Http\Request;

class EnvironmentConfigController extends Controller
{
    public function dashboard(EnvironmentConfigService $service)
    {
        $health = $service->validateEnvironment();
        $configs = SystemConfiguration::latest()->get();

        return view('environment.dashboard', compact('health', 'configs'));
    }

    public function configureForm()
    {
        return view('environment.configure');
    }

    public function storeConfig(Request $request, EnvironmentConfigService $service)
    {
        $validated = $request->validate([
            'config_key' => 'required|string',
            'config_value' => 'required|string',
            'environment' => 'required|in:development,staging,production',
            'config_group' => 'required|string',
        ]);

        $service->setConfig(
            $validated['config_key'],
            $validated['config_value'],
            $validated['environment'],
            $validated['config_group'],
            true,
            auth()->user()->name ?? 'Admin'
        );

        return redirect()->route('deploy.environment.dashboard')
            ->with('success', "Variabel lingkungan [{$validated['config_key']}] terenkripsi berhasil disimpan!");
    }
}
