<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherTodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_date',
        'priority',
        'category',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
                     ->whereNotNull('due_date')
                     ->whereDate('due_date', '<', now()->toDateString());
    }

    public function scopeDueToday($query)
    {
        return $query->where('is_completed', false)
                     ->whereDate('due_date', now()->toDateString());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->where('is_completed', false)
                     ->whereNotNull('due_date')
                     ->whereDate('due_date', '>=', now()->toDateString())
                     ->whereDate('due_date', '<=', now()->addDays(7)->toDateString());
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return !$this->is_completed && $this->due_date && $this->due_date->isPast();
    }

    public function isDueToday(): bool
    {
        return !$this->is_completed && $this->due_date && $this->due_date->isToday();
    }

    public function isDueSoon(): bool
    {
        return !$this->is_completed
            && $this->due_date
            && $this->due_date->isFuture()
            && $this->due_date->diffInDays(now()) <= 3;
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'red',
            'medium' => 'amber',
            'low' => 'emerald',
            default => 'gray',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'mengajar' => '📚',
            'administrasi' => '📋',
            'rapat' => '🤝',
            'penilaian' => '📝',
            'lainnya' => '📌',
            default => '📌',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'mengajar' => 'Mengajar',
            'administrasi' => 'Administrasi',
            'rapat' => 'Rapat',
            'penilaian' => 'Penilaian',
            'lainnya' => 'Lainnya',
            default => 'Lainnya',
        };
    }
}
