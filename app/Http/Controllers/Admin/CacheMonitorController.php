<?php

namespace App\Http\Controllers\Admin;

use App\Domains\School\Services\SchoolBrandingDomainService;
use App\Http\Controllers\Controller;
use App\Services\EnterpriseCacheService;
use Illuminate\Http\Request;
use Exception;

class CacheMonitorController extends Controller
{
    /**
     * Display Multi-Layer Cache Strategy Monitoring Dashboard.
     */
    public function index(EnterpriseCacheService $cacheService)
    {
        $metrics = $cacheService->getCacheMetrics();

        return view('admin.observability.cache', compact('metrics'));
    }

    /**
     * Flush specific cache tag group.
     */
    public function flushTag(Request $request, EnterpriseCacheService $cacheService)
    {
        $request->validate([
            'tag' => 'required|string|max:50',
        ]);

        try {
            $tag = $request->tag;
            $cacheService->flushTag($tag);

            return back()->with('success', "Grup Cache Tag [{$tag}] berhasil dibersihkan!");
        } catch (Exception $e) {
            return back()->with('error', "Gagal membersihkan cache tag: {$e->getMessage()}");
        }
    }

    /**
     * Flush entire application cache.
     */
    public function flushAll(EnterpriseCacheService $cacheService)
    {
        try {
            $cacheService->flushAll();
            return back()->with('success', "Seluruh Cache Aplikasi berhasil dibersihkan (Purged All)!");
        } catch (Exception $e) {
            return back()->with('error', "Gagal membersihkan cache: {$e->getMessage()}");
        }
    }

    /**
     * Pre-warm essential enterprise cache keys.
     */
    public function warmupCache(EnterpriseCacheService $cacheService, SchoolBrandingDomainService $brandingService)
    {
        try {
            // Warmup School Branding
            $brandingService->getSchoolProfile();

            // Warmup general enterprise keys
            $cacheService->remember('warmup_system_ready', 3600, fn() => true, ['system']);

            return back()->with('success', "Proses Warmup Cache Enterprise berhasil diselesaikan! Kunci kritis siap melayani request.");
        } catch (Exception $e) {
            return back()->with('error', "Gagal melakukan warmup cache: {$e->getMessage()}");
        }
    }
}
