<?php

namespace App\Http\Controllers;

use App\Models\CareerOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CareerOpportunityController extends Controller
{
    /**
     * Display a listing of active career opportunities (for Students & Alumni).
     */
    public function index(Request $request)
    {
        $query = CareerOpportunity::active();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type') && in_array($request->type, ['job', 'scholarship'])) {
            $query->where('type', $request->type);
        }

        $opportunities = $query->latest()->paginate(9);

        return view('career.index', compact('opportunities'));
    }

    /**
     * Display the specified career opportunity detail.
     */
    public function show(CareerOpportunity $opportunity)
    {
        return view('career.show', compact('opportunity'));
    }

    /**
     * Download the supporting PDF brochure.
     */
    public function downloadBrosur(CareerOpportunity $opportunity)
    {
        if (! $opportunity->file_path) {
            abort(404, 'Brosur tidak tersedia untuk lowongan ini.');
        }

        if (! Storage::disk('public')->exists($opportunity->file_path)) {
            abort(404, 'Berkas brosur tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($opportunity->file_path, "Brosur_{$opportunity->company_name}.pdf");
    }

    /**
     * Display management dashboard (for Humas / Admin).
     */
    public function manage()
    {
        $opportunities = CareerOpportunity::with('creator')->latest()->paginate(15);

        return view('career.manage', compact('opportunities'));
    }

    /**
     * Show the form for creating a new opportunity.
     */
    public function create()
    {
        return view('career.create');
    }

    /**
     * Store a newly created opportunity in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'type' => 'required|in:job,scholarship',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'contact_email' => 'required|email|max:255',
            'closing_date' => 'required|date|after_or_equal:today',
            'file' => 'nullable|file|mimes:pdf|max:10240', // Max 10MB PDF
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('career_brochures', 'public');
            $validated['file_path'] = $path;
        }

        $validated['created_by'] = auth()->id();

        unset($validated['file']);

        CareerOpportunity::create($validated);

        return redirect()->route('career-portal.manage')
            ->with('success', 'Kesempatan karir baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified opportunity.
     */
    public function edit(CareerOpportunity $opportunity)
    {
        return view('career.edit', compact('opportunity'));
    }

    /**
     * Update the specified opportunity in storage.
     */
    public function update(Request $request, CareerOpportunity $opportunity)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'type' => 'required|in:job,scholarship',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'contact_email' => 'required|email|max:255',
            'closing_date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:10240', // Max 10MB PDF
        ]);

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($opportunity->file_path) {
                Storage::disk('public')->delete($opportunity->file_path);
            }
            $path = $request->file('file')->store('career_brochures', 'public');
            $validated['file_path'] = $path;
        }

        unset($validated['file']);

        $opportunity->update($validated);

        return redirect()->route('career-portal.manage')
            ->with('success', 'Kesempatan karir berhasil diperbarui.');
    }

    /**
     * Remove the specified opportunity from storage.
     */
    public function destroy(CareerOpportunity $opportunity)
    {
        if ($opportunity->file_path) {
            Storage::disk('public')->delete($opportunity->file_path);
        }

        $opportunity->delete();

        return redirect()->route('career-portal.manage')
            ->with('success', 'Kesempatan karir berhasil dihapus.');
    }
}
