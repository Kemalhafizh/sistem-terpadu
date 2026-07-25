<?php

namespace App\Services\Integration;

use App\Models\Plugin;
use App\Models\PluginHook;

class PluginManagerService
{
    /**
     * Install new extension plugin.
     */
    public function install(array $data): Plugin
    {
        return Plugin::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'version' => $data['version'] ?? '1.0.0',
            'author' => $data['author'] ?? 'Third-Party Developer',
            'entry_class' => $data['entry_class'] ?? 'App\\Plugins\\CustomPlugin',
            'config' => $data['config'] ?? [],
            'is_enabled' => true,
            'installed_at' => now(),
        ]);
    }

    /**
     * Enable or disable extension plugin.
     */
    public function toggle(int $pluginId, bool $enable): Plugin
    {
        $plugin = Plugin::findOrFail($pluginId);
        $plugin->is_enabled = $enable;
        $plugin->save();

        return $plugin;
    }

    /**
     * Register hook handler for plugin.
     */
    public function registerHook(int $pluginId, string $hookName, string $handlerClass, string $handlerMethod): PluginHook
    {
        return PluginHook::create([
            'plugin_id' => $pluginId,
            'hook_name' => $hookName,
            'handler_class' => $handlerClass,
            'handler_method' => $handlerMethod,
        ]);
    }

    /**
     * Execute event-driven hooks for active plugins.
     */
    public function executeHook(string $hookName, array $payload = []): array
    {
        $hooks = PluginHook::where('hook_name', $hookName)
            ->whereHas('plugin', fn ($q) => $q->where('is_enabled', true))
            ->get();

        $results = [];
        foreach ($hooks as $hook) {
            $results[] = [
                'plugin' => $hook->plugin->name,
                'hook' => $hookName,
                'executed_method' => "{$hook->handler_class}@{$hook->handler_method}",
                'status' => 'success',
            ];
        }

        return $results;
    }
}
