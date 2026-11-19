<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use App\Services\ProfileChangeRequestService;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileChangeRequestApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ProfileChangeRequestService $changeRequestService,
    ) {}

    /**
     * Immediate self-service contact update (phone & email) without approval.
     */
    public function updateContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $updated = $this->changeRequestService->updateContact($request->user(), $validated);

        return $this->successResponse([
            'uuid' => $updated->uuid,
            'email' => $updated->email,
            'phone_number' => $updated->phone_number,
        ], __('Kontak berhasil diperbarui.'));
    }

    /**
     * List own profile change requests plus selectable targets & editable identity fields.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $requests = ProfileChangeRequest::with(['targetUser', 'reviewer'])
            ->where(function ($q) use ($user) {
                $q->where('requester_user_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (ProfileChangeRequest $item) => $this->formatRequest($item))
            ->values();

        return $this->successResponse([
            'targets' => $this->changeRequestService->availableTargetsFor($user),
            'requests' => $requests,
        ], __('Daftar pengajuan perubahan data berhasil diambil.'));
    }

    /**
     * Submit an identity change request with mandatory evidence attachment.
     */
    public function store(Request $request): JsonResponse
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
            $item = $this->changeRequestService->submit(
                $request->user(),
                $validated,
                $request->file('evidence'),
            );
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse(
            $this->formatRequest($item),
            __('Pengajuan perubahan data berhasil dikirim.'),
            201,
        );
    }

    /**
     * Cancel own pending identity change request.
     */
    public function cancel(Request $request, string $changeRequest): JsonResponse
    {
        $item = $this->resolveChangeRequest($changeRequest);

        try {
            $updated = $this->changeRequestService->cancel($request->user(), $item);
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse($this->formatRequest($updated), __('Pengajuan dibatalkan.'));
    }

    /**
     * Download/stream evidence attachment with IDOR check.
     */
    public function downloadEvidence(Request $request, string $changeRequest)
    {
        $item = $this->resolveChangeRequest($changeRequest);

        if (! $this->changeRequestService->canViewEvidence($request->user(), $item)) {
            return $this->errorResponse(__('Anda tidak berhak mengakses bukti pengajuan ini.'), 403);
        }

        if (! $item->evidence_path || ! Storage::disk('local')->exists($item->evidence_path)) {
            return $this->errorResponse(__('Berkas bukti tidak ditemukan.'), 404);
        }

        return Storage::disk('local')->response($item->evidence_path);
    }

    private function resolveChangeRequest(string $param): ProfileChangeRequest
    {
        return is_numeric($param)
            ? ProfileChangeRequest::findOrFail((int) $param)
            : ProfileChangeRequest::where('uuid', $param)->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRequest(ProfileChangeRequest $item): array
    {
        $item->loadMissing(['targetUser', 'reviewer']);

        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'target_type' => $item->target_type,
            'target_id' => $item->target_id,
            'target_name' => $item->targetUser?->name,
            'field_name' => $item->field_name,
            'field_label' => $item->field_label,
            'old_value' => $item->old_value,
            'new_value' => $item->new_value,
            'reason' => $item->reason,
            'has_evidence' => (bool) $item->evidence_path,
            'evidence_url' => $item->evidence_path
                ? url("/api/v1/profile-change-requests/{$item->uuid}/evidence")
                : null,
            'status' => $item->status,
            'reviewed_by_name' => $item->reviewer?->name,
            'reviewed_at' => $item->reviewed_at?->toIso8601String(),
            'review_notes' => $item->review_notes,
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
