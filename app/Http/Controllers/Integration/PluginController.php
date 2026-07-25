<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\Integration\PluginManagerService;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    /**
     * Display Installed Extension Plugins Manager.
     */
    public function index()
    {
        $plugins = Plugin::with('hooks')->orderBy('id', 'desc')->get();

        return view('integration.plugins.index', compact('plugins'));
    }

    /**
     * Display Plugin Marketplace.
     */
    public function marketplace()
    {
        return view('integration.plugins.marketplace');
    }

    /**
     * Install new extension plugin.
     */
    public function install(Request $request, PluginManagerService $pluginService)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:plugins,slug',
            'version' => 'required|string',
            'author' => 'required|string',
        ]);

        $plugin = $pluginService->install($request->all());

        return redirect()->route('plugins.index')
            ->with('success', "Plugin Ekstensi [{$plugin->name}] berhasil dipasang!");
    }

    /**
     * Toggle enable/disable status.
     */
    public function toggle(int $id, Request $request, PluginManagerService $pluginService)
    {
        $enable = $request->boolean('enable', true);
        $plugin = $pluginService->toggle($id, $enable);

        $statusText = $enable ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';

        return back()->with('success', "Plugin [{$plugin->name}] berhasil {$statusText}!");
    }
}
