<?php

namespace App\Http\Controllers;

use App\Events\NewNotificationEvent;
use App\Models\PortfolioBadge;
use App\Models\PortfolioItem;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    /**
     * Tampilkan halaman direktori portofolio siswa untuk diulas oleh Guru/BK.
     */
    public function index()
    {
        Gate::authorize('verify', PortfolioItem::class);

        $students = Student::with(['user', 'classroom'])
            ->withCount(['portfolioItems', 'badges'])
            ->get();

        return view('portfolios.index', compact('students'));
    }

    /**
     * Tampilkan halaman pengelolaan portofolio privat milik siswa yang bersangkutan.
     */
    public function mine()
    {
        $student = Student::where('user_id', auth()->id())
            ->with(['portfolioItems.verifier', 'badges'])
            ->firstOrFail();

        $allBadges = PortfolioBadge::all();

        return view('portfolios.mine', compact('student', 'allBadges'));
    }

    /**
     * Tampilkan halaman ulasan portofolio & penganugerahan lencana per siswa (untuk Guru/BK).
     */
    public function review(Student $student)
    {
        Gate::authorize('verify', PortfolioItem::class);

        $student->load(['portfolioItems.verifier', 'badges', 'user', 'classroom']);
        $allBadges = PortfolioBadge::all();

        return view('portfolios.review', compact('student', 'allBadges'));
    }

    /**
     * Tampilkan halaman etalase portofolio publik siswa berdesain glassmorphic (Bypass Auth).
     */
    public function publicPortfolio(string $uuid)
    {
        $student = Student::where('uuid', $uuid)
            ->with(['user', 'classroom'])
            ->firstOrFail();

        // Hanya tampilkan item yang TERVERIFIKASI dan diatur PUBLIK
        $portfolioItems = $student->portfolioItems()
            ->verified()
            ->public()
            ->orderBy('date', 'desc')
            ->get();

        $badges = $student->badges;

        return view('portfolios.public', compact('student', 'portfolioItems', 'badges'));
    }

    /**
     * Menyimpan item portofolio baru siswa.
     */
    public function store(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:prestasi,sertifikat,karya,proyek,organisasi,other',
            'date' => 'nullable|date',
            'external_link' => 'nullable|url',
            'file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('uploads/portfolios', 'public');
        }

        $student->portfolioItems()->create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'date' => $request->date,
            'file_path' => $filePath,
            'external_link' => $request->external_link,
            'is_verified' => false,
            'is_public' => false,
        ]);

        return redirect()->route('portfolios.mine')
            ->with('success', 'Item portofolio berhasil ditambahkan sebagai draf.');
    }

    /**
     * Memperbarui draf item portofolio siswa (sebelum terverifikasi).
     */
    public function update(Request $request, PortfolioItem $portfolioItem)
    {
        Gate::authorize('manage', $portfolioItem);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:prestasi,sertifikat,karya,proyek,organisasi,other',
            'date' => 'nullable|date',
            'external_link' => 'nullable|url',
            'file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'date' => $request->date,
            'external_link' => $request->external_link,
        ];

        if ($request->hasFile('file')) {
            if ($portfolioItem->file_path) {
                Storage::disk('public')->delete($portfolioItem->file_path);
            }
            $data['file_path'] = $request->file('file')->store('uploads/portfolios', 'public');
        }

        $portfolioItem->update($data);

        return redirect()->route('portfolios.mine')
            ->with('success', 'Item portofolio berhasil diperbarui.');
    }

    /**
     * Menghapus draf item portofolio siswa.
     */
    public function destroy(PortfolioItem $portfolioItem)
    {
        Gate::authorize('manage', $portfolioItem);

        if ($portfolioItem->file_path) {
            Storage::disk('public')->delete($portfolioItem->file_path);
        }
        $portfolioItem->delete();

        return redirect()->route('portfolios.mine')
            ->with('success', 'Item portofolio berhasil dihapus.');
    }

    /**
     * Mengganti visibilitas publik per item portofolio.
     */
    public function toggleVisibility(PortfolioItem $portfolioItem)
    {
        if ($portfolioItem->student->user_id !== auth()->id()) {
            abort(403, 'Aksi ilegal.');
        }

        $portfolioItem->update([
            'is_public' => !$portfolioItem->is_public,
        ]);

        $status = $portfolioItem->is_public ? 'publik' : 'privat';

        return redirect()->route('portfolios.mine')
            ->with('success', "Visibilitas item diubah menjadi {$status}.");
    }

    /**
     * Menyetujui/memverifikasi klaim keaslian portofolio siswa (untuk Guru/BK).
     */
    public function verify(Request $request, PortfolioItem $portfolioItem)
    {
        Gate::authorize('verify', PortfolioItem::class);

        $request->validate([
            'status' => 'required|in:verified,unverified',
        ]);

        $isVerified = $request->status === 'verified';

        $portfolioItem->update([
            'is_verified' => $isVerified,
            'verified_by' => auth()->id(),
            'verified_at' => $isVerified ? now() : null,
        ]);

        // Kirimkan notifikasi real-time WebSocket ke siswa
        event(new NewNotificationEvent($portfolioItem->student->user, [
            'id' => $portfolioItem->id,
            'type' => 'portfolio_verified',
            'title' => $isVerified ? 'Sertifikat Terverifikasi! 🎉' : 'Status Verifikasi Dicabut',
            'description' => $isVerified 
                ? 'Item portofolio "' . $portfolioItem->title . '" Anda telah disetujui sekolah.' 
                : 'Verifikasi item portofolio "' . $portfolioItem->title . '" Anda dicabut oleh staf pengajar.',
            'created_at' => now()->toDateTimeString(),
        ]));

        return redirect()->route('portfolios.review', $portfolioItem->student)
            ->with('success', $isVerified ? 'Item portofolio berhasil diverifikasi.' : 'Status verifikasi berhasil dicabut.');
    }

    /**
     * Menganugerahkan lencana sekolah secara manual kepada siswa (untuk Guru/BK).
     */
    public function awardBadge(Request $request, Student $student)
    {
        Gate::authorize('awardBadge', PortfolioItem::class);

        $request->validate([
            'badge_id' => 'required|exists:portfolio_badges,id',
            'notes' => 'nullable|string',
        ]);

        // Pakai syncWithoutDetaching agar tidak duplikat atau merusak badge lain
        $student->badges()->syncWithoutDetaching([
            $request->badge_id => [
                'awarded_by' => auth()->id(),
                'earned_at' => now(),
                'notes' => $request->notes,
            ]
        ]);

        $badge = PortfolioBadge::find($request->badge_id);

        // Broadcast notifikasi real-time ke siswa
        event(new NewNotificationEvent($student->user, [
            'id' => $badge->id,
            'type' => 'portfolio_badge',
            'title' => 'Lencana Penghargaan Baru! 🏆',
            'description' => 'Selamat! Anda dianugerahi lencana kehormatan "' . $badge->name . '".',
            'created_at' => now()->toDateTimeString(),
        ]));

        return redirect()->route('portfolios.review', $student)
            ->with('success', "Lencana {$badge->name} berhasil dianugerahkan kepada siswa.");
    }
}
