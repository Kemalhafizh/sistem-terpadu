<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::withCount('assets')->get();

        return view('assets.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:asset_categories,code',
        ]);

        AssetCategory::create($validated);

        return back()->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    public function destroy(AssetCategory $assetCategory)
    {
        if ($assetCategory->assets()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki data aset.');
        }
        $assetCategory->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
