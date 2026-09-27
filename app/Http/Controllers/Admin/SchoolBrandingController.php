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
            'school_short_name' => 'nullable|string|max:255',
            'foundation_name' => 'required|string|max:255',
            // NPSN is always 8 digits.
            'school_npsn' => ['required', 'string', 'regex:/^\d{8}$/'],
            'school_akreditasi' => 'required|string|max:50',
            'school_address' => 'required|string|max:500',
            'school_phone' => 'required|string|max:50',
            'school_email' => 'required|email|max:255',
            'school_website' => 'nullable|url|max:255',
            // International format, used for wa.me links in the app.
            'school_whatsapp' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'principal_name' => 'required|string|max:255',
            // NIP (ASN) is 18 digits; a yayasan's own staff number may differ, so only digits/spaces/dots are enforced.
            'principal_nip' => ['required', 'string', 'max:30', 'regex:/^[0-9 .]+$/'],
            // These were shown on the form but never validated, so they were
            // silently dropped and the attendance geofence could not be changed.
            'school_maps_url' => 'nullable|url|max:500',
            'school_latitude' => 'required|numeric|between:-90,90',
            'school_longitude' => 'required|numeric|between:-180,180',
            'school_geofence_radius' => 'required|numeric|between:20,5000',
        ], [
            'school_npsn.regex' => 'NPSN harus 8 digit angka.',
            'principal_nip.regex' => 'NIP hanya boleh berisi angka (spasi/titik diperbolehkan).',
            'school_whatsapp.regex' => 'Nomor WhatsApp hanya angka, boleh diawali +, 8–15 digit (mis. +6289682585200).',
        ]);

        try {
            $brandingService->updateSchoolProfile($validated);

            return redirect()->route('admin.school.branding.index')
                ->with('success', 'Profil Konfigurasi Tunggal ' . $validated['school_name'] . ' berhasil diperbarui!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui profil sekolah: ' . $e->getMessage());
        }
    }
}
