<?php

namespace App\Http\Controllers;

use App\Models\PointCategory;
use Illuminate\Http\Request;

class PointCategoryController extends Controller
{
    public function index()
    {
        $categories = PointCategory::latest()->get();

        return view('points.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:penalty,reward',
            'default_points' => 'required|integer|min:1',
        ]);

        // Auto-negate points for rewards
        if ($validated['type'] === 'reward') {
            $validated['default_points'] = -abs($validated['default_points']);
        } else {
            $validated['default_points'] = abs($validated['default_points']);
        }

        PointCategory::create($validated);

        return back()->with('success', 'Kategori pelanggaran/prestasi berhasil ditambahkan.');
    }

    public function update(Request $request, PointCategory $pointCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:penalty,reward',
            'default_points' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validated['type'] === 'reward') {
            $validated['default_points'] = -abs($validated['default_points']);
        } else {
            $validated['default_points'] = abs($validated['default_points']);
        }

        $pointCategory->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(PointCategory $pointCategory)
    {
        $pointCategory->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
