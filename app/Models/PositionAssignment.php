<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionAssignment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'position_id',
        'user_id',
        'starts_on',
        'ends_on',
        'decree_number',
        'is_acting',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_acting' => 'boolean',
        ];
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActiveOn(Builder $query, ?CarbonInterface $date = null): void
    {
        $day = ($date ? CarbonImmutable::instance($date) : CarbonImmutable::today())->toDateString();

        $query->whereDate('starts_on', '<=', $day)
            ->where(function (Builder $q) use ($day) {
                $q->whereNull('ends_on')
                    ->orWhereDate('ends_on', '>=', $day);
            })
            ->whereHas('position', fn (Builder $p) => $p->where('is_active', true));
    }

    /**
     * Find the active holder of a given position code on a specific date
     * (e.g. Principal on the date a report card was issued).
     */
    public static function holderOn(string $positionCode, ?CarbonInterface $date = null): ?User
    {
        $assignment = self::query()
            ->activeOn($date)
            ->whereHas('position', fn (Builder $q) => $q->where('code', $positionCode))
            ->orderByDesc('is_acting')
            ->orderByDesc('starts_on')
            ->with('user')
            ->first();

        if ($assignment?->user !== null) {
            return $assignment->user;
        }

        return User::query()->where('role', $positionCode)->first();
    }
}
