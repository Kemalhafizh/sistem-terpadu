<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::withCount('assets')->get();

        return view('assets.rooms', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        Room::create($validated);

        return back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function destroy(Room $room)
    {
        if ($room->assets()->exists()) {
            return back()->with('error', 'Ruangan tidak bisa dihapus karena masih memiliki data aset.');
        }
        $room->delete();

        return back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
