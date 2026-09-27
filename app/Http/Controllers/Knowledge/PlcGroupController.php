<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\PlcGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlcGroupController extends Controller
{
    public const TYPES = [
        'rumpun_mapel' => 'Rumpun mata pelajaran',
        'lintas_mapel' => 'Lintas mata pelajaran',
        'jabatan' => 'Jabatan / peran',
        'minat_khusus' => 'Minat khusus',
    ];

    public function index()
    {
        $groups = PlcGroup::with('coordinator')->withCount(['members', 'discussions'])->where('is_active', true)->latest()->get();

        return view('knowledge.plc.index', ['groups' => $groups, 'types' => self::TYPES]);
    }

    public function show($id)
    {
        $group = PlcGroup::with(['coordinator', 'members.user', 'discussions' => fn ($q) => $q->latest()->limit(20)])->findOrFail($id);

        return view('knowledge.plc.show', ['group' => $group, 'types' => self::TYPES]);
    }

    public function create()
    {
        return view('knowledge.plc.create', ['types' => self::TYPES]);
    }

    public function store(Request $request)
    {
        // This used to redirect without saving anything.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', array_keys(self::TYPES)),
            'description' => 'nullable|string',
        ]);

        $group = DB::transaction(function () use ($validated) {
            $group = PlcGroup::create($validated + ['coordinator_id' => auth()->id(), 'is_active' => true]);
            $group->members()->create(['user_id' => auth()->id(), 'role' => 'coordinator']);

            return $group;
        });

        return redirect()->route('knowledge.plc.show', $group->id)->with('success', 'Komunitas praktisi berhasil dibuat.');
    }
}
