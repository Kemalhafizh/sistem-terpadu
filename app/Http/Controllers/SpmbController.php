<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationSubmitted;
use App\Models\AcademicYear;
use App\Models\Registration;
use App\Models\SpmbDocument;
use App\Models\SpmbPeriod;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;
use App\Services\RegistrationService;
use App\Services\SpmbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SpmbController extends Controller
{
    protected $registrationService;

    protected $spmbService;

    public function __construct(RegistrationService $registrationService, SpmbService $spmbService)
    {
        $this->registrationService = $registrationService;
        $this->spmbService = $spmbService;
    }

    // --- AREA PUBLIK ---

    // Menampilkan Form Pendaftaran Multi-Step
    public function create()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return view('spmb.create', ['activeYear' => null]);
        }

        // Get open waves for the active period
        $activePeriod = SpmbPeriod::where('academic_year_id', $activeYear->id)
            ->where('status', 'open')
            ->first();

        $openWaves = collect();
        $tracks = collect();

        if ($activePeriod) {
            $openWaves = SpmbWave::where('spmb_period_id', $activePeriod->id)
                ->where('status', 'open')
                ->get();

            $tracks = SpmbTrack::where('spmb_period_id', $activePeriod->id)->get();
        }

        return view('spmb.create', compact('activeYear', 'activePeriod', 'openWaves', 'tracks'));
    }

    // Menyimpan Data Pendaftaran dari Publik
    public function store(Request $request)
    {
        // Honeypot check
        if ($request->filled('secondary_email')) {
            return abort(422, 'Spam detected.');
        }

        $validated = $request->validate([
            'spmb_wave_id' => 'required|exists:spmb_waves,id',
            'spmb_track_id' => 'required|exists:spmb_tracks,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nisn' => 'nullable|string|max:20',
            'previous_school' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:255',
            'address' => 'required|string',
            'parent_name' => 'required|string|max:255',
            'document_kk' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_skhun' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_akta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'rapor_scores' => 'required|array',
            'achievement_data' => 'nullable|array',
            'tahfidz_data' => 'nullable|array',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'Pendaftaran saat ini sedang ditutup.');
        }

        $kkPath = $request->file('document_kk')->store('spmb/kk', 'local');
        $certPath = $request->file('document_certificate')->store('spmb/certificates', 'local');
        $photoPath = $request->file('photo')->store('spmb/photos', 'local');

        $skhunPath = null;
        if ($request->hasFile('document_skhun')) {
            $skhunPath = $request->file('document_skhun')->store('spmb/skhun', 'local');
        }

        $aktaPath = null;
        if ($request->hasFile('document_akta')) {
            $aktaPath = $request->file('document_akta')->store('spmb/akta', 'local');
        }

        $registration = DB::transaction(function () use ($validated, $activeYear, $kkPath, $certPath, $photoPath, $skhunPath, $aktaPath) {
            $reg = Registration::create([
                'registration_number' => Registration::generateRegistrationNumber(),
                'spmb_wave_id' => $validated['spmb_wave_id'],
                'spmb_track_id' => $validated['spmb_track_id'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'nisn' => $validated['nisn'],
                'previous_school' => $validated['previous_school'],
                'parent_phone' => $validated['parent_phone'],
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date'],
                'birth_place' => $validated['birth_place'],
                'address' => $validated['address'],
                'parent_name' => $validated['parent_name'],
                'document_kk' => $kkPath,
                'document_certificate' => $certPath,
                'photo_path' => $photoPath,
                'document_skhun_path' => $skhunPath,
                'document_akta_path' => $aktaPath,
                'rapor_scores' => $validated['rapor_scores'],
                'achievement_data' => $validated['achievement_data'] ?? [],
                'tahfidz_data' => $validated['tahfidz_data'] ?? ['juz_count' => 0],
                'status' => 'pending',
                'document_status' => 'submitted',
                'academic_year_id' => $activeYear->id,
            ]);

            // Save documents separately for verifier dashboard
            SpmbDocument::create([
                'registration_id' => $reg->id,
                'document_type' => 'kk',
                'file_path' => $kkPath,
            ]);

            SpmbDocument::create([
                'registration_id' => $reg->id,
                'document_type' => 'certificate',
                'file_path' => $certPath,
            ]);

            SpmbDocument::create([
                'registration_id' => $reg->id,
                'document_type' => 'photo',
                'file_path' => $photoPath,
            ]);

            if ($skhunPath) {
                SpmbDocument::create([
                    'registration_id' => $reg->id,
                    'document_type' => 'skhun',
                    'file_path' => $skhunPath,
                ]);
            }

            if ($aktaPath) {
                SpmbDocument::create([
                    'registration_id' => $reg->id,
                    'document_type' => 'akta',
                    'file_path' => $aktaPath,
                ]);
            }

            return $reg;
        });

        // Send Email Notification
        try {
            Mail::to($registration->email)->send(new RegistrationSubmitted($registration));
        } catch (\Exception $e) {
            Log::error('Failed to send registration email: '.$e->getMessage());
        }

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

    // Lookup Hasil Kelulusan Publik
    public function publicAnnouncement(Request $request)
    {
        $registration = null;
        if ($request->filled('search')) {
            $registration = Registration::where('registration_number', $request->search)
                ->orWhere('nisn', $request->search)
                ->first();
        }

        return view('spmb.announcement', compact('registration'));
    }

    // Cetak Kartu Peserta
    public function printCard(Registration $registration)
    {
        if (! $this->canViewPrintCard($registration)) {
            abort(403, 'Akses ditolak. Anda tidak berwenang mencetak kartu peserta ini.');
        }

        return view('spmb.print-card', compact('registration'));
    }

    // Pas foto peserta pada kartu peserta yang sah
    public function printCardPhoto(Registration $registration)
    {
        if (! $this->canViewPrintCard($registration)) {
            abort(403, 'Akses ditolak. Anda tidak berwenang melihat foto peserta ini.');
        }

        return $this->servePrivateFile($registration->photo_path);
    }

    // Pas foto peserta untuk admin SPMB
    public function adminPhoto(Registration $registration)
    {
        return $this->servePrivateFile($registration->photo_path);
    }

    // Dokumen pendaftar untuk admin SPMB
    public function showDocumentFile(SpmbDocument $document)
    {
        return $this->servePrivateFile($document->file_path);
    }

    private function canViewPrintCard(Registration $registration): bool
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (in_array($user->role, ['admin', 'superadmin', 'staf_tu', 'kepala_sekolah'], true)) {
                return true;
            }
        }

        return session('registration_number') === $registration->registration_number;
    }

    private function servePrivateFile(?string $path)
    {
        if (! $path) {
            abort(404);
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404);
    }

    // --- AREA ADMIN (DASHBOARD) ---

    // Dashboard SPMB Admin
    public function adminDashboard()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $periods = SpmbPeriod::with('academicYear')->get();
        $waves = SpmbWave::all();
        $applicants = Registration::with(['spmbTrack', 'spmbWave'])->get();

        return view('spmb.admin.dashboard', compact('activeYear', 'periods', 'waves', 'applicants'));
    }

    // Kelola Periode
    public function periods()
    {
        $periods = SpmbPeriod::with('academicYear')->latest()->get();
        $academicYears = AcademicYear::latest()->get();

        return view('spmb.admin.periods', compact('periods', 'academicYears'));
    }

    public function storePeriod(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id|unique:spmb_periods,academic_year_id',
            'name' => 'required|string|max:255',
            'total_quota' => 'required|integer|min:1',
            'status' => 'required|in:draft,open,closed,announced',
        ]);

        if ($validated['status'] == 'open') {
            SpmbPeriod::query()->update(['status' => 'closed']);
        }

        SpmbPeriod::create($validated);

        return redirect()->route('spmb.admin.periods')->with('success', 'Periode SPMB berhasil disimpan.');
    }

    // Kelola Gelombang
    public function waves()
    {
        $waves = SpmbWave::with('period')->latest()->get();
        $periods = SpmbPeriod::latest()->get();

        return view('spmb.admin.waves', compact('waves', 'periods'));
    }

    public function storeWave(Request $request)
    {
        $validated = $request->validate([
            'spmb_period_id' => 'required|exists:spmb_periods,id',
            'name' => 'required|string|max:255',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after_or_equal:registration_start',
            'announcement_date' => 'nullable|date|after:registration_end',
            'quota' => 'required|integer|min:1',
            'registration_fee' => 'required|numeric|min:0',
            'status' => 'required|in:upcoming,open,closed,announced',
        ]);

        if ($validated['status'] == 'open') {
            SpmbWave::where('spmb_period_id', $validated['spmb_period_id'])->update(['status' => 'closed']);
        }

        SpmbWave::create($validated);

        return redirect()->route('spmb.admin.waves')->with('success', 'Gelombang pendaftaran berhasil disimpan.');
    }

    // Kelola Jalur
    public function tracks()
    {
        $tracks = SpmbTrack::with('period')->latest()->get();
        $periods = SpmbPeriod::latest()->get();

        return view('spmb.admin.tracks', compact('tracks', 'periods'));
    }

    public function storeTrack(Request $request)
    {
        $validated = $request->validate([
            'spmb_period_id' => 'required|exists:spmb_periods,id',
            'name' => 'required|string|max:255',
            'quota' => 'required|integer|min:1',
            'scoring_weights' => 'required|array',
            'requirements' => 'nullable|string',
        ]);

        SpmbTrack::create($validated);

        return redirect()->route('spmb.admin.tracks')->with('success', 'Jalur seleksi berhasil disimpan.');
    }

    // Verifikasi Dokumen
    public function verifyDocument(SpmbDocument $document, Request $request)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:verified,rejected',
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        $document->update([
            'verification_status' => $validated['verification_status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // Update overall registration document_status
        $registration = $document->registration;
        $totalDocs = $registration->spmbDocuments()->count();
        $verifiedDocs = $registration->spmbDocuments()->where('verification_status', 'verified')->count();
        $rejectedDocs = $registration->spmbDocuments()->where('verification_status', 'rejected')->count();

        if ($rejectedDocs > 0) {
            $registration->document_status = 'rejected';
        } elseif ($verifiedDocs == $totalDocs) {
            $registration->document_status = 'verified';
        } else {
            $registration->document_status = 'submitted';
        }
        $registration->save();

        return back()->with('success', 'Status verifikasi dokumen berhasil diperbarui.');
    }

    // Input Nilai Seleksi
    public function scoring(SpmbWave $wave, Request $request)
    {
        $applicants = Registration::with('spmbTrack')->where('spmb_wave_id', $wave->id)->get();

        return view('spmb.admin.scoring', compact('wave', 'applicants'));
    }

    public function storeScoring(SpmbWave $wave, Request $request)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->scores as $applicantId => $score) {
                Registration::where('id', $applicantId)->update([
                    'entrance_test_score' => $score,
                ]);
            }
        });

        return redirect()->route('spmb.admin.scoring', $wave)->with('success', 'Nilai seleksi pendaftar berhasil disimpan.');
    }

    // Eksekusi Pemeringkatan Seleksi
    public function selection(SpmbWave $wave)
    {
        $applicants = Registration::where('spmb_wave_id', $wave->id)
            ->with(['spmbTrack'])
            ->orderBy('total_score', 'desc')
            ->get();

        return view('spmb.admin.selection', compact('wave', 'applicants'));
    }

    public function runSelection(SpmbWave $wave)
    {
        $summary = $this->spmbService->runSelection($wave->id);

        $wave->update(['status' => 'announced']);

        return redirect()->route('spmb.admin.selection', $wave)->with('success', "Proses seleksi selesai dilaksanakan. Diterima: {$summary['accepted']}, Ditolak: {$summary['rejected']}.");
    }

    // Manajemen Daftar Ulang
    public function reregistration(SpmbWave $wave)
    {
        $applicants = Registration::with('spmbTrack')->where('spmb_wave_id', $wave->id)
            ->where('status', 'accepted')
            ->get();

        return view('spmb.admin.reregistration', compact('wave', 'applicants'));
    }

    public function confirmReregistered(Registration $registration)
    {
        $registration->update([
            'has_reregistered' => true,
        ]);

        return back()->with('success', 'Status daftar ulang untuk pendaftar '.$registration->full_name.' berhasil dikonfirmasi.');
    }

    // Konversi Massal Pendaftar Lolos -> Akun Siswa Baru
    public function bulkConvert(SpmbWave $wave)
    {
        $result = $this->spmbService->bulkConvert($wave->id);
        $back = back()->with('success', __(':count calon siswa dijadikan siswa aktif.', ['count' => $result['converted']]));

        // Say who was left out and why, instead of only counting the ones that worked.
        return $result['skipped'] === [] ? $back : $back->with('warning', implode(' ', $result['skipped']));
    }

    // Area Kanban Pendaftar (Lama)
    public function index()
    {
        $registrations = Registration::with('academicYear')->latest()->get();

        $pending = $registrations->where('status', 'pending');
        $reviewing = $registrations->where('status', 'reviewing');
        $accepted = $registrations->where('status', 'accepted');
        $rejected = $registrations->where('status', 'rejected');

        return view('spmb.admin.index', compact('pending', 'reviewing', 'accepted', 'rejected'));
    }

    // Detail Pendaftar
    public function show(Registration $registration)
    {
        $registration->load(['spmbDocuments.verifier', 'spmbWave', 'spmbTrack']);

        return view('spmb.admin.show', compact('registration'));
    }

    // Mengubah Status (Lama)
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
