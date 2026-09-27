<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CounselingSession extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'student_id', 'counselor_id', 'requested_by', 'scheduled_at',
        'started_at', 'ended_at', 'type', 'category', 'status',
        'reason', 'session_notes', 'action_plan', 'follow_up_notes',
        'follow_up_date', 'is_confidential',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'follow_up_date' => 'date',
        'is_confidential' => 'boolean',

        // Enkripsi otomatis kolom sensitif
        'reason' => 'encrypted',
        'session_notes' => 'encrypted',
        'action_plan' => 'encrypted',
        'follow_up_notes' => 'encrypted',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Scope sesi mendatang
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')->where('scheduled_at', '>=', now());
    }

    /**
     * Scope butuh follow up
     */
    public function scopeNeedsFollowUp($query)
    {
        return $query->where('status', 'completed')
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', now()->addDays(3)->toDateString());
    }
}
