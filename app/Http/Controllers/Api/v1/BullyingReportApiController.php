<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\BullyingReport;
use App\Services\Crisis\AntiBullyingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * FASE 119: bullying reports from the app ("Lapor Perundungan", Android FASE 78).
 * Anyone signed in may file one; only BullyingReport::READER_ROLES may read them.
 */
class BullyingReportApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private AntiBullyingService $service) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:5000',
            'incident_date' => 'nullable|date|before_or_equal:today',
            'location' => 'nullable|string|max:255',
            'type' => ['nullable', Rule::in(BullyingReport::TYPES)],
            'is_anonymous' => 'nullable|boolean',
        ]);

        // Anonymous unless the reporter says otherwise; an anonymous report keeps no trace of who sent it.
        $anonymous = $validated['is_anonymous'] ?? true;
        $report = $this->service->submitReport([
            ...$validated,
            'is_anonymous' => $anonymous,
            'reporter_id' => $anonymous ? null : $request->user()->id,
        ]);

        return $this->successResponse(
            $this->present($report, false),
            __('Laporan perundungan terkirim. Guru BK akan menindaklanjutinya.'),
            201,
        );
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', Rule::in(BullyingReport::STATUSES)]]);

        $reports = BullyingReport::with('reporter:id,name')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => $reports->getCollection()->map(fn (BullyingReport $report) => $this->present($report, true))->values(),
            'meta' => ['current_page' => $reports->currentPage(), 'last_page' => $reports->lastPage(), 'total' => $reports->total()],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $report = BullyingReport::with('reporter:id,name')->find($id);
        if (! $report) {
            return $this->errorResponse(__('Laporan tidak ditemukan.'), 404);
        }

        return $this->successResponse($this->present($report, true));
    }

    /** @return array<string, mixed> */
    private function present(BullyingReport $report, bool $forCounsellor): array
    {
        $data = [
            'id' => $report->id,
            'ticket_number' => $report->ticket_number,
            'description' => $report->description,
            'type' => $report->type ? __('bullying.types.'.$report->type) : null,
            'type_code' => $report->type,
            'location' => $report->location,
            'incident_date' => $report->incident_date?->toDateString(),
            'is_anonymous' => $report->is_anonymous,
            'status' => __('bullying.statuses.'.$report->status),
            'status_code' => $report->status,
            'created_at' => $report->created_at?->toIso8601String(),
        ];

        if ($forCounsellor) {
            $data += [
                'severity' => $report->severity ? __('bullying.severities.'.$report->severity) : null,
                'severity_code' => $report->severity,
                'reporter' => $report->is_anonymous ? null : $report->reporter?->name,
            ];
        }

        return $data;
    }
}
