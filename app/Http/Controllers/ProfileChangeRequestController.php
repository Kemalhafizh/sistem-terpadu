<?php

namespace App\Http\Controllers;

use App\Models\ProfileChangeRequest;
use App\Services\ProfileChangeRequestService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileChangeRequestController extends Controller
{
    public function __construct(
        protected ProfileChangeRequestService $changeRequestService,
    ) {}

    /**
     * Self-service contact & verified identity change requests page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $targets = $this->changeRequestService->availableTargetsFor($user);

        $requests = ProfileChangeRequest::with(['targetUser', 'reviewer'])
            ->where(function ($q) use ($user) {
                $q->where('requester_user_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->latest('id')
            ->paginate(20);

        return view('profile.change-requests', [
            'user' => $user,
            'targets' => $targets,
            'requests' => $requests,
        ]);
    }

    /**
     * Immediate self-service update of phone number and email (no TU approval required).
     */
    public function updateContact(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $this->changeRequestService->updateContact($request->user(), $validated);

        return back()->with('success', __('Data kontak (Nomor HP / Email) berhasil diperbarui langsung.'));
    }

    /**
     * Submit an identity change request with mandatory supporting evidence (KK/Akta/Ijazah).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_type' => ['required', 'string', 'in:student,employee,user'],
            'target_id' => ['nullable', 'integer'],
            'field_name' => ['required', 'string', 'max:60'],
            'new_value' => ['required', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        try {
            $this->changeRequestService->submit(
                $request->user(),
                $validated,
                $request->file('evidence'),
            );
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return redirect()
            ->route('profile.change-requests.index')
            ->with('success', __('Pengajuan perubahan data identitas dikirim ke Tata Usaha untuk diverifikasi.'));
    }

    /**
     * Cancel own pending identity change request.
     */
    public function cancel(Request $request, string $changeRequest)
    {
        $item = $this->resolveChangeRequest($changeRequest);

        try {
            $this->changeRequestService->cancel($request->user(), $item);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __('Pengajuan perubahan data berhasil dibatalkan.'));
    }

    /**
     * Stream private evidence file with strict IDOR protection.
     */
    public function downloadEvidence(Request $request, string $changeRequest)
    {
        $item = $this->resolveChangeRequest($changeRequest);

        if (! $this->changeRequestService->canViewEvidence($request->user(), $item)) {
            abort(403, __('Anda tidak berhak melihat berkas bukti pengajuan ini.'));
        }

        if (! $item->evidence_path || ! Storage::disk('local')->exists($item->evidence_path)) {
            abort(404, __('Berkas bukti tidak ditemukan.'));
        }

        return Storage::disk('local')->response($item->evidence_path);
    }

    /**
     * TU / Admin verification queue for profile identity changes.
     */
    public function adminIndex(Request $request)
    {
        $status = (string) $request->input('status', ProfileChangeRequest::STATUS_PENDING);

        $requests = ProfileChangeRequest::with(['requester', 'targetUser', 'reviewer'])
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $pendingCount = ProfileChangeRequest::where('status', ProfileChangeRequest::STATUS_PENDING)->count();

        return view('admin.profile-change-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function approve(Request $request, string $changeRequest)
    {
        $item = $this->resolveChangeRequest($changeRequest);
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->changeRequestService->approve($request->user(), $item, $validated['review_notes'] ?? null);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __('Pengajuan disetujui. Data diperbarui dan masuk antrean Dapodik bila relevan.'));
    }

    public function reject(Request $request, string $changeRequest)
    {
        $item = $this->resolveChangeRequest($changeRequest);
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->changeRequestService->reject($request->user(), $item, $validated['review_notes']);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __('Pengajuan perubahan data ditolak.'));
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'request_ids' => ['required', 'array', 'min:1'],
            'request_ids.*' => ['integer'],
            'review_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = $this->changeRequestService->bulkApprove(
                $request->user(),
                array_map('intval', $validated['request_ids']),
                $validated['review_notes'] ?? null,
            );
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __(':approved pengajuan disetujui (:failed dilewati).', [
            'approved' => $result['approved'],
            'failed' => count($result['failed']),
        ]));
    }

    private function resolveChangeRequest(string $param): ProfileChangeRequest
    {
        return is_numeric($param)
            ? ProfileChangeRequest::findOrFail((int) $param)
            : ProfileChangeRequest::where('uuid', $param)->firstOrFail();
    }
}
