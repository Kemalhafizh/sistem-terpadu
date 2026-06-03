<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use \App\Traits\HasUuid, \App\Traits\LogsActivity, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'must_change_password',
        'two_factor_code',
        'two_factor_expires_at',
        'google_id',
        'google_token',
        'dashboard_layout',
        'preferred_locale',
        'preferred_theme',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
            'dashboard_layout' => 'array',
        ];
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
        $this->two_factor_code = rand(100000, 999999);
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
        return [
            ['id' => 'general_stats', 'name' => 'Statistik Utama', 'visible' => true],
            ['id' => 'quick_access', 'name' => 'Akses Cepat', 'visible' => true],
            ['id' => 'financial_trend', 'name' => 'Tren Keuangan', 'visible' => true],
            ['id' => 'live_feed', 'name' => 'Aktivitas Terbaru', 'visible' => true],
            ['id' => 'spmb_analytics', 'name' => 'Analisis SPMB', 'visible' => true],
        ];
    }
}
