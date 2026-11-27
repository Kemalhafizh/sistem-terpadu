<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenanceTicket;
use App\Services\Sarpras\AssetMaintenanceService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class AssetDamageReportApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AssetMaintenanceService $maintenanceService,
    ) {}

    /**
     * Lookup asset metadata when a school member scans its QR sticker (`GET /api/v1/assets/scan/{asset}`).
     */
    public function scan(Asset $asset): JsonResponse
    {
        $asset->loadMissing(['room', 'category']);

        return $this->successResponse([
            'uuid' => $asset->uuid,
            'asset_code' => $asset->asset_code,
            'name' => $asset->name,
            'category' => $asset->category?->name,
            'room' => $asset->room?->name,
            'location' => $asset->room?->location,
            'condition' => $asset->condition,
            'status' => $asset->status,
        ], __('Data aset berhasil dipindai.'));
    }

    /**
     * Submit an asset damage report (`POST /api/v1/assets/scan/{asset}/report`).
     */
    public function report(Request $request, Asset $asset): JsonResponse
    {
        $validated = $request->validate([
            'issue_description' => ['required', 'string', 'min:10', 'max:2000'],
            'priority' => ['required', Rule::in(AssetMaintenanceTicket::PRIORITIES)],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $uploadedPhoto = $request->file('photo') ?? $request->file('photo_evidence');

        $ticket = $this->maintenanceService->submitDamageReport(
            $asset,
            $request->user(),
            $validated,
            $uploadedPhoto
        );

        return $this->successResponse($this->formatTicket($ticket->loadMissing(['asset.room'])), __('Laporan kerusakan aset berhasil dikirim.'), 201);
    }

    /**
     * List damage reports submitted by the authenticated user (`GET /api/v1/assets/damage-reports/my`).
     */
    public function myReports(Request $request): JsonResponse
    {
        $tickets = AssetMaintenanceTicket::with(['asset.room', 'resolver'])
            ->where('reported_by', $request->user()->id)
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (AssetMaintenanceTicket $ticket) => $this->formatTicket($ticket))
            ->values();

        return $this->successResponse($tickets, __('Daftar laporan kerusakan Anda berhasil diambil.'));
    }

    /**
     * Stream private damage photo attachment (`GET /api/v1/assets/damage-reports/{ticket}/attachment`).
     */
    public function downloadAttachment(Request $request, string $ticket)
    {
        $resolvedTicket = $this->maintenanceService->findTicketByKey($ticket);

        return $this->maintenanceService->streamTicketAttachment($resolvedTicket, $request->user());
    }

    /**
     * @return array<string, mixed>
     */
    private function formatTicket(AssetMaintenanceTicket $ticket): array
    {
        $hasPhoto = ! empty($ticket->photo_evidence);
        $photoUrl = ($hasPhoto && Route::has('api.v1.assets.damage-reports.attachment'))
            ? route('api.v1.assets.damage-reports.attachment', $ticket->uuid)
            : null;

        return [
            'uuid' => $ticket->uuid,
            'asset_uuid' => $ticket->asset?->uuid,
            'asset_code' => $ticket->asset?->asset_code,
            'asset_name' => $ticket->asset?->name,
            'room' => $ticket->asset?->room?->name,
            'issue_description' => $ticket->issue_description,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'has_photo' => $hasPhoto,
            'photo_url' => $photoUrl,
            'resolution_notes' => $ticket->resolution_notes,
            'resolved_at' => $ticket->resolved_at?->toIso8601String(),
            'created_at' => $ticket->created_at?->toIso8601String(),
        ];
    }
}
