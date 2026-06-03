<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class UksVisit extends Model
{
    use HasUuid;

    protected $fillable = [
        'student_id', 'handled_by', 'visit_time', 'leave_time',
        'complaint', 'diagnosis', 'treatment', 'severity',
        'disposition', 'parent_notified', 'notes',
    ];

    protected $casts = [
        'visit_time' => 'datetime',
        'leave_time' => 'datetime',
        'parent_notified' => 'boolean',
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

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(UksMedicine::class, 'uks_visit_medicines')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
