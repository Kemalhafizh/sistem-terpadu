<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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

    /**
     * Check if user has specific role.
     */

    /**
     * Check if user has specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Parent accounts exist under both role names.
     */
    public function isParent(): bool
    {
        return in_array($this->role, ['parent', 'orang_tua'], true);
    }

    /**
     * Wellbeing screening results of this user as a student.
     */
    public function wellbeingResponses(): HasMany
    {
        return $this->hasMany(WellbeingScreeningResponse::class, 'student_id');
    }

    /**
     * Check if user is an administrator type (Admin or Waka).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [
            'admin',
            'superadmin',
            'kepala_sekolah',
            'waka_kurikulum',
            'waka_kesiswaan',
            'waka_sarpras',
            'waka_humas',
        ]);
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
     * Get default dashboard layout configurations.
     */
    public function getDefaultDashboardLayout(): array
    {
        switch ($this->role) {
            case 'guru':
                return [
                    ['id' => 'teacher_schedule_today', 'name' => 'Jadwal Hari Ini', 'visible' => true],
                    ['id' => 'teacher_pending_attendance', 'name' => 'Absensi Belum Diinput', 'visible' => true],
                    ['id' => 'teacher_journal_today', 'name' => 'Jurnal Hari Ini', 'visible' => true],
                    ['id' => 'teacher_ungraded_assignments', 'name' => 'Tugas Belum Dinilai', 'visible' => true],
                    ['id' => 'teacher_tahfidz_progress', 'name' => 'Progress Tahfidz Kelas', 'visible' => true],
                    ['id' => 'teacher_todo_widget', 'name' => 'Todo List Saya', 'visible' => true],
                    ['id' => 'teacher_recent_notifications', 'name' => 'Notifikasi Terbaru', 'visible' => true],
                ];

            case 'student':
                return [
                    ['id' => 'student_schedule_today', 'name' => 'Jadwal Pelajaran Hari Ini', 'visible' => true],
                    ['id' => 'student_pending_assignments', 'name' => 'Tugas Tertunda', 'visible' => true],
                    ['id' => 'student_recent_scores', 'name' => 'Nilai Terbaru', 'visible' => true],
                    ['id' => 'student_mutabaah_today', 'name' => 'Mutaba\'ah Hari Ini', 'visible' => true],
                    ['id' => 'student_library_loans', 'name' => 'Buku yang Dipinjam', 'visible' => true],
                    ['id' => 'student_recent_forum', 'name' => 'Thread Forum Terbaru', 'visible' => true],
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

            case 'admin':
            case 'superadmin':
            case 'kepala_sekolah':
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
