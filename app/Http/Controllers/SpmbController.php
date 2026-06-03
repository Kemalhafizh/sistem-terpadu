<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationSubmitted;
use App\Models\AcademicYear;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SpmbController extends Controller
{
    protected $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    // --- AREA PUBLIK ---

    // Menampilkan Landing Page & Form Pendaftaran
    public function create()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        return view('spmb.create', compact('activeYear'));
    }

    // Menyimpan Data Pendaftaran dari Publik
    public function store(Request $request)
    {
        // Honeypot check
        if ($request->filled('secondary_email')) {
            return abort(422, 'Spam detected.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nisn' => 'nullable|string|max:20',
            'previous_school' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'document_kk' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'document_kk.required' => 'Mohon unggah Kartu Keluarga (KK).',
            'document_kk.mimes' => 'Format KK harus PDF, JPG, atau PNG.',
            'document_kk.max' => 'Ukuran KK tidak boleh lebih dari 2MB.',
            'document_certificate.required' => 'Mohon unggah Ijazah/SKL.',
            'document_certificate.mimes' => 'Format Ijazah harus PDF, JPG, atau PNG.',
            'document_certificate.max' => 'Ukuran Ijazah tidak boleh lebih dari 2MB.',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            return back()->with('error', 'Pendaftaran saat ini sedang ditutup (Tahun Ajaran tidak aktif).');
        }

        $kkPath = null;
        if ($request->hasFile('document_kk')) {
            $kkPath = $request->file('document_kk')->store('spmb/kk', 'public');
        }

        $certPath = null;
        if ($request->hasFile('document_certificate')) {
            $certPath = $request->file('document_certificate')->store('spmb/certificates', 'public');
        }

        $registration = Registration::create([
            'registration_number' => Registration::generateRegistrationNumber(),
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'nisn' => $validated['nisn'],
            'previous_school' => $validated['previous_school'],
            'parent_phone' => $validated['parent_phone'],
            'document_kk' => $kkPath,
            'document_certificate' => $certPath,
            'status' => 'pending',
            'academic_year_id' => $activeYear->id,
        ]);

        // Send Email Notification
        try {
            Mail::to($registration->email)->send(new RegistrationSubmitted($registration));
        } catch (\Exception $e) {
            Log::error('Failed to send registration email: '.$e->getMessage());
        }

        // Simpan nomor pendaftaran ke session untuk ditampilkan di halaman sukses
        return redirect()->route('spmb.success')->with('registration_number', $registration->registration_number);
    }

    // Halaman Sukses
    public function success()
    {
        if (! session('registration_number')) {
            return redirect()->route('spmb.create');
        }
        $candidate = Registration::where('registration_number', session('registration_number'))->first();

        return view('spmb.success', compact('candidate'));
    }

    // Cek Status Pendaftaran
    public function checkStatus(Request $request)
    {
        $registration = null;
        if ($request->has('registration_number') && $request->has('nisn')) {
            $registration = Registration::where('registration_number', $request->registration_number)
                ->where('nisn', $request->nisn)
                ->first();

            if (! $registration) {
                return back()->with('error', 'Data tidak ditemukan. Pastikan Nomor Pendaftaran dan NISN benar.');
            }
        }

        return view('spmb.status', compact('registration'));
    }

    // --- AREA ADMIN (DASHBOARD) ---

    // Menampilkan Kanban Board / Tabel Pendaftar
    public function index()
    {
        // Authorization: handled by 'role:admin,superadmin' middleware
        $registrations = Registration::with('academicYear')->latest()->get();

        $pending = $registrations->where('status', 'pending');
        $reviewing = $registrations->where('status', 'reviewing');
        $accepted = $registrations->where('status', 'accepted');
        $rejected = $registrations->where('status', 'rejected');

        return view('spmb.admin.index', compact('pending', 'reviewing', 'accepted', 'rejected'));
    }

    // Melihat Detail Pendaftar
    public function show(Registration $registration)
    {
        // Authorization: handled by 'role:admin,superadmin' middleware
        return view('spmb.admin.show', compact('registration'));
    }

    // Mengubah Status & Konversi Otomatis
    public function updateStatus(Request $request, Registration $registration)
    {
        $validated = $request->validate([
            'status' => 'required|in:reviewing,accepted,rejected',
        ]);

        try {
            if ($validated['status'] == 'accepted') {
                $result = $this->registrationService->convertToStudent($registration);
                $message = $result['message'];
            } else {
                $registration->status = $validated['status'];
                $registration->save();
                $message = 'Status pendaftar berhasil diperbarui menjadi: '.strtoupper($validated['status']);
            }

            return redirect()->route('spmb.admin.index')->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
