<?php

namespace App\Http\Controllers\Grc;

use App\Http\Controllers\Controller;
use App\Models\SopDocument;
use App\Services\Grc\SopGovernanceService;
use Illuminate\Http\Request;

class SopGovernanceController extends Controller
{
    /**
     * Display Searchable SOP Library & Catalog.
     */
    public function index(Request $request)
    {
        $query = SopDocument::with(['author', 'approver']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%')
                ->orWhere('code', 'like', '%'.$request->search.'%');
        }

        $sops = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('sop.index', compact('sops'));
    }

    /**
     * Display SOP Document Creation Form.
     */
    public function create()
    {
        return view('sop.index');
    }

    /**
     * Store new SOP document draft.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'code' => 'required|string|max:50|unique:sop_documents,code',
            'category' => 'required|in:academic,administrative,financial,safety,it',
            'content_html' => 'required|string',
            'effective_date' => 'required|date',
            'review_date' => 'required|date|after_or_equal:effective_date',
        ]);

        $authorId = auth()->id();
        $sop = SopDocument::create(array_merge($request->all(), [
            'author_id' => $authorId,
            'version' => 1,
            'status' => 'published',
            'approved_by' => $authorId,
        ]));

        return redirect()->route('sop.show', $sop->id)
            ->with('success', "Dokumen SOP [{$sop->code}: {$sop->title}] berhasil diterbitkan!");
    }

    /**
     * Display SOP Reader interface + digital acknowledgement signing button.
     */
    public function show(int $id, SopGovernanceService $sopService)
    {
        $sop = SopDocument::with(['author', 'approver', 'acknowledgements.user'])->findOrFail($id);
        $isAcknowledged = $sop->isAcknowledgedBy(auth()->id());
        $complianceRate = $sopService->getComplianceRate($sop->id);

        return view('sop.show', compact('sop', 'isAcknowledged', 'complianceRate'));
    }

    /**
     * Publish & Approve SOP document.
     */
    public function publish(int $id, SopGovernanceService $sopService)
    {
        $approverId = auth()->id();
        $sop = $sopService->publish($id, $approverId);

        return back()->with('success', "SOP #{$sop->code} berhasil disetujui & DITERBITKAN!");
    }

    /**
     * Digital acknowledgement signing by staff/user.
     */
    public function acknowledge(int $id, Request $request, SopGovernanceService $sopService)
    {
        $userId = auth()->id();
        $ip = $request->ip();

        $sopService->acknowledge($id, $userId, $ip);

        return back()->with('success', 'Terima kasih, konfirmasi pembacaan SOP berhasil dicatat secara sah!');
    }

    /**
     * Display Compliance Rate Analytics Dashboard.
     */
    public function complianceReport(SopGovernanceService $sopService)
    {
        $sops = SopDocument::where('status', 'published')->get()->map(function ($sop) use ($sopService) {
            $sop->compliance_rate = $sopService->getComplianceRate($sop->id);
            $sop->unacknowledged_users = $sopService->getUnacknowledgedUsers($sop->id);

            return $sop;
        });

        return view('sop.compliance', compact('sops'));
    }
}
