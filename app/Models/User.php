<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuid, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'account_type',
        'account_status',
        'deactivated_reason',
        'must_change_password',
        'two_factor_code',
        'two_factor_expires_at',
        'totp_secret',
        'totp_enabled_at',
        'totp_recovery_codes',
        'google_id',
        'google_token',
        'dashboard_layout',
        'preferred_locale',
        'preferred_theme',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'totp_enabled_at' => 'datetime',
            'totp_recovery_codes' => 'array',
            'dashboard_layout' => 'array',
        ];
    }

    public function hasTotpEnabled(): bool
    {
        return ! empty($this->totp_secret) && ! empty($this->totp_enabled_at);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function classrooms() // As a teacher
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function subjects() // Subjects taught by the teacher
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * Relationship to point logs.
     */
    public function pointLogs()
    {
        return $this->hasMany(StudentPointLog::class);
    }

    /**
     * Get current total points.
     */
    public function totalPoints()
    {
        return $this->pointLogs()->sum('points');
    }

    /**
     * Parent relationship: A parent user can have many children (students).
     */
    public function children()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    /** @return HasMany<PositionAssignment, $this> */
    public function positionAssignments(): HasMany
    {
        return $this->hasMany(PositionAssignment::class);
    }

    /**
     * Resolve static Layer-1 account type (`siswa`, `orang_tua`, `guru`, `karyawan`).
     */
    public function resolvedAccountType(): ?string
    {
        if (! empty($this->account_type)) {
            return $this->account_type;
        }

        if ($this->role === 'superadmin') {
            return null;
        }

        return config("permissions.role_to_account_type.{$this->role}");
    }

    /**
     * Active position codes held by this user on the given date.
     *
     * @return list<string>
     */
    public function activePositionCodes(?CarbonInterface $onDate = null): array
    {
        if (! $this->exists || ! Schema::hasTable('position_assignments')) {
            return [];
        }

        return $this->positionAssignments()
            ->activeOn($onDate)
            ->with('position')
            ->get()
            ->map(fn (PositionAssignment $assignment) => $assignment->position?->code)
            ->filter()
            ->values()
            ->unique()
            ->all();
    }

    /**
     * All effective role tokens for this user (combining base account_type/role,
     * active position assignments, and role equivalents of active permissions).
     *
     * @return list<string>
     */
    public function effectiveRoles(?CarbonInterface $onDate = null): array
    {
        $roles = [];

        if (! empty($this->role)) {
            $roles[$this->role] = true;
        }

        if (! empty($this->account_type)) {
            if ($this->account_type === 'guru') {
                $roles['guru'] = true;
            } elseif ($this->account_type === 'siswa') {
                $roles['siswa'] = true;
            } elseif ($this->account_type === 'orang_tua') {
                $roles['orang_tua'] = true;
            }
        }

        if ($this->exists && Schema::hasTable('position_assignments')) {
            $assignments = $this->positionAssignments()
                ->activeOn($onDate)
                ->with('position.permissions')
                ->get();

            $catalog = config('permissions.catalog', []);
            foreach ($assignments as $assignment) {
                $position = $assignment->position;
                if (! $position || ! $position->is_active) {
                    continue;
                }
                $roles[$position->code] = true;
                if ($position->code === 'kepala_sekolah') {
                    $roles['kepsek'] = true;
                }
                foreach ($position->permissions as $perm) {
                    foreach ($catalog[$perm->permission]['implies_roles'] ?? [] as $impliedRole) {
                        $roles[$impliedRole] = true;
                    }
                }
            }
        }

        return array_keys($roles);
    }

    /**
     * Check whether this user holds the given permission key (`kurikulum.kelola`, etc.).
     */
    public function hasPermission(string $permission, ?CarbonInterface $onDate = null): bool
    {
        if (($this->account_status ?? 'aktif') === 'nonaktif') {
            return false;
        }

        if ($this->role === 'superadmin') {
            return true;
        }

        $catalog = config('permissions.catalog', []);
        $definition = $catalog[$permission] ?? null;

        if ($this->role === 'admin') {
            if ($permission === 'sistem.kelola_jabatan') {
                return true;
            }

            return $definition !== null && empty($definition['sensitive']);
        }

        // Check active position assignments first
        if ($this->exists && Schema::hasTable('position_assignments')) {
            $hasViaAssignment = $this->positionAssignments()
                ->activeOn($onDate)
                ->whereHas('position.permissions', fn ($q) => $q->where('permission', $permission))
                ->exists();

            if ($hasViaAssignment) {
                return true;
            }
        }

        // Fallback to position matching the user's legacy `role` code if that position is active
        if (! empty($this->role) && Schema::hasTable('positions')) {
            $roleCode = $this->role === 'kepsek' ? 'kepala_sekolah' : $this->role;
            $hasViaLegacyRolePosition = Position::query()
                ->where('code', $roleCode)
                ->where('is_active', true)
                ->whereHas('permissions', fn ($q) => $q->where('permission', $permission))
                ->exists();

            if ($hasViaLegacyRolePosition) {
                return true;
            }
        }

        if ($definition !== null && in_array($this->role, $definition['implies_roles'] ?? [], true)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has specific role (or active position).
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->effectiveRoles(), true);
    }

    /**
     * Parent accounts exist under both role names.
     */
    public function isParent(): bool
    {
        return $this->resolvedAccountType() === 'orang_tua'
            || in_array($this->role, ['parent', 'orang_tua'], true);
    }

    /**
     * Check if user is an administrator type (Admin or Waka).
     */
    public function isAdmin(): bool
    {
        return array_intersect($this->effectiveRoles(), [
            'admin',
            'superadmin',
            'kepala_sekolah',
            'waka_kurikulum',
            'waka_kesiswaan',
            'waka_sarpras',
            'waka_humas',
        ]) !== [];
    }

    public function generateTwoFactorCode()
    {
        $this->timestamps = false;
        // random_int: a code someone could predict defeats the second factor.
        $this->two_factor_code = random_int(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(10)->toDateTimeString();
        $this->save();
    }

    public function resetTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    /**
     * Simulated attribute for Enterprise MFA setup check.
     */
    public function getMfaEnabledAttribute(): bool
    {
        return ! empty($this->two_factor_code);
    }

    /**
     * Relationship to Tahfidz logs.
     */
    public function tahfidzLogs()
    {
        return $this->hasMany(TahfidzLog::class, 'teacher_id');
    }

    /**
     * Which dashboard this account gets.
     * Every spelling of a role lands on the same one, so a `siswa` account
     * sees the student dashboard and `orang_tua` the parent one. Staff with
     * no dashboard of their own (UKS, library) get the general one, not the
     * school-wide figures.
     */
    public function dashboardRole(): string
    {
        return match (true) {
            in_array($this->role, StudentDataAccess::STUDENT_ROLES, true) => 'student',
            in_array($this->role, StudentDataAccess::PARENT_ROLES, true) => 'parent',
            in_array($this->role, [...StudentDataAccess::TEACHER_ROLES, 'bk', 'wali_kelas'], true) => 'teacher',
            in_array($this->role, ['admin', 'superadmin', 'staf_tu', ...Roles::LEADERSHIP], true) => 'leadership',
            default => 'general',
        };
    }

    /**
     * Get default dashboard layout configurations.
     */
    public function getDefaultDashboardLayout(): array
    {
        switch ($this->dashboardRole()) {
            case 'teacher':
                return [
                    ['id' => 'teacher_schedule_today', 'name' => 'Jadwal Hari Ini', 'visible' => true],
                    ['id' => 'teacher_pending_attendance', 'name' => 'Absensi Belum Diinput', 'visible' => true],
                    ['id' => 'teacher_journal_today', 'name' => 'Jurnal Hari Ini', 'visible' => true],
                    ['id' => 'teacher_ungraded_assignments', 'name' => 'Tugas Belum Dinilai', 'visible' => true],
                    ['id' => 'teacher_tahfidz_progress', 'name' => 'Progress Tahfidz Kelas', 'visible' => true],
                    ['id' => 'teacher_recent_notifications', 'name' => 'Notifikasi Terbaru', 'visible' => true],
                ];

            case 'student':
                return [
                    ['id' => 'student_schedule_today', 'name' => 'Jadwal Pelajaran Hari Ini', 'visible' => true],
                    ['id' => 'student_pending_assignments', 'name' => 'Tugas Tertunda', 'visible' => true],
                    ['id' => 'student_recent_scores', 'name' => 'Nilai Terbaru', 'visible' => true],
                    ['id' => 'student_mutabaah_today', 'name' => 'Mutaba\'ah Hari Ini', 'visible' => true],
                    ['id' => 'student_library_loans', 'name' => 'Buku yang Dipinjam', 'visible' => true],
                ];

            case 'parent':
                return [
                    ['id' => 'parent_child_attendance', 'name' => 'Kehadiran Anak Hari Ini', 'visible' => true],
                    ['id' => 'parent_child_scores', 'name' => 'Nilai Terbaru Anak', 'visible' => true],
                    ['id' => 'parent_child_mutabaah', 'name' => 'Mutaba\'ah Anak Minggu Ini', 'visible' => true],
                    ['id' => 'parent_upcoming_events', 'name' => 'Agenda Sekolah Mendatang', 'visible' => true],
                    ['id' => 'parent_report_progress', 'name' => 'Rapor Progress Anak', 'visible' => true],
                    ['id' => 'parent_homeroom_messages', 'name' => 'Pesan dari Wali Kelas', 'visible' => true],
                ];

            default:
                return [
                    ['id' => 'general_stats', 'name' => 'Statistik Utama', 'visible' => true],
                    ['id' => 'quick_access', 'name' => 'Akses Cepat', 'visible' => true],
                    ['id' => 'attendance_today', 'name' => 'Tingkat Kehadiran Hari Ini', 'visible' => true],
                    ['id' => 'financial_status_month', 'name' => 'Status Keuangan Bulan Ini', 'visible' => true],
                    ['id' => 'teaching_journal_today', 'name' => 'Jurnal Mengajar Hari Ini', 'visible' => true],
                    ['id' => 'average_grades_semester', 'name' => 'Rata-rata Nilai Semester', 'visible' => true],
                    ['id' => 'approval_pending', 'name' => 'Persetujuan Tertunda', 'visible' => true],
                    ['id' => 'weekly_mutabaah_recap', 'name' => 'Rekap Mutaba\'ah Minggu Ini', 'visible' => true],
                    ['id' => 'financial_trend', 'name' => 'Tren Keuangan', 'visible' => true],
                    ['id' => 'live_feed', 'name' => 'Aktivitas Terbaru', 'visible' => true],
                    ['id' => 'spmb_analytics', 'name' => 'Analisis SPMB', 'visible' => true],
                ];
        }
    }
}
