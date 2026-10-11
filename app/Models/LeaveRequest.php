<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class LeaveRequest extends Model
{
    use HasFactory;

    /** The approval workflow every leave request goes through (WorkflowDefinitionSeeder). */
    public const WORKFLOW = 'leave_approval';

    /** Leave type => Indonesian label (translated with __()). */
    public const TYPES = [
        'sakit' => 'Sakit',
        'izin' => 'Izin',
        'cuti_tahunan' => 'Cuti Tahunan',
        'cuti_melahirkan' => 'Cuti Melahirkan',
        'umroh_haji' => 'Umroh/Haji',
        'dinas_luar' => 'Dinas Luar',
    ];

    /** How an approved leave day counts in the attendance recap. */
    public const RECAP_BUCKET = [
        'sakit' => 'sakit',
        'izin' => 'izin',
        'dinas_luar' => 'izin',
        'cuti_tahunan' => 'cuti',
        'cuti_melahirkan' => 'cuti',
        'umroh_haji' => 'cuti',
    ];

    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'type',
        'reason',
        'attachment',
        'status',
        'admin_notes',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getTypeLabelAttribute(): string
    {
        return __(self::TYPES[$this->type] ?? $this->type);
    }

    /** @return MorphOne<WorkflowRequest, $this> */
    public function workflow(): MorphOne
    {
        return $this->morphOne(WorkflowRequest::class, 'requestable')->latestOfMany();
    }

    /** Approved leave that overlaps the given dates. */
    public function scopeApprovedBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->where('status', 'approved')
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from);
    }

    /**
     * Get the employee who submitted the leave request.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the leave request.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
