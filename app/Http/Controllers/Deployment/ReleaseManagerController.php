<?php

namespace App\Http\Controllers\Deployment;

use App\Http\Controllers\Controller;
use App\Models\ReleaseVersion;
use App\Services\Deployment\ReleaseManagerService;
use Illuminate\Http\Request;

class ReleaseManagerController extends Controller
{
    public function index()
    {
        $releases = ReleaseVersion::latest('released_at')->get();
        return view('releases.index', compact('releases'));
    }

    public function storeRelease(Request $request, ReleaseManagerService $service)
    {
        $validated = $request->validate([
            'version' => 'required|string|unique:release_versions,version',
            'release_type' => 'required|in:major,minor,patch',
            'changelog_summary' => 'required|string',
        ]);

        $rel = $service->createRelease(
            $validated['version'],
            $validated['changelog_summary'],
            $validated['release_type'],
            auth()->user()->name ?? 'DevOps'
        );

        return redirect()->route('deploy.releases.index')
            ->with('success', "Versi Rilis SemVer [v{$rel->version}] berhasil di-tag dan dipublikasikan!");
    }

    public function changelog(int $id)
    {
        $release = ReleaseVersion::findOrFail($id);
        return view('releases.changelog', compact('release'));
    }

    public function rollbackTest(int $id)
    {
        $release = ReleaseVersion::findOrFail($id);
        return view('releases.rollback-test', compact('release'));
    }
}
