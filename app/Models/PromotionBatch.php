<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromotionBatch extends Model
{
    use HasUuid, LogsActivity;

    public const STATUS_DRAFT = 'draf';

    public const STATUS_HOMEROOM_REVIEW = 'ditinjau_wali';

    public const STATUS_REVIEWED = 'ditinjau_wali';

    public const STATUS_DECIDED = 'ditetapkan';

    public const STATUS_EXECUTED = 'dieksekusi';

    public const STATUS_CANCELLED = 'dibatalkan';

    protected $fillable = [
        'from_school_year_id',
        'to_school_year_id',
        'status',
        'meeting_note_id',
        'decided_by',
        'decided_at',
        'held_by',
        'held_at',
        'hold_reason',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
            'held_at' => 'datetime',
            'executed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function fromSchoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'from_school_year_id');
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function toSchoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'to_school_year_id');
    }

    /** @return HasMany<PromotionDecision, $this> */
    public function decisions(): HasMany
    {
        return $this->hasMany(PromotionDecision::class);
    }
}
