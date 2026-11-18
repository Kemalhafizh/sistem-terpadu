<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectiveSurvey extends Model
{
    use HasUuid, LogsActivity;

    public const STATUS_DRAFT = 'draf';

    public const STATUS_OPEN = 'dibuka';

    public const STATUS_CLOSED = 'ditutup';

    public const STATUS_COMPLETED = 'selesai';

    protected $fillable = [
        'school_year_id',
        'title',
        'opens_at',
        'closes_at',
        'min_choices',
        'max_choices',
        'ranked',
        'status',
        'announced_at',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'announced_at' => 'datetime',
            'min_choices' => 'integer',
            'max_choices' => 'integer',
            'ranked' => 'boolean',
        ];
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /** @return BelongsToMany<Subject, $this> */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'elective_survey_subjects')->withTimestamps();
    }

    /** @return HasMany<ElectiveChoice, $this> */
    public function choices(): HasMany
    {
        return $this->hasMany(ElectiveChoice::class);
    }

    public function isOpen(): bool
    {
        if ($this->status !== self::STATUS_OPEN) {
            return false;
        }
        if ($this->closes_at !== null && now()->greaterThan($this->closes_at)) {
            return false;
        }

        return true;
    }
}
