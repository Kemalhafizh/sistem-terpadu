<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'status',
        'target_role',
        'target_classroom_id',
        'is_anonymous',
        'start_date',
        'end_date',
        'max_responses',
        'send_notification',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'send_notification' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'target_classroom_id');
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order', 'asc');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOpen($query)
    {
        $now = now();

        return $query->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            });
    }

    public function scopeForRole($query, ?string $role)
    {
        if (empty($role)) {
            return $query;
        }

        return $query->where(function ($q) use ($role) {
            $q->whereNull('target_role')
                ->orWhere('target_role', $role);
        });
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        if ($this->max_responses && $this->responses()->count() >= $this->max_responses) {
            return false;
        }

        return true;
    }
}
