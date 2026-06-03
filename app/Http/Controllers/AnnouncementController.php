<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Announcement::class);
        $announcements = Announcement::with('creator', 'targetClassroom')->latest()->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);
        $classrooms = Classroom::all();

        return view('announcements.create', compact('classrooms'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target_role' => 'nullable|string',
            'target_classroom_id' => 'nullable|exists:classrooms,id',
            'priority' => 'required|in:low,normal,urgent',
            'expires_at' => 'nullable|date',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'target_role' => $validated['target_role'] ?? null,
            'target_classroom_id' => $validated['target_classroom_id'] ?? null,
            'priority' => $validated['priority'],
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil disebarkan.');
    }

    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $classrooms = Classroom::all();

        return view('announcements.edit', compact('announcement', 'classrooms'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target_role' => 'nullable|string',
            'target_classroom_id' => 'nullable|exists:classrooms,id',
            'priority' => 'required|in:low,normal,urgent',
            'expires_at' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $validated['image_path'] ?? $announcement->image_path,
            'target_role' => $validated['target_role'] ?? $announcement->target_role,
            'target_classroom_id' => $validated['target_classroom_id'] ?? $announcement->target_classroom_id,
            'priority' => $validated['priority'],
            'expires_at' => $validated['expires_at'] ?? $announcement->expires_at,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
