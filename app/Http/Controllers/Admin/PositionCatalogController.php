<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\PositionPermission;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PositionCatalogController extends Controller
{
    public function index(): View
    {
        $positions = Position::query()
            ->with(['permissions', 'assignments' => fn ($q) => $q->activeOn()->with('user')])
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $assignments = PositionAssignment::query()
            ->with(['position', 'user', 'assigner'])
            ->orderByDesc('starts_on')
            ->paginate(20);

        $permissionCatalog = config('permissions.catalog', []);
        $accountTypes = config('permissions.account_types', []);

        $eligibleUsers = User::query()
            ->where(function ($q) {
                $q->whereIn('account_type', ['guru', 'karyawan'])
                    ->orWhereIn('role', ['guru', 'teacher', 'staf_tu', 'bk', 'wali_kelas', 'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'admin']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'account_type']);

        return view('admin.positions.index', compact(
            'positions',
            'assignments',
            'permissionCatalog',
            'accountTypes',
            'eligibleUsers',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $catalog = config('permissions.catalog', []);
        $validPermissions = array_keys($catalog);
        $validAccountTypes = array_keys(config('permissions.account_types', []));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/', Rule::unique('positions', 'code')],
            'description' => ['nullable', 'string', 'max:1000'],
            'allowed_account_types' => ['required', 'array', 'min:1'],
            'allowed_account_types.*' => ['string', Rule::in($validAccountTypes)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($validPermissions)],
        ]);

        $requestedPermissions = array_values(array_unique($validated['permissions'] ?? []));
        $this->authorizeSensitivePermissionChanges($request->user(), [], $requestedPermissions);

        $baseCode = $validated['code'] ?? Str::slug($validated['name'], '_');
        $code = $baseCode;
        $suffix = 2;
        while (Position::where('code', $code)->exists()) {
            $code = $baseCode.'_'.$suffix;
            $suffix++;
        }

        DB::transaction(function () use ($validated, $code, $requestedPermissions) {
            $position = Position::create([
                'code' => $code,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'allowed_account_types' => array_values($validated['allowed_account_types']),
                'is_system' => false,
                'is_active' => true,
            ]);

            foreach ($requestedPermissions as $perm) {
                PositionPermission::create([
                    'position_id' => $position->id,
                    'permission' => $perm,
                ]);
            }
        });

        return redirect()->route('admin.positions.index')
            ->with('success', __('Jabatan baru berhasil disimpan ke katalog.'));
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $catalog = config('permissions.catalog', []);
        $validPermissions = array_keys($catalog);
        $validAccountTypes = array_keys(config('permissions.account_types', []));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'allowed_account_types' => ['required', 'array', 'min:1'],
            'allowed_account_types.*' => ['string', Rule::in($validAccountTypes)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($validPermissions)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $requestedPermissions = array_values(array_unique($validated['permissions'] ?? []));
        $currentPermissions = $position->permissionKeys();

        // Guardrail 1: Kepala Sekolah cannot lose 'pimpinan.mengesahkan' or be deactivated
        if ($position->code === 'kepala_sekolah') {
            if (! in_array('pimpinan.mengesahkan', $requestedPermissions, true)) {
                throw ValidationException::withMessages([
                    'permissions' => __('Jabatan Kepala Sekolah wajib memiliki hak pengesahan resmi kepala sekolah (pimpinan.mengesahkan).'),
                ]);
            }
            if (array_key_exists('is_active', $validated) && ! $validated['is_active']) {
                throw ValidationException::withMessages([
                    'is_active' => __('Jabatan sistem Kepala Sekolah tidak dapat dinonaktifkan.'),
                ]);
            }
        }

        // Guardrail 2: Sensitive permissions can only be added/removed by superadmin
        $this->authorizeSensitivePermissionChanges($request->user(), $currentPermissions, $requestedPermissions);

        // Guardrail 3: Position used in active workflow definition steps cannot be deactivated
        $nextActive = array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : $position->is_active;
        if ($position->is_active && ! $nextActive && $this->isUsedInActiveWorkflow($position->code)) {
            throw ValidationException::withMessages([
                'is_active' => __('Jabatan ini masih dipakai pada langkah persetujuan alur kerja aktif dan tidak dapat dinonaktifkan sebelum langkah tersebut dialihkan.'),
            ]);
        }

        $activeHolderCount = $position->assignments()->activeOn()->count();

        DB::transaction(function () use ($position, $validated, $requestedPermissions, $nextActive) {
            $position->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'allowed_account_types' => array_values($validated['allowed_account_types']),
                'is_active' => $nextActive,
            ]);

            $position->permissions()->delete();
            foreach ($requestedPermissions as $perm) {
                PositionPermission::create([
                    'position_id' => $position->id,
                    'permission' => $perm,
                ]);
            }
        });

        return redirect()->route('admin.positions.index')
            ->with('success', __('Jabatan :name berhasil diperbarui (:count pemegang aktif terdampak).', [
                'name' => $position->name,
                'count' => $activeHolderCount,
            ]));
    }

    public function destroy(Position $position): RedirectResponse
    {
        if ($position->is_system || $position->code === 'kepala_sekolah') {
            throw ValidationException::withMessages([
                'position' => __('Jabatan sistem tidak dapat dihapus.'),
            ]);
        }

        if ($position->assignments()->exists()) {
            throw ValidationException::withMessages([
                'position' => __('Jabatan yang pernah memiliki pemegang hanya dapat dinonaktifkan agar riwayat persetujuan tetap terbaca.'),
            ]);
        }

        if ($this->isUsedInActiveWorkflow($position->code)) {
            throw ValidationException::withMessages([
                'position' => __('Jabatan yang dipakai pada alur persetujuan aktif tidak dapat dihapus.'),
            ]);
        }

        $position->delete();

        return redirect()->route('admin.positions.index')
            ->with('success', __('Jabatan berhasil dihapus dari katalog.'));
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'position_id' => ['required', 'exists:positions,id'],
            'user_id' => ['required', 'exists:users,id'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'decree_number' => ['nullable', 'string', 'max:120'],
            'is_acting' => ['sometimes', 'boolean'],
        ]);

        $position = Position::findOrFail($validated['position_id']);
        if (! $position->is_active) {
            throw ValidationException::withMessages([
                'position_id' => __('Jabatan yang nonaktif tidak dapat diberikan kepada pejabat baru.'),
            ]);
        }

        $targetUser = User::findOrFail($validated['user_id']);
        $accountType = $targetUser->resolvedAccountType();
        if ($accountType === null || ! $position->allowsAccountType($accountType)) {
            throw ValidationException::withMessages([
                'user_id' => __('Jenis akun pengguna (:type) tidak diizinkan memegang jabatan :position.', [
                    'type' => $accountType ?? 'tidak diketahui',
                    'position' => $position->name,
                ]),
            ]);
        }

        PositionAssignment::create([
            'position_id' => $position->id,
            'user_id' => $targetUser->id,
            'starts_on' => $validated['starts_on'],
            'ends_on' => $validated['ends_on'] ?? null,
            'decree_number' => $validated['decree_number'] ?? null,
            'is_acting' => (bool) ($validated['is_acting'] ?? false),
            'assigned_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.positions.index')
            ->with('success', __('Penugasan jabatan berhasil disimpan.'));
    }

    public function destroyAssignment(PositionAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return redirect()->route('admin.positions.index')
            ->with('success', __('Penugasan jabatan berhasil diakhiri.'));
    }

    /**
     * @param  list<string>  $before
     * @param  list<string>  $after
     */
    private function authorizeSensitivePermissionChanges(User $actor, array $before, array $after): void
    {
        if ($actor->role === 'superadmin') {
            return;
        }

        $catalog = config('permissions.catalog', []);
        $changed = array_merge(array_diff($after, $before), array_diff($before, $after));

        foreach ($changed as $perm) {
            if (! empty($catalog[$perm]['sensitive'])) {
                abort(403, __('Hanya Admin Utama (superadmin) yang dapat memberikan atau mencabut hak akses sensitif (:perm).', [
                    'perm' => $perm,
                ]));
            }
        }
    }

    private function isUsedInActiveWorkflow(string $positionCode): bool
    {
        $definitions = WorkflowDefinition::query()->where('is_active', true)->get(['steps']);
        foreach ($definitions as $def) {
            foreach ($def->steps ?? [] as $step) {
                if (($step['role'] ?? null) === $positionCode || ($step['position'] ?? null) === $positionCode) {
                    return true;
                }
            }
        }

        return false;
    }
}
