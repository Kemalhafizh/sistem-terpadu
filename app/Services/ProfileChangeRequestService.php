<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\DapodikSyncItem;
use App\Models\Employee;
use App\Models\PositionAssignment;
use App\Models\ProfileChangeRequest;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ProfileChangeRequestNotification;
use App\Services\Auth\UserIdentifierResolver;
use App\Support\Access\StudentDataAccess;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileChangeRequestService
{
    public const DEFAULT_ESCALATION_HOURS = 48;

    public function __construct(
        protected UserIdentifierResolver $identifierResolver,
    ) {}

    public function escalationHours(): int
    {
        $configured = Setting::get('profile_change_escalation_hours', self::DEFAULT_ESCALATION_HOURS);

        return max(1, (int) ($configured ?? self::DEFAULT_ESCALATION_HOURS));
    }

    /**
     * K4 Anti-macet: Phone number and email updates save immediately without TU verification.
     */
    public function updateContact(User $actor, array $data): User
    {
        $updates = [];
        $oldValues = [];

        if (array_key_exists('phone_number', $data)) {
            $rawPhone = trim((string) ($data['phone_number'] ?? ''));
            if ($rawPhone === '') {
                $updates['phone_number'] = null;
            } else {
                $normalized = $this->identifierResolver->normalizeLocalPhone($rawPhone);
                if ($normalized === null) {
                    throw ValidationException::withMessages([
                        'phone_number' => __('Format nomor HP tidak valid.'),
                    ]);
                }
                $updates['phone_number'] = $normalized;
            }
            $oldValues['phone_number'] = $actor->phone_number;
        }

        if (array_key_exists('email', $data)) {
            $rawEmail = strtolower(trim((string) ($data['email'] ?? '')));
            if ($rawEmail === '') {
                $updates['email'] = null;
                $updates['email_verified_at'] = null;
            } else {
                if (! filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                    throw ValidationException::withMessages([
                        'email' => __('Format alamat email tidak valid.'),
                    ]);
                }

                $taken = User::where('email', $rawEmail)->where('id', '!=', $actor->id)->exists();
                if ($taken) {
                    throw ValidationException::withMessages([
                        'email' => __('Alamat email sudah digunakan oleh akun lain.'),
                    ]);
                }

                $updates['email'] = $rawEmail;
                if ($rawEmail !== $actor->email) {
                    $updates['email_verified_at'] = null;
                }
            }
            $oldValues['email'] = $actor->email;
        }

        $nextEmail = array_key_exists('email', $updates) ? $updates['email'] : $actor->email;
        $nextPhone = array_key_exists('phone_number', $updates) ? $updates['phone_number'] : $actor->phone_number;
        $hasStudentOrEmployee = $actor->student()->exists() || $actor->employee()->exists();

        if ($nextEmail === null && $nextPhone === null && ! $hasStudentOrEmployee) {
            throw ValidationException::withMessages([
                'email' => __('Sisakan minimal Email atau Nomor HP agar akun tetap dapat login.'),
            ]);
        }

        if ($updates !== []) {
            DB::transaction(function () use ($actor, $updates, $oldValues) {
                $actor->forceFill($updates)->save();

                ActivityLog::create([
                    'user_id' => $actor->id,
                    'event' => 'profile.contact_updated',
                    'auditable_type' => User::class,
                    'auditable_id' => $actor->id,
                    'old_values' => $oldValues,
                    'new_values' => array_diff_key($updates, ['email_verified_at' => true]),
                ]);
            });
        }

        return $actor->fresh();
    }

    /**
     * Allowed verified identity fields and their corresponding Dapodik column label (nullable when not in Dapodik).
     *
     * @return array<string, array<string, array{label: string, dapodik_column: ?string}>>
     */
    public function allowedFieldsMap(): array
    {
        return [
            ProfileChangeRequest::TARGET_STUDENT => [
                'name' => ['label' => 'Nama Lengkap Siswa', 'dapodik_column' => 'Nama Lengkap'],
                'birth_place' => ['label' => 'Tempat Lahir', 'dapodik_column' => 'Tempat Lahir'],
                'birth_date' => ['label' => 'Tanggal Lahir', 'dapodik_column' => 'Tanggal Lahir'],
                'nik' => ['label' => 'NIK Siswa', 'dapodik_column' => 'NIK'],
                'nisn' => ['label' => 'NISN', 'dapodik_column' => 'NISN'],
                'gender' => ['label' => 'Jenis Kelamin', 'dapodik_column' => 'Jenis Kelamin'],
                'mother_name' => ['label' => 'Nama Ibu Kandung', 'dapodik_column' => 'Nama Ibu Kandung'],
                'address' => ['label' => 'Alamat (Sesuai KK)', 'dapodik_column' => 'Alamat'],
            ],
            ProfileChangeRequest::TARGET_EMPLOYEE => [
                'name' => ['label' => 'Nama Lengkap PTK', 'dapodik_column' => 'Nama PTK'],
                'nik' => ['label' => 'NIK PTK', 'dapodik_column' => 'NIK'],
                'nuptk' => ['label' => 'NUPTK', 'dapodik_column' => 'NUPTK'],
            ],
            ProfileChangeRequest::TARGET_USER => [
                'name' => ['label' => 'Nama Lengkap', 'dapodik_column' => null],
            ],
        ];
    }

    /**
     * Enumerate selectable identity targets & current values for the authenticated user (Web/Android UI helper).
     *
     * @return list<array<string, mixed>>
     */
    public function availableTargetsFor(User $actor): array
    {
        $map = $this->allowedFieldsMap();
        $targets = [];

        if ($actor->student) {
            $student = $actor->student->loadMissing(['user', 'classroom']);
            $targets[] = [
                'target_type' => ProfileChangeRequest::TARGET_STUDENT,
                'target_id' => $student->id,
                'label' => __('Data Siswa — :name', ['name' => $actor->name]),
                'current_values' => $this->extractValues(ProfileChangeRequest::TARGET_STUDENT, $student, $actor),
                'fields' => $map[ProfileChangeRequest::TARGET_STUDENT],
            ];
        }

        if (in_array($actor->role, StudentDataAccess::PARENT_ROLES, true)) {
            $children = Student::with(['user', 'classroom'])->where('parent_id', $actor->id)->orderBy('id')->get();
            foreach ($children as $child) {
                $targets[] = [
                    'target_type' => ProfileChangeRequest::TARGET_STUDENT,
                    'target_id' => $child->id,
                    'label' => __('Anak — :name (:nis)', [
                        'name' => $child->user?->name ?? '-',
                        'nis' => $child->nis,
                    ]),
                    'current_values' => $this->extractValues(ProfileChangeRequest::TARGET_STUDENT, $child, $child->user),
                    'fields' => $map[ProfileChangeRequest::TARGET_STUDENT],
                ];
            }
        }

        if ($actor->employee) {
            $employee = $actor->employee->loadMissing('user');
            $targets[] = [
                'target_type' => ProfileChangeRequest::TARGET_EMPLOYEE,
                'target_id' => $employee->id,
                'label' => __('Data PTK — :name', ['name' => $actor->name]),
                'current_values' => $this->extractValues(ProfileChangeRequest::TARGET_EMPLOYEE, $employee, $actor),
                'fields' => $map[ProfileChangeRequest::TARGET_EMPLOYEE],
            ];
        }

        if ($targets === [] || in_array($actor->role, StudentDataAccess::PARENT_ROLES, true)) {
            $targets[] = [
                'target_type' => ProfileChangeRequest::TARGET_USER,
                'target_id' => $actor->id,
                'label' => __('Profil Akun — :name', ['name' => $actor->name]),
                'current_values' => $this->extractValues(ProfileChangeRequest::TARGET_USER, $actor, $actor),
                'fields' => $map[ProfileChangeRequest::TARGET_USER],
            ];
        }

        return $targets;
    }

    /**
     * Resolve and strictly authorize target ownership for submitting an identity change request.
     *
     * @return array{target_type: string, target_id: int, target_user_id: int, model: Student|Employee|User, user: User}
     */
    public function resolveTarget(User $actor, string $targetType, ?int $targetId = null): array
    {
        if ($targetType === ProfileChangeRequest::TARGET_STUDENT) {
            $student = $targetId !== null ? Student::with('user')->find($targetId) : $actor->student?->load('user');
            if (! $student) {
                throw ValidationException::withMessages([
                    'target_id' => __('Data siswa tidak ditemukan.'),
                ]);
            }

            $isOwner = (int) $student->user_id === (int) $actor->id || (int) $student->parent_id === (int) $actor->id;
            if (! $isOwner) {
                throw new AuthorizationException(__('Anda hanya dapat mengajukan perubahan data untuk diri sendiri atau anak Anda.'));
            }

            return [
                'target_type' => ProfileChangeRequest::TARGET_STUDENT,
                'target_id' => $student->id,
                'target_user_id' => $student->user_id,
                'model' => $student,
                'user' => $student->user,
            ];
        }

        if ($targetType === ProfileChangeRequest::TARGET_EMPLOYEE) {
            $employee = $targetId !== null ? Employee::with('user')->find($targetId) : $actor->employee?->load('user');
            if (! $employee) {
                throw ValidationException::withMessages([
                    'target_id' => __('Data pegawai/PTK tidak ditemukan.'),
                ]);
            }

            if ((int) $employee->user_id !== (int) $actor->id) {
                throw new AuthorizationException(__('Anda hanya dapat mengajukan perubahan data kepegawaian milik Anda sendiri.'));
            }

            return [
                'target_type' => ProfileChangeRequest::TARGET_EMPLOYEE,
                'target_id' => $employee->id,
                'target_user_id' => $employee->user_id,
                'model' => $employee,
                'user' => $employee->user,
            ];
        }

        if ($targetType === ProfileChangeRequest::TARGET_USER) {
            $targetUser = $targetId !== null ? User::find($targetId) : $actor;
            if (! $targetUser) {
                throw ValidationException::withMessages([
                    'target_id' => __('Akun pengguna tidak ditemukan.'),
                ]);
            }

            if ((int) $targetUser->id !== (int) $actor->id) {
                throw new AuthorizationException(__('Anda hanya dapat mengajukan perubahan profil milik Anda sendiri.'));
            }

            return [
                'target_type' => ProfileChangeRequest::TARGET_USER,
                'target_id' => $targetUser->id,
                'target_user_id' => $targetUser->id,
                'model' => $targetUser,
                'user' => $targetUser,
            ];
        }

        throw ValidationException::withMessages([
            'target_type' => __('Tipe target perubahan data tidak valid.'),
        ]);
    }

    /**
     * Submit an identity change request with mandatory supporting evidence stored on private local disk.
     */
    public function submit(User $actor, array $data, ?UploadedFile $evidence = null): ProfileChangeRequest
    {
        $targetType = (string) ($data['target_type'] ?? $this->inferDefaultTargetType($actor));
        $targetId = isset($data['target_id']) && $data['target_id'] !== '' ? (int) $data['target_id'] : null;

        $resolved = $this->resolveTarget($actor, $targetType, $targetId);
        $allowedMap = $this->allowedFieldsMap()[$resolved['target_type']] ?? [];
        $fieldName = (string) ($data['field_name'] ?? '');

        if (! array_key_exists($fieldName, $allowedMap)) {
            throw ValidationException::withMessages([
                'field_name' => __('Kolom identitas yang dipilih tidak valid untuk diajukan.'),
            ]);
        }

        if ($evidence === null && empty($data['evidence_path'])) {
            throw ValidationException::withMessages([
                'evidence' => __('Dokumen bukti pendukung (foto KK, Akta Kelahiran, atau Ijazah) wajib diunggah.'),
            ]);
        }

        $newValue = $this->validateFieldValue($resolved['target_type'], $resolved['model'], $fieldName, (string) ($data['new_value'] ?? ''));
        $currentValues = $this->extractValues($resolved['target_type'], $resolved['model'], $resolved['user']);
        $oldValue = $currentValues[$fieldName] ?? null;

        if ((string) ($oldValue ?? '') === $newValue) {
            throw ValidationException::withMessages([
                'new_value' => __('Nilai baru harus berbeda dari data yang sudah tercatat.'),
            ]);
        }

        $duplicatePending = ProfileChangeRequest::query()
            ->where('target_type', $resolved['target_type'])
            ->where('target_id', $resolved['target_id'])
            ->where('field_name', $fieldName)
            ->where('status', ProfileChangeRequest::STATUS_PENDING)
            ->exists();

        if ($duplicatePending) {
            throw ValidationException::withMessages([
                'field_name' => __('Sudah ada pengajuan perubahan yang menunggu verifikasi untuk kolom ini.'),
            ]);
        }

        $evidencePath = $evidence?->store("profile-change-evidence/{$resolved['target_type']}/{$resolved['target_id']}", 'local');
        if ($evidencePath === false) {
            $evidencePath = null;
        }

        $fieldMeta = $allowedMap[$fieldName];

        $changeRequest = DB::transaction(function () use ($actor, $resolved, $fieldName, $fieldMeta, $oldValue, $newValue, $data, $evidencePath) {
            $changeRequest = ProfileChangeRequest::create([
                'requester_user_id' => $actor->id,
                'target_user_id' => $resolved['target_user_id'],
                'target_type' => $resolved['target_type'],
                'target_id' => $resolved['target_id'],
                'field_name' => $fieldName,
                'field_label' => $fieldMeta['label'],
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'reason' => filled($data['reason'] ?? null) ? trim((string) $data['reason']) : null,
                'evidence_path' => $evidencePath,
                'status' => ProfileChangeRequest::STATUS_PENDING,
            ]);

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'profile_change.submitted',
                'auditable_type' => ProfileChangeRequest::class,
                'auditable_id' => $changeRequest->id,
                'new_values' => [
                    'target_type' => $resolved['target_type'],
                    'target_id' => $resolved['target_id'],
                    'field_name' => $fieldName,
                ],
            ]);

            return $changeRequest;
        });

        foreach ($this->resolveActiveRoleHolders(['staf_tu', 'admin']) as $reviewer) {
            if ((int) $reviewer->id === (int) $actor->id) {
                continue;
            }
            $this->safeNotify($reviewer, new ProfileChangeRequestNotification($changeRequest, 'submitted'));
        }

        return $changeRequest->fresh(['requester', 'targetUser']);
    }

    public function hasReviewerRole(User $actor): bool
    {
        if (($actor->account_status ?? 'aktif') === 'nonaktif') {
            return false;
        }

        return $actor->role === 'superadmin' || array_intersect($actor->effectiveRoles(), ['admin', 'staf_tu']) !== [];
    }

    public function canReview(User $actor, ProfileChangeRequest $changeRequest): bool
    {
        if (! $this->hasReviewerRole($actor)) {
            return false;
        }

        if ((int) $actor->id === (int) $changeRequest->requester_user_id
            || (int) $actor->id === (int) $changeRequest->target_user_id) {
            return false;
        }

        return true;
    }

    public function canViewEvidence(User $actor, ProfileChangeRequest $changeRequest): bool
    {
        if (($actor->account_status ?? 'aktif') === 'nonaktif') {
            return false;
        }

        if ((int) $actor->id === (int) $changeRequest->requester_user_id
            || (int) $actor->id === (int) $changeRequest->target_user_id) {
            return true;
        }

        if ($actor->role === 'superadmin') {
            return true;
        }

        return array_intersect($actor->effectiveRoles(), ['admin', 'staf_tu', 'kepala_sekolah', 'kepsek']) !== [];
    }

    public function approve(User $actor, ProfileChangeRequest $changeRequest, ?string $notes = null): ProfileChangeRequest
    {
        $this->assertCanReview($actor, $changeRequest);

        $updated = DB::transaction(function () use ($actor, $changeRequest, $notes) {
            /** @var ProfileChangeRequest $locked */
            $locked = ProfileChangeRequest::whereKey($changeRequest->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ProfileChangeRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => __('Hanya pengajuan berstatus menunggu yang dapat disetujui.'),
                ]);
            }

            $appliedOldValue = $this->applyFieldUpdate($locked);

            $locked->update([
                'status' => ProfileChangeRequest::STATUS_APPROVED,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'review_notes' => filled($notes) ? trim((string) $notes) : null,
            ]);

            $fieldMeta = $this->allowedFieldsMap()[$locked->target_type][$locked->field_name] ?? null;
            if ($fieldMeta !== null && $fieldMeta['dapodik_column'] !== null && in_array($locked->target_type, [ProfileChangeRequest::TARGET_STUDENT, ProfileChangeRequest::TARGET_EMPLOYEE], true)) {
                DapodikSyncItem::create([
                    'entity_type' => $locked->target_type === ProfileChangeRequest::TARGET_STUDENT ? DapodikSyncItem::ENTITY_STUDENT : DapodikSyncItem::ENTITY_EMPLOYEE,
                    'entity_id' => $locked->target_id,
                    'profile_change_request_id' => $locked->id,
                    'field_name' => $locked->field_name,
                    'dapodik_column_label' => $fieldMeta['dapodik_column'],
                    'old_value' => $appliedOldValue,
                    'new_value' => $locked->new_value,
                    'status' => DapodikSyncItem::STATUS_PENDING,
                ]);
            }

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'profile_change.approved',
                'auditable_type' => ProfileChangeRequest::class,
                'auditable_id' => $locked->id,
                'old_values' => ['value' => $appliedOldValue, 'status' => ProfileChangeRequest::STATUS_PENDING],
                'new_values' => ['value' => $locked->new_value, 'status' => ProfileChangeRequest::STATUS_APPROVED],
            ]);

            return $locked;
        });

        $updated->loadMissing('requester');
        if ($updated->requester) {
            $this->safeNotify($updated->requester, new ProfileChangeRequestNotification($updated, 'approved'));
        }

        return $updated->fresh(['requester', 'targetUser', 'reviewer', 'dapodikSyncItems']);
    }

    public function reject(User $actor, ProfileChangeRequest $changeRequest, string $reason): ProfileChangeRequest
    {
        $this->assertCanReview($actor, $changeRequest);

        $cleanReason = trim($reason);
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'review_notes' => __('Alasan penolakan wajib diisi.'),
            ]);
        }

        $updated = DB::transaction(function () use ($actor, $changeRequest, $cleanReason) {
            /** @var ProfileChangeRequest $locked */
            $locked = ProfileChangeRequest::whereKey($changeRequest->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ProfileChangeRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => __('Hanya pengajuan berstatus menunggu yang dapat ditolak.'),
                ]);
            }

            $locked->update([
                'status' => ProfileChangeRequest::STATUS_REJECTED,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'review_notes' => $cleanReason,
            ]);

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'profile_change.rejected',
                'auditable_type' => ProfileChangeRequest::class,
                'auditable_id' => $locked->id,
                'old_values' => ['status' => ProfileChangeRequest::STATUS_PENDING],
                'new_values' => ['status' => ProfileChangeRequest::STATUS_REJECTED, 'review_notes' => $cleanReason],
            ]);

            return $locked;
        });

        $updated->loadMissing('requester');
        if ($updated->requester) {
            $this->safeNotify($updated->requester, new ProfileChangeRequestNotification($updated, 'rejected'));
        }

        return $updated->fresh(['requester', 'targetUser', 'reviewer']);
    }

    /**
     * @param  list<int>  $requestIds
     * @return array{approved: int, failed: list<array{id: int, reason: string}>}
     */
    public function bulkApprove(User $actor, array $requestIds, ?string $notes = null): array
    {
        if (! $this->hasReviewerRole($actor)) {
            throw new AuthorizationException(__('Anda tidak berwenang memverifikasi pengajuan perubahan data.'));
        }

        $approved = 0;
        $failed = [];

        $requests = ProfileChangeRequest::whereIn('id', $requestIds)->orderBy('id')->get();
        foreach ($requests as $changeRequest) {
            try {
                $this->approve($actor, $changeRequest, $notes);
                $approved++;
            } catch (Throwable $e) {
                $failed[] = [
                    'id' => $changeRequest->id,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'approved' => $approved,
            'failed' => $failed,
        ];
    }

    public function cancel(User $actor, ProfileChangeRequest $changeRequest): ProfileChangeRequest
    {
        if ((int) $actor->id !== (int) $changeRequest->requester_user_id) {
            throw new AuthorizationException(__('Anda hanya dapat membatalkan pengajuan milik Anda sendiri.'));
        }

        $updated = DB::transaction(function () use ($actor, $changeRequest) {
            /** @var ProfileChangeRequest $locked */
            $locked = ProfileChangeRequest::whereKey($changeRequest->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ProfileChangeRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => __('Pengajuan yang sudah diputuskan tidak dapat dibatalkan.'),
                ]);
            }

            $locked->update([
                'status' => ProfileChangeRequest::STATUS_CANCELLED,
            ]);

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'profile_change.cancelled',
                'auditable_type' => ProfileChangeRequest::class,
                'auditable_id' => $locked->id,
                'old_values' => ['status' => ProfileChangeRequest::STATUS_PENDING],
                'new_values' => ['status' => ProfileChangeRequest::STATUS_CANCELLED],
            ]);

            return $locked;
        });

        return $updated->fresh(['requester', 'targetUser']);
    }

    /**
     * Scheduler task: escalate pending requests older than threshold to Admin / Kepala Sekolah.
     */
    public function escalateOverdue(?Carbon $now = null): int
    {
        $now ??= now();
        $cutoff = $now->copy()->subHours($this->escalationHours());

        $overdue = ProfileChangeRequest::query()
            ->where('status', ProfileChangeRequest::STATUS_PENDING)
            ->whereNull('escalated_at')
            ->where('created_at', '<=', $cutoff)
            ->get();

        if ($overdue->isEmpty()) {
            return 0;
        }

        $recipients = $this->resolveActiveRoleHolders(['kepala_sekolah', 'kepsek', 'admin']);
        $escalated = 0;

        foreach ($overdue as $requestItem) {
            $requestItem->update(['escalated_at' => $now]);

            foreach ($recipients as $recipient) {
                if ((int) $recipient->id === (int) $requestItem->requester_user_id) {
                    continue;
                }
                $this->safeNotify($recipient, new ProfileChangeRequestNotification($requestItem, 'escalated'));
            }

            $escalated++;
        }

        return $escalated;
    }

    /**
     * @return Collection<int, User>
     */
    private function resolveActiveRoleHolders(array $roles): Collection
    {
        $byBaseRole = User::query()
            ->whereIn('role', $roles)
            ->where(fn ($q) => $q->whereNull('account_status')->orWhere('account_status', '!=', 'nonaktif'))
            ->get();

        $byAssignment = collect();
        if (Schema::hasTable('position_assignments') && Schema::hasTable('positions')) {
            $userIds = PositionAssignment::query()
                ->activeOn()
                ->whereHas('position', fn ($q) => $q->whereIn('code', $roles)->where('is_active', true))
                ->pluck('user_id');

            if ($userIds->isNotEmpty()) {
                $byAssignment = User::query()
                    ->whereIn('id', $userIds)
                    ->where(fn ($q) => $q->whereNull('account_status')->orWhere('account_status', '!=', 'nonaktif'))
                    ->get();
            }
        }

        return $byBaseRole->merge($byAssignment)->unique('id')->values();
    }

    private function inferDefaultTargetType(User $actor): string
    {
        if ($actor->student) {
            return ProfileChangeRequest::TARGET_STUDENT;
        }
        if ($actor->employee) {
            return ProfileChangeRequest::TARGET_EMPLOYEE;
        }

        return ProfileChangeRequest::TARGET_USER;
    }

    private function extractValues(string $targetType, Student|Employee|User $model, ?User $user): array
    {
        if ($targetType === ProfileChangeRequest::TARGET_STUDENT && $model instanceof Student) {
            $birthDate = $model->birth_date;

            return [
                'name' => $user?->name,
                'birth_place' => $model->birth_place,
                'birth_date' => $birthDate instanceof CarbonInterface ? $birthDate->toDateString() : ($birthDate ?: null),
                'nik' => $model->nik,
                'nisn' => $model->nisn,
                'gender' => $model->gender,
                'mother_name' => $model->mother_name,
                'address' => $model->address,
            ];
        }

        if ($targetType === ProfileChangeRequest::TARGET_EMPLOYEE && $model instanceof Employee) {
            return [
                'name' => $user?->name,
                'nik' => $model->nik,
                'nuptk' => $model->nuptk,
            ];
        }

        return [
            'name' => $user?->name,
        ];
    }

    private function validateFieldValue(string $targetType, Student|Employee|User $model, string $fieldName, string $rawValue): string
    {
        $clean = trim($rawValue);
        if ($clean === '') {
            throw ValidationException::withMessages([
                'new_value' => __('Nilai baru wajib diisi.'),
            ]);
        }

        if ($fieldName === 'nisn') {
            if (! preg_match('/^\d{10}$/', $clean)) {
                throw ValidationException::withMessages([
                    'new_value' => __('NISN harus terdiri dari tepat 10 digit angka.'),
                ]);
            }
            if ($model instanceof Student && Student::where('nisn', $clean)->where('id', '!=', $model->id)->exists()) {
                throw ValidationException::withMessages([
                    'new_value' => __('NISN tersebut sudah tercatat pada siswa lain.'),
                ]);
            }
        }

        if ($fieldName === 'nik' && ! preg_match('/^\d{16}$/', $clean)) {
            throw ValidationException::withMessages([
                'new_value' => __('NIK harus terdiri dari tepat 16 digit angka.'),
            ]);
        }

        if ($fieldName === 'gender') {
            $upper = strtoupper($clean);
            if ($upper === 'LAKI-LAKI') {
                $upper = 'L';
            } elseif ($upper === 'PEREMPUAN') {
                $upper = 'P';
            }

            if (! in_array($upper, ['L', 'P'], true)) {
                throw ValidationException::withMessages([
                    'new_value' => __('Jenis kelamin harus diisi L atau P.'),
                ]);
            }

            return $upper;
        }

        if ($fieldName === 'birth_date') {
            try {
                return Carbon::parse($clean)->toDateString();
            } catch (Throwable) {
                throw ValidationException::withMessages([
                    'new_value' => __('Format tanggal lahir tidak valid.'),
                ]);
            }
        }

        if ($fieldName === 'nuptk' && $targetType === ProfileChangeRequest::TARGET_EMPLOYEE && $model instanceof Employee) {
            if (Employee::where('nuptk', $clean)->where('id', '!=', $model->id)->exists()) {
                throw ValidationException::withMessages([
                    'new_value' => __('NUPTK tersebut sudah digunakan pegawai lain.'),
                ]);
            }
        }

        return $clean;
    }

    private function applyFieldUpdate(ProfileChangeRequest $changeRequest): ?string
    {
        $fieldName = $changeRequest->field_name;
        $newValue = $changeRequest->new_value;

        if ($changeRequest->target_type === ProfileChangeRequest::TARGET_STUDENT) {
            $student = Student::with('user')->findOrFail($changeRequest->target_id);
            if ($fieldName === 'name') {
                $old = $student->user?->name;
                $student->user?->update(['name' => $newValue]);

                return $old;
            }

            if ($fieldName === 'nisn' && Student::where('nisn', $newValue)->where('id', '!=', $student->id)->exists()) {
                throw ValidationException::withMessages([
                    'new_value' => __('NISN tersebut sudah tercatat pada siswa lain.'),
                ]);
            }

            $old = $student->{$fieldName};
            $oldStr = $old instanceof CarbonInterface ? $old->toDateString() : ($old !== null ? (string) $old : null);
            $student->update([$fieldName => $newValue]);

            return $oldStr;
        }

        if ($changeRequest->target_type === ProfileChangeRequest::TARGET_EMPLOYEE) {
            $employee = Employee::with('user')->findOrFail($changeRequest->target_id);
            if ($fieldName === 'name') {
                $old = $employee->user?->name;
                $employee->user?->update(['name' => $newValue]);

                return $old;
            }

            if ($fieldName === 'nuptk' && Employee::where('nuptk', $newValue)->where('id', '!=', $employee->id)->exists()) {
                throw ValidationException::withMessages([
                    'new_value' => __('NUPTK tersebut sudah digunakan pegawai lain.'),
                ]);
            }

            $old = $employee->{$fieldName};
            $employee->update([$fieldName => $newValue]);

            return $old !== null ? (string) $old : null;
        }

        $targetUser = User::findOrFail($changeRequest->target_id);
        $old = $targetUser->name;
        $targetUser->update(['name' => $newValue]);

        return $old;
    }

    private function assertCanReview(User $actor, ProfileChangeRequest $changeRequest): void
    {
        if ((int) $actor->id === (int) $changeRequest->requester_user_id
            || (int) $actor->id === (int) $changeRequest->target_user_id) {
            throw new AuthorizationException(__('Pengaju perubahan tidak dapat memverifikasi atau menyetujui pengajuannya sendiri.'));
        }

        if (! $this->canReview($actor, $changeRequest)) {
            throw new AuthorizationException(__('Anda tidak berwenang memverifikasi pengajuan perubahan data ini.'));
        }
    }

    private function safeNotify(User $recipient, ProfileChangeRequestNotification $notification): void
    {
        try {
            $recipient->notify($notification);
        } catch (Throwable $e) {
            Log::warning('ProfileChangeRequestService: notification failed', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
