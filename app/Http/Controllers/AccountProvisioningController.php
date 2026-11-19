<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Services\Auth\AccountProvisioningService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AccountProvisioningController extends Controller
{
    public function __construct(
        protected AccountProvisioningService $provisioningService
    ) {}

    /**
     * Classroom student & parent accounts overview for TU, Admin, Kepsek, and Homeroom Teachers.
     */
    public function studentAccounts(Request $request)
    {
        $actor = $request->user();
        $roles = $actor->effectiveRoles();
        $isOperator = $actor->role === 'superadmin' || array_intersect($roles, ['admin', 'staf_tu', 'kepala_sekolah', 'kepsek']) !== [];

        $currentYear = AcademicYear::current();

        $classroomsQuery = Classroom::with('teacher')->orderBy('name');
        if (! $isOperator) {
            $classroomsQuery->where('teacher_id', $actor->id)
                ->when($currentYear, fn ($q) => $q->where('academic_year', $currentYear->year));
        }

        $classrooms = $classroomsQuery->get();

        $selectedClassroom = null;
        if ($request->filled('classroom_id')) {
            $selectedParam = (string) $request->input('classroom_id');
            $selectedClassroom = $classrooms->first(fn (Classroom $c) => (string) $c->id === $selectedParam || $c->uuid === $selectedParam);
        }

        if (! $selectedClassroom) {
            $selectedClassroom = $classrooms->first();
        }

        $students = collect();
        if ($selectedClassroom) {
            $students = Student::with(['user', 'parent'])
                ->where('classroom_id', $selectedClassroom->id)
                ->orderBy('nis')
                ->get();
        }

        $canBulkProvision = $actor->role === 'superadmin' || array_intersect($roles, ['admin', 'staf_tu']) !== [];

        return view('users.student-accounts', compact(
            'classrooms',
            'selectedClassroom',
            'students',
            'canBulkProvision'
        ));
    }

    /**
     * Reset a target user's password to a random unambiguous temporary password.
     */
    public function resetPassword(Request $request, string $user)
    {
        $target = is_numeric($user)
            ? User::findOrFail((int) $user)
            : User::where('uuid', $user)->firstOrFail();

        try {
            $result = $this->provisioningService->resetPasswordByActor($request->user(), $target);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\DomainException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        $loginIdentifier = $target->student?->nisn
            ?: ($target->student?->nis
            ?: ($target->phone_number
            ?: ($target->email ?: '-')));

        $payload = [
            'user_id' => $result['user']->id,
            'user_uuid' => $result['user']->uuid,
            'name' => $result['user']->name,
            'role' => $result['user']->role,
            'login_identifier' => $loginIdentifier,
            'temp_password' => $result['temp_password'],
            'must_change_password' => true,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Kata sandi sementara baru berhasil diterbitkan.'),
                'data' => $payload,
            ]);
        }

        return back()
            ->with('success', __('Kata sandi sementara baru untuk :name berhasil dibuat.', ['name' => $target->name]))
            ->with('reset_credentials', $payload);
    }

    /**
     * Bulk provision missing shared parent accounts from SPMB registrations.
     */
    public function bulkProvisionParents(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
        ]);

        $classroomId = isset($validated['classroom_id']) ? (int) $validated['classroom_id'] : null;
        $results = $this->provisioningService->bulkProvisionMissingParents($classroomId);

        $createdRows = array_values(array_filter($results, fn (array $r) => ($r['created'] ?? false) && ! empty($r['temp_password'])));
        $token = null;

        if ($createdRows !== []) {
            $credentials = array_map(fn (array $row) => [
                'role_label' => 'Orang Tua / Wali',
                'name' => $row['parent_name'],
                'identifier' => $row['parent_phone'] ?: '-',
                'temp_password' => $row['temp_password'],
                'notes' => 'Wali dari '.($row['student_name'] ?? '-').($row['nis'] ? " ({$row['nis']})" : ''),
            ], $createdRows);

            $token = self::storeBatchCredentials($request->user()->id, 'Akun Wali Murid (Provisioning Massal)', $credentials);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Provisioning akun wali selesai diproses.'),
                'data' => [
                    'linked_count' => count($results),
                    'created_count' => count($createdRows),
                    'batch_credentials_token' => $token,
                ],
            ]);
        }

        return back()
            ->with('success', __('Provisioning akun wali selesai: :linked tertaut (:created akun baru dibuat).', [
                'linked' => count($results),
                'created' => count($createdRows),
            ]))
            ->with('batch_credentials_token', $token);
    }

    /**
     * Generate & download printable DomPDF credential sheet for a classroom.
     */
    public function classroomCredentialSheet(Request $request, string $classroom)
    {
        $targetClassroom = is_numeric($classroom)
            ? Classroom::findOrFail((int) $classroom)
            : Classroom::where('uuid', $classroom)->firstOrFail();

        try {
            $sheetData = $this->provisioningService->issueClassroomCredentialSheet(
                $request->user(),
                $targetClassroom,
                ! $request->boolean('reset_all')
            );
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        $safeName = Str::slug((string) $targetClassroom->name, '_');

        return $this->provisioningService->buildClassroomCredentialPdf($sheetData)
            ->download("lembar_akun_rombel_{$safeName}.pdf");
    }

    /**
     * Download PDF credential sheet from an import/provisioning batch.
     */
    public function downloadBatchCredentials(Request $request, string $token)
    {
        $encrypted = Cache::get(self::batchCacheKey($token));
        if (! is_string($encrypted)) {
            abort(404, __('Lembar kredensial batch tidak ditemukan atau sudah kedaluwarsa.'));
        }

        try {
            /** @var array{actor_id: int, label: string, credentials: list<array<string, mixed>>} $payload */
            $payload = Crypt::decrypt($encrypted);
        } catch (\Throwable) {
            abort(404);
        }

        $actor = $request->user();
        $roles = $actor->effectiveRoles();
        $isOperator = $actor->role === 'superadmin' || array_intersect($roles, ['admin', 'staf_tu']) !== [];
        if (! $isOperator && (int) ($payload['actor_id'] ?? 0) !== (int) $actor->id) {
            abort(403);
        }

        $label = (string) ($payload['label'] ?? 'Impor Massal');
        $safeLabel = Str::slug($label, '_');

        return $this->provisioningService->buildImportBatchCredentialPdf(
            $payload['credentials'] ?? [],
            $label
        )->download("lembar_kredensial_{$safeLabel}.pdf");
    }

    /**
     * Encrypt and cache temporary batch credentials for 60 minutes without persisting raw passwords in DB.
     *
     * @param  list<array{role_label: string, name: string, identifier: string, temp_password: string, notes?: string}>  $credentials
     */
    public static function storeBatchCredentials(int $actorId, string $label, array $credentials): string
    {
        $token = (string) Str::uuid();
        Cache::put(
            self::batchCacheKey($token),
            Crypt::encrypt([
                'actor_id' => $actorId,
                'label' => $label,
                'credentials' => $credentials,
            ]),
            now()->addMinutes(60)
        );

        return $token;
    }

    private static function batchCacheKey(string $token): string
    {
        return "k5_batch_creds:{$token}";
    }
}
