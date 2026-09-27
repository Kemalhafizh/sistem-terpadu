<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Partnership;
use App\Services\Community\PartnershipService;
use Illuminate\Http\Request;

class PartnershipController extends Controller
{
    public function index()
    {
        $partnerships = Partnership::with('creator')->latest()->get();

        return view('partnerships.index', compact('partnerships'));
    }

    public function create()
    {
        return view('partnerships.create');
    }

    public function store(Request $request, PartnershipService $service)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string',
            'partner_type' => 'required|in:university,corporate,government,ngo,community',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
        ]);

        $service->registerPartner($validated['organization_name'], $validated['partner_type'], auth()->id(), $validated['contact_person'] ?? null, $validated['contact_email'] ?? null);

        return redirect()->route('partnerships.index')->with('success', 'Mitra komunitas/industri baru berhasil didaftarkan!');
    }
}
