<?php

namespace App\Http\Controllers\Admin;

use App\Domains\School\Services\SchoolBrandingDomainService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class SchoolBrandingController extends Controller
{
    /**
     * Display Single-School Enterprise Core Branding Settings.
     */
    public function index(SchoolBrandingDomainService $brandingService)
    {
        $profile = $brandingService->getSchoolProfile();
        $headerData = $brandingService->getFormalHeaderData();

        return view('admin.school.branding', compact('profile', 'headerData'));
    }

    /**
     * Update Single-School Enterprise Core Profile.
     */
    public function update(Request $request, SchoolBrandingDomainService $brandingService)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'foundation_name' => 'required|string|max:255',
            'school_npsn' => 'required|string|max:50',
            'school_akreditasi' => 'required|string|max:50',
            'school_address' => 'required|string|max:500',
            'school_phone' => 'required|string|max:50',
            'school_email' => 'required|email|max:255',
            'principal_name' => 'required|string|max:255',
            'principal_nip' => 'required|string|max:100',
            'active_academic_year' => 'required|string|max:100',
        ]);

        try {
            $brandingService->updateSchoolProfile($validated);

            return redirect()->route('admin.school.branding.index')
                ->with('success', 'Profil Konfigurasi Tunggal SMA Islam Sultan Agung 1 Semarang berhasil diperbarui!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui profil sekolah: ' . $e->getMessage());
        }
    }
}
