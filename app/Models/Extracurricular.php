<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extracurricular extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'advisor_id',
        'coach_name',
        'schedule_day',
        'schedule_time',
        'location',
        'max_members',
        'is_active',
        'is_registration_open',
        'cover_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_registration_open' => 'boolean',
        'max_members' => 'integer',
    ];

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function members()
    {
        return $this->hasMany(ExtracurricularMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(ExtracurricularMember::class)->where('status', 'active');
    }

    public function attendances()
    {
        return $this->hasMany(ExtracurricularAttendance::class);
    }

    public function activities()
    {
        return $this->hasMany(ExtracurricularActivity::class)->orderBy('activity_date', 'desc');
    }

    public function getAvailableSlotsAttribute(): int
    {
        if (is_null($this->max_members)) {
            return 9999; // Unlimited
        }
        return max(0, $this->max_members - $this->activeMembers()->count());
    }

    public function achievements()
    {
        return $this->hasMany(ExtracurricularAchievement::class);
    }

    /**
     * Map model to Android EkskulItem schema
     */
    public function toMobileItemArray(?Student $currentStudent = null): array
    {
        $schedule = "Setiap {$this->schedule_day}, {$this->schedule_time} WIB";
        if (empty($this->schedule_day)) {
            $schedule = $this->schedule_time ?: 'Jadwal Menyesuaikan';
        }

        $isRegistered = false;
        if ($currentStudent) {
            $isRegistered = $this->members()
                ->where('student_id', $currentStudent->id)
                ->where('status', 'active')
                ->exists();
        }

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'category' => (string) ($this->category ?? 'Pengembangan Minat & Bakat'),
            'coach_name' => (string) ($this->coach_name ?? $this->advisor?->name ?? 'Pembina Sekolah'),
            'training_schedule' => (string) $schedule,
            'location' => (string) ($this->location ?? 'Area Kampus'),
            'description' => (string) ($this->description ?? 'Kegiatan ekstrakurikuler sekolah.'),
            'member_count' => (int) $this->activeMembers()->count(),
            'is_registered' => (bool) $isRegistered,
        ];
    }
}

