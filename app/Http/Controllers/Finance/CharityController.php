<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CharityCampaign;
use App\Models\CharityDonation;
use App\Models\Student;
use App\Models\WadiahAccount;
use App\Services\CharityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CharityController extends Controller
{
    protected $charityService;

    public function __construct(CharityService $charityService)
    {
        $this->charityService = $charityService;
    }

    /**
     * Display listing of campaigns (Admin).
     */
    public function index()
    {
        $campaigns = CharityCampaign::withCount('donations')->latest()->get();

        return view('finance.charity.index', compact('campaigns'));
    }

    /**
     * Show form to create new campaign (Admin).
     */
    public function create()
    {
        return view('finance.charity.create');
    }

    /**
     * Store new campaign.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'type' => 'required|in:zakat,infaq,shodaqoh,wakaf',
            'description' => 'required|string',
            'target_amount' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date|after_or_equal:today',
        ]);

        $this->charityService->createCampaign($validated);

        return redirect()->route('admin.finance.charity.index')
            ->with('success', 'Kampanye donasi baru berhasil dibuat.');
    }

    /**
     * Show form to edit campaign (Admin).
     */
    public function edit($id)
    {
        $campaign = CharityCampaign::findOrFail($id);

        return view('finance.charity.edit', compact('campaign'));
    }

    /**
     * Update campaign.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'type' => 'required|in:zakat,infaq,shodaqoh,wakaf',
            'description' => 'required|string',
            'target_amount' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        $campaign = CharityCampaign::findOrFail($id);
        $campaign->update($validated);

        return redirect()->route('admin.finance.charity.index')
            ->with('success', 'Kampanye donasi berhasil diperbarui.');
    }

    /**
     * Show list of donations for a specific campaign (Admin).
     */
    public function campaignDonors($campaignId, Request $request)
    {
        $campaign = CharityCampaign::findOrFail($campaignId);
        $status = $request->get('status');

        $donations = CharityDonation::with('user')
            ->where('charity_campaign_id', $campaignId)
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return view('finance.charity.campaign_donors', compact('campaign', 'donations', 'status'));
    }

    /**
     * Verify a pending donation.
     */
    public function verify($id)
    {
        try {
            $this->charityService->verifyDonation($id, auth()->id());

            return back()->with('success', 'Donasi berhasil diverifikasi.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memverifikasi donasi: '.$e->getMessage());
        }
    }

    /**
     * Cancel a pending donation.
     */
    public function cancel($id)
    {
        try {
            $this->charityService->cancelDonation($id);

            return back()->with('success', 'Donasi berhasil dibatalkan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan donasi: '.$e->getMessage());
        }
    }

    /**
     * Charity Portal Dashboard (Student/Parent).
     */
    public function portal()
    {
        $campaigns = CharityCampaign::active()->latest()->get();
        $wallets = $this->familyWallets();

        return view('finance.charity.portal', compact('campaigns', 'wallets'));
    }

    /**
     * Show donation payment form.
     */
    public function donateForm($campaignId)
    {
        $campaign = CharityCampaign::active()->findOrFail($campaignId);
        $wallets = $this->familyWallets();

        return view('finance.charity.donate', compact('campaign', 'wallets'));
    }

    /**
     * The wadiah savings the caller may pay from: their own as a student, each
     * child's as a parent, none otherwise. A student without an account has
     * balance null ("belum punya tabungan"), not Rp 0.
     *
     * @return Collection<int, array{student: Student, balance: ?float}>
     */
    private function familyWallets(): Collection
    {
        $students = Student::ofFamily(auth()->user())->with('user')->get();
        $accounts = WadiahAccount::whereIn('student_id', $students->pluck('id'))->get()->keyBy('student_id');

        return $students->map(fn (Student $student) => [
            'student' => $student,
            'balance' => isset($accounts[$student->id]) ? (float) $accounts[$student->id]->balance : null,
        ])->values();
    }

    /**
     * Process donation submission.
     */
    public function processDonate(Request $request)
    {
        $request->validate([
            'charity_campaign_id' => 'required|exists:charity_campaigns,id',
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:transfer,wadiah_wallet,cash',
            'evidence' => 'nullable|image|max:2048',
            'donor_name' => 'nullable|string|max:100',
            'is_anonymous' => 'nullable|boolean',
            'student_id' => 'nullable|integer',
        ]);

        $data = $request->all();

        // Handle bank transfer evidence upload
        if ($request->hasFile('evidence') && $request->payment_method === 'transfer') {
            $path = $request->file('evidence')->store('evidence_donations', 'public');
            $data['evidence_path'] = $path;
        }

        try {
            $donation = $this->charityService->donate($data, auth()->id());

            if ($donation->status === 'verified') {
                return redirect()->route('portal.charity.history')
                    ->with('success', 'Donasi Anda berhasil disalurkan via Wadiah E-Wallet! Jazakumullah Khairan.');
            }

            return redirect()->route('portal.charity.history')
                ->with('success', 'Donasi berhasil diajukan. Silakan tunggu verifikasi admin.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show personal donation history.
     */
    public function history()
    {
        $donations = CharityDonation::with('campaign')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('finance.charity.history', compact('donations'));
    }

    /**
     * Download Certificate of Donation.
     */
    public function downloadCertificate($donationId)
    {
        $donation = CharityDonation::with('campaign')->findOrFail($donationId);

        $user = auth()->user();
        if ($user->role !== 'admin' && $user->role !== 'superadmin' && $donation->user_id !== $user->id) {
            abort(403, 'Aksi tidak diotorisasi.');
        }

        if ($donation->status !== 'verified') {
            return back()->with('error', 'Sertifikat hanya dapat diunduh untuk donasi yang sudah terverifikasi.');
        }

        $pdf = Pdf::loadView('finance.charity.certificate_pdf', compact('donation'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("Sertifikat_Donasi_{$donation->receipt_number}.pdf");
    }
}
