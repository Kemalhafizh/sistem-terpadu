<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $table = 'achievements';

    protected $fillable = [
        'student_id',
        'title',
        'field',
        'level',
        'organizer',
        'event_date',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'certificate_path',
        'points_earned',
    ];

    protected $casts = [
        'event_date' => 'date',
        'verified_at' => 'datetime',
        'points_earned' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeValidated($query)
    {
        return $query->where('verification_status', 'VALIDATED');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'PENDING_REVIEW');
    }

    public function approve(User $verifier): self
    {
        $this->update([
            'verification_status' => 'VALIDATED',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return $this;
    }

    public function reject(User $verifier, ?string $reason = null): self
    {
        $this->update([
            'verification_status' => 'REJECTED',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'rejection_reason' => $reason ?: 'Dokumen sertifikat belum memenuhi kriteria validasi.',
        ]);

        return $this;
    }

    /**
     * Map model to Android AchievementItem schema
     */
    public function toMobileItemArray(): array
    {
        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'field' => (string) $this->field,
            'level' => (string) strtoupper($this->level),
            'organizer' => (string) $this->organizer,
            'date' => $this->event_date ? $this->event_date->format('Y-m-d') : now()->format('Y-m-d'),
            'verification_status' => (string) $this->verification_status,
            'certificate_url' => $this->certificate_path ? asset('storage/' . $this->certificate_path) : null,
            'points_earned' => (int) ($this->points_earned ?? 0),
        ];
    }
}
