<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicEvent extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'is_all_day',
        'color',
        'target_role',
        'target_classroom_id',
        'academic_year_id',
        'created_by',
        'is_active',
        'send_notification',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_all_day' => 'boolean',
        'is_active' => 'boolean',
        'send_notification' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'target_classroom_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Scopes untuk filtering dinamis
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeForRole(Builder $query, string $role): void
    {
        $query->where(function ($q) use ($role) {
            $q->whereNull('target_role')
                ->orWhere('target_role', $role);
        });
    }

    public function scopeForClassroom(Builder $query, ?int $classroomId): void
    {
        if ($classroomId) {
            $query->where(function ($q) use ($classroomId) {
                $q->whereNull('target_classroom_id')
                    ->orWhere('target_classroom_id', $classroomId);
            });
        }
    }

    public function scopeInDateRange(Builder $query, string $start, string $end): void
    {
        $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($sub) use ($start, $end) {
                    $sub->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                });
        });
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('start_date', '>=', now());
    }
}
