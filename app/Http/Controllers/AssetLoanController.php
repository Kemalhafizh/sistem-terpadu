<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetLoanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AssetLoan::class);
        $user = auth()->user();

        $query = AssetLoan::with(['asset', 'borrower', 'approver']);

        // Guru & staf hanya bisa melihat daftar milik mereka sendiri
        if (!in_array($user->role, ['admin', 'superadmin', 'waka_sarpras', 'kepala_sekolah', 'staf_tu'])) {
            $query->where('borrower_id', $user->id);
        } else {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('borrower', function ($qb) use ($search) {
                        $qb->where('name', 'like', '%'.$search.'%');
                    })->orWhereHas('asset', function ($qa) use ($search) {
                        $qa->where('name', 'like', '%'.$search.'%')
                          ->orWhere('asset_code', 'like', '%'.$search.'%');
                    });
                });
            }
        }

        $loans = $query->latest()->paginate(15);
        return view('assets.loans.index', compact('loans'));
    }

    public function create()
    {
        $this->authorize('create', AssetLoan::class);
        // Tampilkan hanya aset yang is_borrowable & status available
        $assets = Asset::where('is_borrowable', true)->where('status', 'available')->get();
        return view('assets.loans.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', AssetLoan::class);
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'expected_return_at' => 'required|date|after:today',
            'purpose' => 'required|string',
        ]);

        $asset = Asset::find($validated['asset_id']);
        if (!$asset->is_borrowable || $asset->status !== 'available') {
            return back()->with('error', 'Aset saat ini tidak dapat dipinjam.');
        }

        AssetLoan::create([
            'asset_id' => $validated['asset_id'],
            'borrower_id' => auth()->id(),
            'expected_return_at' => $validated['expected_return_at'],
            'purpose' => $validated['purpose'],
            'status' => 'pending',
        ]);

        return redirect()->route('assets.loans.index')->with('success', 'Permohonan peminjaman berhasil diajukan.');
    }

    public function show(AssetLoan $loan)
    {
        $this->authorize('view', $loan);
        return view('assets.loans.show', compact('loan'));
    }

    public function approve(Request $request, AssetLoan $loan)
    {
        $this->authorize('approve', AssetLoan::class);
        $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        if ($request->action === 'approve') {
            $loan->update([
                'status' => 'approved', // updates asset status to 'borrowed' via update hook
                'borrowed_at' => now(),
                'approved_by' => auth()->id(),
            ]);
            $message = 'Peminjaman aset berhasil disetujui.';
        } else {
            $loan->update(['status' => 'rejected']);
            $message = 'Peminjaman aset ditolak.';
        }

        return redirect()->route('assets.loans.index')->with('success', $message);
    }

    public function returnAsset(Request $request, AssetLoan $loan)
    {
        $this->authorize('return', AssetLoan::class);
        $request->validate([
            'return_condition' => 'required|in:good,damaged,broken',
            'return_notes' => 'nullable|string',
        ]);

        $loan->update([
            'status' => 'returned',
            'returned_at' => now(),
            'return_condition' => $request->return_condition,
            'return_notes' => $request->return_notes,
        ]);

        return redirect()->route('assets.loans.index')->with('success', 'Aset berhasil dikembalikan.');
    }

    // 🏷️ Generate Sticker QR Labels (PDF)
    public function qrLabelsSheet(Request $request)
    {
        $this->authorize('approve', AssetLoan::class);
        $assets = Asset::all();
        $pdf = Pdf::loadView('assets.qr-labels', compact('assets'));
        return $pdf->setPaper('a4', 'portrait')->download('Stiker_Label_Aset.pdf');
    }

    // 📊 Laporan Penyusutan Garis Lurus (Depreciation)
    public function depreciationReport()
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin', 'waka_sarpras', 'kepala_sekolah'])) {
            abort(403);
        }

        $assets = Asset::whereNotNull('purchase_price')
            ->whereNotNull('useful_life_years')
            ->get();

        return view('assets.depreciation', compact('assets'));
    }
}
