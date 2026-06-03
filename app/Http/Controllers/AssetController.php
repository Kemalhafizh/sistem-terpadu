<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Asset::class);
        $query = Asset::with(['category', 'room', 'creator']);

        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->room) {
            $query->where('room_id', $request->room);
        }
        if ($request->condition) {
            $query->where('condition', $request->condition);
        }
        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('asset_code', 'like', '%'.$request->search.'%');
        }

        $assets = $query->latest()->paginate(20);
        $categories = AssetCategory::all();
        $rooms = Room::all();

        return view('assets.index', compact('assets', 'categories', 'rooms'));
    }

    public function create()
    {
        $this->authorize('create', Asset::class);
        $categories = AssetCategory::all();
        $rooms = Room::all();

        return view('assets.create', compact('categories', 'rooms'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Asset::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'room_id' => 'required|exists:rooms,id',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'useful_life_years' => 'nullable|integer|min:1',
            'is_borrowable' => 'nullable|boolean',
            'condition' => 'required|in:good,damaged,broken',
            'status' => 'required|in:available,borrowed,repairing,lost',
            'description' => 'nullable|string',
        ]);

        $validated['is_borrowable'] = $request->has('is_borrowable') ? true : false;

        $category = AssetCategory::find($validated['category_id']);
        $assetCode = 'INV-'.date('Y').'-'.$category->code.'-'.strtoupper(Str::random(4));

        Asset::create(array_merge($validated, [
            'asset_code' => $assetCode,
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('assets.index')->with('success', 'Aset berhasil didaftarkan.');
    }

    public function show(Asset $asset)
    {
        $this->authorize('view', $asset);

        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $this->authorize('update', $asset);
        $categories = AssetCategory::all();
        $rooms = Room::all();

        return view('assets.edit', compact('asset', 'categories', 'rooms'));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'room_id' => 'required|exists:rooms,id',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'useful_life_years' => 'nullable|integer|min:1',
            'is_borrowable' => 'nullable|boolean',
            'condition' => 'required|in:good,damaged,broken',
            'status' => 'required|in:available,borrowed,repairing,lost',
            'description' => 'nullable|string',
        ]);

        $validated['is_borrowable'] = $request->has('is_borrowable') ? true : false;

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset);
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil dihapus.');
    }
}
